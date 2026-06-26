<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Start payment for an order (redirects to gateway or shows hosted form).
     */
    public function start(Request $request, Order $order, PaymentService $service)
    {
        abort_unless(Auth::id() === $order->user_id, 403);

        $gateway = PaymentGateway::where('code', $request->input('gateway'))->firstOrFail();
        $result  = $service->pay($order, $gateway);

        if (! ($result['ok'] ?? false)) {
            // Send user to the order page with a clear failure message + retry option.
            return redirect()
                ->route('checkout.completed', ['order' => $order->id])
                ->with('error', $result['message'] ?? 'تعذر بدء عملية الدفع.');
        }
        if (! empty($result['redirect_url'])) {
            return redirect()->away($result['redirect_url']);
        }
        if (! empty($result['html'])) {
            return response($result['html']);
        }
        return redirect()->route('checkout.completed', ['order' => $order->id]);
    }

    /** Called by the gateway after the user completes/cancels payment. */
    public function verify(Request $request, ?string $payment = null)
    {
        $result = app(PaymentService::class)->verify($request, $payment);
        return view('checkout.verify', compact('result'));
    }

    public function completed(Order $order): View
    {
        abort_unless(Auth::id() === $order->user_id, 403);
        return view('checkout.completed', compact('order'));
    }
}
