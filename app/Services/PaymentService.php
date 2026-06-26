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
     * Test connectivity by attempting a tiny no-op verification check (best-effort).
     */
    public function testConnection(PaymentGateway $gateway): array
    {
        if ($gateway->driver === 'cod') {
            return ['ok' => true, 'message' => 'COD لا يحتاج اتصال خارجي.'];
        }

        if (! class_exists(\Nafezly\Payments\Factories\PaymentFactory::class)) {
            return ['ok' => false, 'message' => 'المكتبة غير مثبتة.'];
        }

        try {
            $this->applyRuntimeConfig($gateway);
            $driver = (new \Nafezly\Payments\Factories\PaymentFactory())->get($gateway->driver);
            return ['ok' => true, 'message' => 'تم تحميل الـ driver: ' . get_class($driver)];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
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
