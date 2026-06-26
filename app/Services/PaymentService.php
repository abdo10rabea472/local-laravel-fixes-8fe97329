<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Thin adapter around the Nafezly payments package + a built-in COD driver.
 * - Resolves gateway by stored "code".
 * - Pushes per-gateway config into runtime config('nafezly-payments.*') before calling pay/verify
 *   so each gateway is fully isolated and independently toggleable.
 */
class PaymentService
{
    /**
     * Initiate payment for an order.
     *
     * @return array{ok:bool, redirect_url?:string, html?:string, payment_id?:string, message?:string}
     */
    public function pay(Order $order, PaymentGateway $gateway, ?string $channel = null): array
    {
        $driverName = $this->canonicalDriver($gateway->driver);

        if (! $gateway->is_active) {
            return ['ok' => false, 'message' => 'بوابة الدفع غير مفعلة.'];
        }

        // Cash on Delivery — built-in driver, no external call.
        if ($driverName === 'cod') {
            $alreadyHandled = $order->payment_status === 'cod_pending';

            $order->forceFill([
                'payment_gateway'   => 'cod',
                'payment_status'    => 'cod_pending',
                'payment_reference' => 'COD-' . $order->order_number,
            ])->save();

            if (! $alreadyHandled) {
                $this->sendPlacedMailOnce($order);
                $this->dispatchShipmentIfNeeded($order);
            }

            return [
                'ok'           => true,
                'payment_id'   => 'COD-' . $order->order_number,
                'redirect_url' => route('checkout.completed', ['order' => $order->id]),
            ];
        }

        if (in_array($driverName, ['Paymob', 'PaymobWallet'], true)) {
            return $this->payWithPaymob($order, $gateway, $driverName === 'PaymobWallet' ? 'wallet' : $channel);
        }

        if (! class_exists(\Nafezly\Payments\Factories\PaymentFactory::class)) {
            return ['ok' => false, 'message' => 'مكتبة بوابات الدفع غير مثبتة. شغّل: composer require nafezly/payments dev-master'];
        }

        $configError = $this->validateRequiredConfig($gateway);
        if ($configError) {
            return ['ok' => false, 'message' => $configError];
        }

        try {
            $this->applyRuntimeConfig($gateway);

            /** @var \Nafezly\Payments\Interfaces\PaymentInterface $driver */
            $driver = (new \Nafezly\Payments\Factories\PaymentFactory())->get($driverName);

            $response = $driver
                ->setUserId((string) ($order->user_id ?? $order->id))
                ->setUserFirstName($this->splitName($order->customer_name)[0])
                ->setUserLastName($this->splitName($order->customer_name)[1])
                ->setUserEmail($order->email)
                ->setUserPhone($order->phone ?? '00000000000')
                ->setAmount((float) $order->total)
                ->setCurrency($order->currency ?: 'EGP')
                ->pay();

            $hasRedirect = ! empty($response['redirect_url']);
            $hasHtml     = ! empty($response['html']) && ! $this->looksLikeGatewayError($response['html']);
            $explicitOk  = array_key_exists('success', $response) ? (bool) $response['success'] : null;

            // The driver returned a response but it neither redirects to the gateway
            // nor returns embeddable HTML — treat this as a failure (e.g. invalid keys).
            if ($explicitOk === false || (! $hasRedirect && ! $hasHtml)) {
                $msg = $response['message']
                    ?? $this->extractGatewayError($response['html'] ?? null)
                    ?? 'البوابة لم تُرجع رابط دفع صالح — تحقق من بيانات الاعتماد في إعدادات البوابة.';
                Log::warning('payment.pay.no_redirect', [
                    'gateway'  => $gateway->code,
                    'order_id' => $order->id,
                    'response' => $response,
                ]);
                return ['ok' => false, 'message' => $msg];
            }

            // Persist reference for later verification
            $order->forceFill([
                'payment_gateway'   => $gateway->code,
                'payment_status'    => 'pending',
                'payment_reference' => $response['payment_id'] ?? null,
                'payment_response'  => $response,
            ])->save();

            return [
                'ok'           => true,
                'payment_id'   => $response['payment_id'] ?? null,
                'redirect_url' => $response['redirect_url'] ?? null,
                'html'         => $response['html'] ?? null,
            ];
        } catch (\Throwable $e) {
            Log::error('payment.pay.failed', [
                'gateway'  => $gateway->code,
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
            return ['ok' => false, 'message' => 'تعذر بدء عملية الدفع: ' . $e->getMessage()];
        }
    }

    /**
     * Verify a returned payment (called from /payments/verify/{payment}).
     */
    public function verify(Request $request, ?string $paymentCode = null): array
    {
        $code = $paymentCode ?: $request->input('payment') ?: $request->input('gateway');
        if (! $code) {
            return ['success' => false, 'message' => 'بوابة الدفع غير محددة.'];
        }

        $gateway = PaymentGateway::where('code', $code)->first();
        if (! $gateway) {
            return ['success' => false, 'message' => 'بوابة الدفع غير موجودة.'];
        }

        $driverName = $this->canonicalDriver($gateway->driver);

        if ($driverName === 'cod') {
            return ['success' => true, 'message' => 'تم تأكيد الدفع عند الاستلام.'];
        }

        if (in_array($driverName, ['Paymob', 'PaymobWallet'], true)) {
            return $this->verifyPaymob($request, $gateway);
        }

        if (! class_exists(\Nafezly\Payments\Factories\PaymentFactory::class)) {
            return ['success' => false, 'message' => 'مكتبة بوابات الدفع غير مثبتة.'];
        }

        try {
            $this->applyRuntimeConfig($gateway);
            $driver = (new \Nafezly\Payments\Factories\PaymentFactory())->get($driverName);
            $result = $driver->verify($request);

            // Try to update order
            $reference = $result['payment_id'] ?? $request->input('payment_id') ?? $request->input('merchantRefNumber');
            if ($reference) {
                Order::where('payment_reference', $reference)
                    ->orWhere('order_number', $reference)
                    ->limit(1)
                    ->each(function (Order $order) use (&$result, $gateway) {
                        $wasPaid = $order->payment_status === 'paid';
                        $paidNow = (bool) ($result['success'] ?? false);

                        $result['order_id'] = $order->id;

                        if (! $paidNow) {
                            $this->rejectUnpaidOrder($order, $result['message'] ?? 'payment verification failed', $result);
                            return;
                        }

                        $order->forceFill([
                            'payment_status'   => 'paid',
                            'payment_response' => $result,
                            'paid_at'          => now(),
                            'status'           => 'paid',
                        ])->save();

                        if (! $wasPaid) {
                            $this->clearCartForOrder($order);
                            $this->sendPlacedMailOnce($order);
                            $this->dispatchShipmentIfNeeded($order);
                        }
                    });
            }

            return $result;
        } catch (\Throwable $e) {
            Log::error('payment.verify.failed', [
                'gateway' => $gateway->code,
                'error'   => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => 'تعذر التحقق من الدفع: ' . $e->getMessage()];
        }
    }

    /**
     * Test connectivity:
     *  1) verify required config keys are present for this driver
     *  2) load the driver via the Nafezly factory
     *  3) where possible (Paymob), make a real authenticated call to validate the credentials
     */
    public function testConnection(PaymentGateway $gateway): array
    {
        $driverName = $this->canonicalDriver($gateway->driver);

        if ($driverName === 'cod') {
            return ['ok' => true, 'message' => '✓ الدفع عند الاستلام (COD) جاهز — لا يحتاج إلى اتصال خارجي.'];
        }

        // 1) Required config keys per driver
        $required = $this->requiredKeysFor($driverName);
        $cfg = array_change_key_case((array) $gateway->config, CASE_UPPER);
        $missing = [];
        foreach ($required as $key) {
            if (empty($cfg[strtoupper($key)])) {
                $missing[] = $key;
            }
        }
        if (! empty($missing)) {
            return [
                'ok' => false,
                'message' => "⚠ يجب إدخال المفاتيح التالية أولًا:\n• " . implode("\n• ", $missing),
            ];
        }

        // 2) Load the driver
        if (! class_exists(\Nafezly\Payments\Factories\PaymentFactory::class)) {
            return ['ok' => false, 'message' => '❌ مكتبة Nafezly غير مثبتة. شغّل: composer require nafezly/payments dev-master'];
        }

        try {
            $this->applyRuntimeConfig($gateway);
            $driver = (new \Nafezly\Payments\Factories\PaymentFactory())->get($driverName);
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => '❌ تعذر تحميل البوابة: ' . $e->getMessage()];
        }

        // 3) Driver-specific live credential check (best-effort)
        $live = $this->liveCredentialCheck($gateway, $cfg);
        if ($live !== null) {
            return $live;
        }

        // Fallback: keys present + driver loaded — we couldn't ping the gateway live
        return [
            'ok' => true,
            'message' => "✓ جميع المفاتيح المطلوبة مُدخلة والبوابة جاهزة (" . count($required) . " مفتاح).\nℹ ملاحظة: لا يوجد اختبار اتصال مباشر لهذه البوابة — صحّة المفاتيح تظهر عند أول عملية دفع.",
        ];
    }

    /** Map of required config keys per driver. */
    protected function requiredKeysFor(string $driver): array
    {
        $driver = $this->canonicalDriver($driver);

        return [
            'Paymob'       => ['PAYMOB_API_KEY','PAYMOB_INTEGRATION_ID','PAYMOB_IFRAME_ID','PAYMOB_HMAC'],
            'PaymobWallet' => ['PAYMOB_API_KEY','PAYMOB_WALLET_INTEGRATION_ID','PAYMOB_HMAC'],
            'Fawry'        => ['FAWRY_URL','FAWRY_SECRET','FAWRY_MERCHANT'],
            'Kashier'      => ['KASHIER_ACCOUNT_KEY','KASHIER_IFRAME_KEY','KASHIER_TOKEN','KASHIER_URL'],
            'HyperPay'     => ['HYPERPAY_BASE_URL','HYPERPAY_TOKEN','HYPERPAY_CREDIT_ID'],
            'PayPal'       => ['PAYPAL_CLIENT_ID','PAYPAL_SECRET'],
            'Stripe'       => ['STRIPE_SECRET_KEY','STRIPE_PUBLIC_KEY'],
            'Tap'          => ['TAP_SECRET_KEY','TAP_PUBLIC_KEY'],
            'Opay'         => ['OPAY_SECRET_KEY','OPAY_PUBLIC_KEY','OPAY_MERCHANT_ID'],
            'PayTabs'      => ['PAYTABS_PROFILE_ID','PAYTABS_SERVER_KEY'],
            'Thawani'      => ['THAWANI_API_KEY','THAWANI_URL','THAWANI_PUBLISHABLE_KEY'],
            'Telr'         => ['TELR_MERCHANT_ID','TELR_API_KEY'],
            'ClickPay'     => ['CLICKPAY_SERVER_KEY','CLICKPAY_PROFILE_ID'],
            'Binance'      => ['BINANCE_API','BINANCE_SECRET'],
            'NowPayments'  => ['NOWPAYMENTS_API_KEY'],
            'Payeer'       => ['PAYEER_MERCHANT_ID','PAYEER_API_KEY'],
            'PerfectMoney' => ['PERFECT_MONEY_ID','PERFECT_MONEY_PASSPHRASE'],
        ][$driver] ?? [];
    }

    /**
     * Real network call against the gateway to validate the API key.
     * Returns ['ok'=>bool,'message'=>string] for supported drivers, or null when
     * a live check isn't implemented for this driver.
     */
    protected function liveCredentialCheck(PaymentGateway $gateway, array $cfg): ?array
    {
        try {
            switch ($this->canonicalDriver($gateway->driver)) {
                case 'Paymob':
                    $resp = \Illuminate\Support\Facades\Http::timeout(15)
                        ->acceptJson()
                        ->post('https://accept.paymob.com/api/auth/tokens', [
                            'api_key' => $cfg['PAYMOB_API_KEY'],
                        ]);

                    if ($resp->successful() && $resp->json('token')) {
                        return ['ok' => true, 'message' => '✓ تم الاتصال بـ Paymob بنجاح والتحقق من API Key.'];
                    }

                    return ['ok' => false, 'message' => '❌ Paymob رفض API Key: ' . ($resp->json('detail') ?? $resp->body())];

                case 'PaymobWallet':
                    $resp = \Illuminate\Support\Facades\Http::timeout(15)
                        ->acceptJson()
                        ->post('https://accept.paymob.com/api/auth/tokens', [
                            'api_key' => $cfg['PAYMOB_API_KEY'],
                        ]);
                    if ($resp->successful() && $resp->json('token')) {
                        return ['ok' => true, 'message' => '✓ تم الاتصال بـ Paymob بنجاح والتحقق من API Key.'];
                    }
                    return ['ok' => false, 'message' => '❌ Paymob رفض المفاتيح: ' . ($resp->json('detail') ?? $resp->body())];

                // Stripe: GET /v1/balance with Bearer secret key
                case 'Stripe':
                    $resp = \Illuminate\Support\Facades\Http::timeout(15)
                        ->withToken($cfg['STRIPE_SECRET_KEY'])
                        ->get('https://api.stripe.com/v1/balance');
                    if ($resp->successful()) {
                        return ['ok' => true, 'message' => '✓ تم الاتصال بـ Stripe والتحقق من Secret Key.'];
                    }
                    return ['ok' => false, 'message' => '❌ Stripe رفض المفتاح: ' . ($resp->json('error.message') ?? $resp->status())];

                // PayPal: OAuth token
                case 'PayPal':
                    $base = (($cfg['PAYPAL_MODE'] ?? 'sandbox') === 'live')
                        ? 'https://api-m.paypal.com'
                        : 'https://api-m.sandbox.paypal.com';
                    $resp = \Illuminate\Support\Facades\Http::timeout(15)
                        ->asForm()
                        ->withBasicAuth($cfg['PAYPAL_CLIENT_ID'], $cfg['PAYPAL_SECRET'])
                        ->post("$base/v1/oauth2/token", ['grant_type' => 'client_credentials']);
                    if ($resp->successful() && $resp->json('access_token')) {
                        return ['ok' => true, 'message' => '✓ تم الاتصال بـ PayPal والتحقق من المفاتيح.'];
                    }
                    return ['ok' => false, 'message' => '❌ PayPal رفض المفاتيح: ' . ($resp->json('error_description') ?? $resp->status())];
            }
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => '❌ فشل الاتصال بالبوابة: ' . $e->getMessage()];
        }

        return null; // No live check for this driver
    }

    protected function payWithPaymob(Order $order, PaymentGateway $gateway, ?string $channel = null): array
    {
        $cfg = array_change_key_case((array) $gateway->config, CASE_UPPER);
        $useWallet = $channel === 'wallet';

        $missing = [];
        foreach (['PAYMOB_API_KEY', 'PAYMOB_HMAC'] as $key) {
            if (empty($cfg[$key])) {
                $missing[] = $key;
            }
        }

        if ($useWallet) {
            if (($cfg['PAYMOB_WALLET_ENABLED'] ?? '0') !== '1' && $this->canonicalDriver($gateway->driver) !== 'PaymobWallet') {
                return ['ok' => false, 'message' => 'محافظ Paymob غير مفعّلة من إعدادات البوابة.'];
            }
            if (empty($cfg['PAYMOB_WALLET_INTEGRATION_ID'])) {
                $missing[] = 'PAYMOB_WALLET_INTEGRATION_ID';
            }
        } else {
            foreach (['PAYMOB_INTEGRATION_ID', 'PAYMOB_IFRAME_ID'] as $key) {
                if (empty($cfg[$key])) {
                    $missing[] = $key;
                }
            }
        }

        if (! empty($missing)) {
            return ['ok' => false, 'message' => 'إعدادات Paymob ناقصة: ' . implode(', ', array_unique($missing))];
        }

        try {
            $amountCents = (int) round(((float) $order->total) * 100);
            $reference = $order->order_number ?: ('ORD-' . $order->id);
            $billing = $this->paymobBillingData($order);

            $auth = \Illuminate\Support\Facades\Http::timeout(20)
                ->acceptJson()
                ->asJson()
                ->post('https://accept.paymob.com/api/auth/tokens', [
                    'api_key' => $cfg['PAYMOB_API_KEY'],
                ]);

            $authToken = $auth->json('token');
            if (! $auth->successful() || ! $authToken) {
                return ['ok' => false, 'message' => 'Paymob رفض API Key: ' . $this->paymobError($auth->json(), $auth->body())];
            }

            $paymobOrder = \Illuminate\Support\Facades\Http::timeout(20)
                ->acceptJson()
                ->asJson()
                ->post('https://accept.paymob.com/api/ecommerce/orders', [
                    'auth_token' => $authToken,
                    'delivery_needed' => false,
                    'amount_cents' => $amountCents,
                    'currency' => $order->currency ?: ($cfg['PAYMOB_CURRENCY'] ?? 'EGP'),
                    'merchant_order_id' => $reference,
                    'items' => [],
                ]);

            $paymobOrderId = $paymobOrder->json('id');
            if (! $paymobOrder->successful() || ! $paymobOrderId) {
                return ['ok' => false, 'message' => 'تعذر إنشاء طلب Paymob: ' . $this->paymobError($paymobOrder->json(), $paymobOrder->body())];
            }

            $paymentKey = \Illuminate\Support\Facades\Http::timeout(20)
                ->acceptJson()
                ->asJson()
                ->post('https://accept.paymob.com/api/acceptance/payment_keys', [
                    'auth_token' => $authToken,
                    'expiration' => 3600,
                    'amount_cents' => $amountCents,
                    'order_id' => $paymobOrderId,
                    'billing_data' => $billing,
                    'currency' => $order->currency ?: ($cfg['PAYMOB_CURRENCY'] ?? 'EGP'),
                    'integration_id' => $useWallet ? $cfg['PAYMOB_WALLET_INTEGRATION_ID'] : $cfg['PAYMOB_INTEGRATION_ID'],
                    'lock_order_when_paid' => true,
                ]);

            $paymentToken = $paymentKey->json('token');
            if (! $paymentKey->successful() || ! $paymentToken) {
                return ['ok' => false, 'message' => 'تعذر إنشاء مفتاح دفع Paymob: ' . $this->paymobError($paymentKey->json(), $paymentKey->body())];
            }

            if ($useWallet) {
                $walletIframe = trim((string) ($cfg['PAYMOB_WALLET_IFRAME_ID'] ?? ''));

                if ($walletIframe !== '') {
                    // Open the wallet flow inside an IFRAME, same as cards
                    $redirectUrl = 'https://accept.paymob.com/api/acceptance/iframes/' . rawurlencode($walletIframe) . '?payment_token=' . rawurlencode($paymentToken);
                } else {
                    $walletPay = \Illuminate\Support\Facades\Http::timeout(20)
                        ->acceptJson()
                        ->asJson()
                        ->post('https://accept.paymob.com/api/acceptance/payments/pay', [
                            'source' => [
                                'identifier' => preg_replace('/\s+/', '', (string) $order->phone),
                                'subtype' => 'WALLET',
                            ],
                            'payment_token' => $paymentToken,
                        ]);

                    $redirectUrl = $walletPay->json('redirect_url');
                    if (! $walletPay->successful() || ! $redirectUrl) {
                        return ['ok' => false, 'message' => 'تعذر بدء دفع محفظة Paymob: ' . $this->paymobError($walletPay->json(), $walletPay->body())];
                    }
                }
            } else {
                $redirectUrl = 'https://accept.paymob.com/api/acceptance/iframes/' . rawurlencode((string) $cfg['PAYMOB_IFRAME_ID']) . '?payment_token=' . rawurlencode($paymentToken);
            }

            $order->forceFill([
                'payment_gateway' => 'paymob',
                'payment_status' => 'pending',
                'payment_reference' => $reference,
                'payment_response' => [
                    'channel' => $useWallet ? 'wallet' : 'card',
                    'paymob_order_id' => $paymobOrderId,
                    'payment_token_created' => true,
                ],
            ])->save();

            return [
                'ok' => true,
                'payment_id' => $reference,
                'redirect_url' => $redirectUrl,
            ];
        } catch (\Throwable $e) {
            Log::error('paymob.direct_pay.failed', [
                'order_id' => $order->id,
                'channel' => $useWallet ? 'wallet' : 'card',
                'error' => $e->getMessage(),
            ]);

            return ['ok' => false, 'message' => 'تعذر الاتصال بـ Paymob: ' . $e->getMessage()];
        }
    }

    protected function verifyPaymob(Request $request, PaymentGateway $gateway): array
    {
        $cfg = array_change_key_case((array) $gateway->config, CASE_UPPER);
        $hmac = (string) ($cfg['PAYMOB_HMAC'] ?? '');
        $reference = $request->input('merchant_order_id')
            ?: $request->input('payment_id')
            ?: $request->input('order')
            ?: $request->input('merchantRefNumber');

        if ($hmac !== '' && $request->filled('hmac')) {
            $string = $request->input('amount_cents')
                . $request->input('created_at')
                . $request->input('currency')
                . $request->input('error_occured')
                . $request->input('has_parent_transaction')
                . $request->input('id')
                . $request->input('integration_id')
                . $request->input('is_3d_secure')
                . $request->input('is_auth')
                . $request->input('is_capture')
                . $request->input('is_refunded')
                . $request->input('is_standalone_payment')
                . $request->input('is_voided')
                . $request->input('order')
                . $request->input('owner')
                . $request->input('pending')
                . $request->input('source_data_pan')
                . $request->input('source_data_sub_type')
                . $request->input('source_data_type')
                . $request->input('success');

            if (! hash_equals(hash_hmac('sha512', $string, $hmac), (string) $request->input('hmac'))) {
                return ['success' => false, 'payment_id' => $reference, 'message' => 'فشل التحقق من توقيع Paymob.'];
            }
        }

        $paidNow = in_array((string) $request->input('success'), ['true', '1'], true);
        $order = null;

        if ($reference) {
            $order = Order::where('payment_reference', $reference)
                ->orWhere('order_number', $reference)
                ->orWhere('payment_response->paymob_order_id', $reference)
                ->first();
        }

        if (! $order) {
            return ['success' => false, 'payment_id' => $reference, 'message' => 'لم يتم العثور على الطلب المرتبط بعملية Paymob.'];
        }

        $wasPaid = $order->payment_status === 'paid';
        $result = [
            'success' => $paidNow,
            'payment_id' => $order->payment_reference,
            'order_id' => $order->id,
            'message' => $paidNow ? 'تم الدفع بنجاح.' : 'لم يتم الدفع.',
            'process_data' => $request->all(),
        ];

        if (! $paidNow) {
            $this->rejectUnpaidOrder($order, $result['message'], $result);
            return $result;
        }

        $order->forceFill([
            'payment_status' => 'paid',
            'payment_response' => $result,
            'paid_at' => now(),
            'status' => 'paid',
        ])->save();

        if (! $wasPaid) {
            $this->clearCartForOrder($order);
            $this->sendPlacedMailOnce($order);
            $this->dispatchShipmentIfNeeded($order);
        }

        return $result;
    }

    protected function paymobBillingData(Order $order): array
    {
        [$first, $last] = $this->splitName($order->customer_name);

        return [
            'apartment' => 'NA',
            'email' => $order->email,
            'floor' => 'NA',
            'first_name' => $first,
            'street' => $order->shipping_address ?: 'NA',
            'building' => 'NA',
            'phone_number' => $order->phone ?: '01000000000',
            'shipping_method' => 'NA',
            'postal_code' => $order->shipping_postcode ?: 'NA',
            'city' => $order->shipping_city ?: ($order->shipping_region ?: 'NA'),
            'country' => 'EG',
            'last_name' => $last,
            'state' => $order->shipping_region ?: 'NA',
        ];
    }

    protected function paymobError(mixed $json, string $body): string
    {
        if (is_array($json)) {
            $msg = $json['detail'] ?? $json['message'] ?? $json['error'] ?? null;
            if ($msg) {
                return is_string($msg) ? $msg : json_encode($msg, JSON_UNESCAPED_UNICODE);
            }
        }

        return trim($body) !== '' ? trim($body) : 'خطأ غير معروف من Paymob';
    }

    /** Push gateway config into the runtime nafezly-payments config. */
    protected function applyRuntimeConfig(PaymentGateway $gateway): void
    {
        $cfg = (array) $gateway->config;
        config()->set('nafezly-payments.VERIFY_ROUTE_NAME', 'verify-payment');
        config()->set('nafezly-payments.APP_NAME', config('app.name'));
        config()->set('nafezly-payments.PAYMOB_CURRENCY', config('nafezly-payments.PAYMOB_CURRENCY', 'EGP'));
        if (empty($cfg)) return;
        foreach ($cfg as $key => $value) {
            // Allow the admin to store either short keys (api_key) or full keys (PAYMOB_API_KEY)
            $key = strtoupper($key);
            config()->set("nafezly-payments.$key", $value);
        }
    }

    protected function canonicalDriver(?string $driver): string
    {
        $driver = trim((string) $driver);
        $map = [
            'cod' => 'cod',
            'paymob' => 'Paymob',
            'paymobwallet' => 'PaymobWallet',
            'fawry' => 'Fawry',
            'kashier' => 'Kashier',
            'hyperpay' => 'HyperPay',
            'paypal' => 'PayPal',
            'stripe' => 'Stripe',
            'tap' => 'Tap',
            'opay' => 'Opay',
            'paytabs' => 'PayTabs',
            'thawani' => 'Thawani',
            'telr' => 'Telr',
            'clickpay' => 'ClickPay',
            'binance' => 'Binance',
            'nowpayments' => 'NowPayments',
            'payeer' => 'Payeer',
            'perfectmoney' => 'PerfectMoney',
        ];

        return $map[strtolower($driver)] ?? $driver;
    }

    protected function validateRequiredConfig(PaymentGateway $gateway): ?string
    {
        $driver = $this->canonicalDriver($gateway->driver);
        $cfg = array_change_key_case((array) $gateway->config, CASE_UPPER);
        $missing = [];

        foreach ($this->requiredKeysFor($driver) as $key) {
            if (empty($cfg[strtoupper($key)])) {
                $missing[] = $key;
            }
        }

        if (! empty($missing)) {
            return 'إعدادات بوابة الدفع ناقصة. أدخل: ' . implode(', ', $missing);
        }

        if (in_array($driver, ['Paymob', 'PaymobWallet'], true)) {
            $apiKey = trim((string) ($cfg['PAYMOB_API_KEY'] ?? ''));
            if ($apiKey === '' || mb_strlen($apiKey) < 40) {
                return 'PAYMOB_API_KEY غير صحيح أو غير كامل. انسخ API Key كامل من لوحة Paymob ثم احفظ الإعدادات.';
            }
        }

        return null;
    }

    public function rejectUnpaidOrder(Order $order, string $reason = '', array $response = []): void
    {
        if ($order->trashed()) {
            return;
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($order, $reason, $response) {
                $order->loadMissing('items');
                $stockService = app(\App\Services\StockService::class);

                foreach ($order->items as $item) {
                    if (! $item->product_id || (int) $item->quantity <= 0) {
                        continue;
                    }

                    $product = \App\Models\Product::whereKey($item->product_id)->lockForUpdate()->first();
                    if ($product) {
                        $stockService->apply(
                            $product,
                            (int) $item->quantity,
                            'payment_failed',
                            'Order',
                            $order->id,
                            'فشل/إلغاء الدفع للطلب ' . $order->order_number
                        );
                    }
                }

                if ($order->coupon_code) {
                    $coupon = \App\Models\Coupon::where('code', $order->coupon_code)->lockForUpdate()->first();
                    if ($coupon) {
                        \App\Models\CouponRedemption::where('coupon_id', $coupon->id)
                            ->where('email', strtolower(trim((string) $order->email)))
                            ->latest('id')
                            ->limit(1)
                            ->delete();

                        if ((int) $coupon->used_count > 0) {
                            $coupon->decrement('used_count');
                        }
                    }
                }

                $order->forceFill([
                    'status' => 'cancelled',
                    'payment_status' => 'failed',
                    'payment_response' => array_filter([
                        'rejected_reason' => $reason,
                        'rejected_at' => now()->toIso8601String(),
                        'gateway_response' => $response ?: null,
                    ]),
                    'cancelled_at' => now(),
                ])->save();

                $order->delete();
            });
        } catch (\Throwable $e) {
            Log::error('payment.reject_order.failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }

        cache()->forget('admin.orders.stats');
    }

    public function clearCartForOrder(Order $order): void
    {
        \App\Models\CartItem::query()
            ->when($order->user_id, fn ($q) => $q->where('user_id', $order->user_id))
            ->when(! $order->user_id, fn ($q) => $q->where('session_id', session()->getId()))
            ->delete();
    }

    protected function looksLikeGatewayError(?string $html): bool
    {
        if (! is_string($html) || trim($html) === '') {
            return false;
        }

        $body = trim($html);
        $decoded = json_decode($body, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return isset($decoded['detail']) || isset($decoded['error']) || isset($decoded['message']) || isset($decoded['errors']);
        }

        return str_contains(strtolower($body), 'unauthorized')
            || str_contains(strtolower($body), 'invalid')
            || str_contains(strtolower($body), 'error');
    }

    protected function extractGatewayError(?string $html): ?string
    {
        if (! is_string($html) || trim($html) === '') {
            return null;
        }

        $decoded = json_decode(trim($html), true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $message = $decoded['detail']
                ?? $decoded['message']
                ?? $decoded['error']
                ?? (isset($decoded['errors']) ? json_encode($decoded['errors'], JSON_UNESCAPED_UNICODE) : null);

            if ($message === 'Authentication credentials were not provided.') {
                return 'بيانات Paymob غير صحيحة أو ناقصة. أدخل PAYMOB_API_KEY من Developers > API Key وليس Public/Secret Key.';
            }

            return $message;
        }

        return null;
    }

    protected function splitName(?string $name): array
    {
        $name = trim((string) ($name ?? 'Customer'));
        $parts = preg_split('/\s+/', $name, 2) ?: ['Customer'];
        return [$parts[0] ?? 'Customer', $parts[1] ?? '-'];
    }

    protected function sendPlacedMailOnce(Order $order): void
    {
        try {
            \Illuminate\Support\Facades\Mail::to($order->email)
                ->send(new \App\Mail\OrderStatusMail($order->loadMissing('items'), 'placed'));
        } catch (\Throwable $e) {
            Log::warning('Order placed mail failed', [
                'order_id' => $order->id,
                'err' => $e->getMessage(),
            ]);
        }
    }

    protected function dispatchShipmentIfNeeded(Order $order): void
    {
        if (! $order->shipping_carrier_id) {
            return;
        }

        try {
            \App\Jobs\CreateCarrierShipment::dispatch($order->id)->onQueue('shipping');
        } catch (\Throwable $e) {
            Log::channel('shipping')->error('Shipment dispatch failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
