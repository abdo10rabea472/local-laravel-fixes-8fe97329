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
    public function pay(Order $order, PaymentGateway $gateway): array
    {
        if (! $gateway->is_active) {
            return ['ok' => false, 'message' => 'بوابة الدفع غير مفعلة.'];
        }

        // Cash on Delivery — built-in driver, no external call.
        if ($gateway->driver === 'cod') {
            $order->forceFill([
                'payment_gateway'   => 'cod',
                'payment_status'    => 'cod_pending',
                'payment_reference' => 'COD-' . $order->order_number,
            ])->save();

            return [
                'ok'           => true,
                'payment_id'   => 'COD-' . $order->order_number,
                'redirect_url' => route('checkout.completed', ['order' => $order->id]),
            ];
        }

        if (! class_exists(\Nafezly\Payments\Factories\PaymentFactory::class)) {
            return ['ok' => false, 'message' => 'مكتبة بوابات الدفع غير مثبتة. شغّل: composer require nafezly/payments dev-master'];
        }

        try {
            $this->applyRuntimeConfig($gateway);

            /** @var \Nafezly\Payments\Interfaces\PaymentInterface $driver */
            $driver = (new \Nafezly\Payments\Factories\PaymentFactory())->get($gateway->driver);

            $response = $driver
                ->setUserId((string) ($order->user_id ?? $order->id))
                ->setUserFirstName($this->splitName($order->customer_name)[0])
                ->setUserLastName($this->splitName($order->customer_name)[1])
                ->setUserEmail($order->email)
                ->setUserPhone($order->phone ?? '00000000000')
                ->setAmount((float) $order->total)
                ->setCurrency($order->currency ?: 'EGP')
                ->pay();

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

        if ($gateway->driver === 'cod') {
            return ['success' => true, 'message' => 'تم تأكيد الدفع عند الاستلام.'];
        }

        if (! class_exists(\Nafezly\Payments\Factories\PaymentFactory::class)) {
            return ['success' => false, 'message' => 'مكتبة بوابات الدفع غير مثبتة.'];
        }

        try {
            $this->applyRuntimeConfig($gateway);
            $driver = (new \Nafezly\Payments\Factories\PaymentFactory())->get($gateway->driver);
            $result = $driver->verify($request);

            // Try to update order
            $reference = $result['payment_id'] ?? $request->input('payment_id') ?? $request->input('merchantRefNumber');
            if ($reference) {
                Order::where('payment_reference', $reference)
                    ->orWhere('order_number', $reference)
                    ->limit(1)
                    ->each(function (Order $order) use ($result, $gateway) {
                        $order->forceFill([
                            'payment_status'   => ($result['success'] ?? false) ? 'paid' : 'failed',
                            'payment_response' => $result,
                            'paid_at'          => ($result['success'] ?? false) ? now() : $order->paid_at,
                            'status'           => ($result['success'] ?? false) ? 'paid' : $order->status,
                        ])->save();
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
        if ($gateway->driver === 'cod') {
            return ['ok' => true, 'message' => '✓ الدفع عند الاستلام (COD) جاهز — لا يحتاج إلى اتصال خارجي.'];
        }

        // 1) Required config keys per driver
        $required = $this->requiredKeysFor($gateway->driver);
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
            $driver = (new \Nafezly\Payments\Factories\PaymentFactory())->get($gateway->driver);
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
            switch ($gateway->driver) {
                // Paymob: POST /api/auth/tokens with api_key → returns { token: "..." }
                case 'Paymob':
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

    /** Push gateway config into the runtime nafezly-payments config. */
    protected function applyRuntimeConfig(PaymentGateway $gateway): void
    {
        $cfg = (array) $gateway->config;
        if (empty($cfg)) return;
        foreach ($cfg as $key => $value) {
            // Allow the admin to store either short keys (api_key) or full keys (PAYMOB_API_KEY)
            $key = strtoupper($key);
            config()->set("nafezly-payments.$key", $value);
        }
    }

    protected function splitName(?string $name): array
    {
        $name = trim((string) ($name ?? 'Customer'));
        $parts = preg_split('/\s+/', $name, 2) ?: ['Customer'];
        return [$parts[0] ?? 'Customer', $parts[1] ?? '-'];
    }
}
