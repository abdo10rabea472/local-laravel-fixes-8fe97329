<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OrderStatusMail;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $visibleToAdmin = fn ($query) => $query->where(function ($w) {
            $w->whereIn('payment_status', ['paid', 'cod_pending', 'refunded'])
              ->orWhereIn('status', ['paid', 'shipped', 'delivered', 'refunded']);
        });

        $q = Order::query()->where($visibleToAdmin)->with('items:id,order_id')->latest();

        if ($s = $request->string('q')->trim()->value()) {
            $q->where(function ($w) use ($s) {
                $w->where('order_number', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('customer_name', 'like', "%{$s}%");
            });
        }
        if ($status = $request->string('status')->value()) {
            if (in_array($status, Order::STATUSES, true)) {
                $q->where('status', $status);
            }
        }
        if ($from = $request->date('from')) {
            $q->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->date('to')) {
            $q->whereDate('created_at', '<=', $to);
        }

        $orders = $q->paginate(20)->withQueryString();

        $stats = cache()->remember('admin.orders.stats', 60, function () {
            return [
                'total' => Order::where(fn ($w) => $w->whereIn('payment_status', ['paid', 'cod_pending', 'refunded'])->orWhereIn('status', ['paid', 'shipped', 'delivered', 'refunded']))->count(),
                'pending' => Order::where(fn ($w) => $w->whereIn('payment_status', ['paid', 'cod_pending', 'refunded'])->orWhereIn('status', ['paid', 'shipped', 'delivered', 'refunded']))->where('status', 'pending')->count(),
                'paid' => Order::where(fn ($w) => $w->whereIn('payment_status', ['paid', 'cod_pending', 'refunded'])->orWhereIn('status', ['paid', 'shipped', 'delivered', 'refunded']))->where('status', 'paid')->count(),
                'shipped' => Order::where(fn ($w) => $w->whereIn('payment_status', ['paid', 'cod_pending', 'refunded'])->orWhereIn('status', ['paid', 'shipped', 'delivered', 'refunded']))->where('status', 'shipped')->count(),
                'delivered' => Order::where(fn ($w) => $w->whereIn('payment_status', ['paid', 'cod_pending', 'refunded'])->orWhereIn('status', ['paid', 'shipped', 'delivered', 'refunded']))->where('status', 'delivered')->count(),
                'revenue' => (float) Order::whereIn('status', ['paid','shipped','delivered'])->sum('total'),
            ];
        });

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    public function show(Order $order)
    {
        abort_unless(
            in_array($order->payment_status, ['paid', 'cod_pending', 'refunded'], true)
            || in_array($order->status, ['paid', 'shipped', 'delivered', 'refunded'], true),
            404
        );

        $order->load(['items.product:id,slug,name', 'history', 'user:id,name,email']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|in:' . implode(',', Order::STATUSES),
            'note' => 'nullable|string|max:500',
            'notify' => 'nullable|boolean',
        ]);

        if ($order->status === $data['status']) {
            return $this->jsonOrBack($request, false, 'الحالة الحالية بالفعل.');
        }

        DB::transaction(function () use ($order, $data) {
            $from = $order->status;
            $order->status = $data['status'];

            // Restock on cancellation/refund (tracked via StockService)
            if (in_array($data['status'], ['cancelled', 'refunded'], true)
                && ! in_array($from, ['cancelled','refunded'], true)) {
                $stockService = app(\App\Services\StockService::class);
                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        $p = Product::find($item->product_id);
                        if ($p) {
                            $stockService->apply(
                                $p,
                                (int) $item->quantity,
                                'order_cancel',
                                'Order',
                                $order->id,
                                "إلغاء الطلب {$order->order_number}",
                                'admin',
                                \Illuminate\Support\Facades\Auth::guard('admin')->id()
                            );
                        }
                    }
                }
            }

            $now = now();
            match ($data['status']) {
                'paid' => $order->paid_at = $order->paid_at ?: $now,
                'shipped' => $order->shipped_at = $order->shipped_at ?: $now,
                'delivered' => $order->delivered_at = $order->delivered_at ?: $now,
                'cancelled' => $order->cancelled_at = $now,
                'refunded' => $order->refunded_at = $now,
                default => null,
            };

            if ($data['status'] === 'paid' && $order->payment_status !== 'paid') {
                $order->payment_status = 'paid';
            }
            if ($data['status'] === 'refunded') {
                $order->payment_status = 'refunded';
            }

            $order->save();

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $from,
                'to_status' => $data['status'],
                'note' => $data['note'] ?? null,
                'changed_by_type' => 'admin',
                'changed_by_id' => Auth::guard('admin')->id(),
            ]);
        });

        cache()->forget('admin.orders.stats');

        if (! empty($data['notify'])) {
            $this->safeMail($order->fresh(), $data['status']);
        }

        return $this->jsonOrBack($request, true, 'تم تحديث حالة الطلب.');
    }

    public function updateShipping(Request $request, Order $order)
    {
        $data = $request->validate([
            'tracking_number' => 'nullable|string|max:100',
            'shipping_carrier' => 'nullable|string|max:100',
            'shipping_carrier_id' => 'nullable|integer|exists:shipping_carriers,id',
            'actual_shipping_cost' => 'nullable|numeric|min:0',
            'shipped_notes' => 'nullable|string|max:1000',
        ]);
        $order->update($data);
        return $this->jsonOrBack($request, true, 'تم تحديث بيانات الشحن.');
    }

    public function resendEmail(Request $request, Order $order)
    {
        $kind = $request->input('kind', $order->status);
        $this->safeMail($order, $kind);
        return $this->jsonOrBack($request, true, 'تم إرسال البريد للعميل.');
    }

    /** Pull live tracking from carrier API. */
    public function refreshTracking(Request $request, Order $order, \App\Services\ShippingTrackingService $tracker)
    {
        $res = $tracker->refresh($order);
        return $this->jsonOrBack($request, (bool) $res['ok'], $res['message']);
    }

    /** Retry shipment creation for orders where it previously failed. */
    public function retryShipment(Request $request, Order $order, \App\Services\ShippingDispatchService $dispatcher)
    {
        if ($order->shipment_number) {
            return $this->jsonOrBack($request, false, 'الشحنة موجودة بالفعل، لا حاجة لإعادة الإنشاء.');
        }
        if (!$order->shipping_carrier_id) {
            return $this->jsonOrBack($request, false, 'حدّد شركة الشحن أولاً.');
        }
        $res = $dispatcher->createForOrder($order->fresh());
        return $this->jsonOrBack($request, (bool) $res['ok'], $res['message'] ?? ($res['ok'] ? 'تم إنشاء الشحنة.' : 'فشل إنشاء الشحنة.'));
    }

    /** Resync live shipment status from carrier. */
    public function syncShipment(Request $request, Order $order, \App\Services\ShippingDispatchService $dispatcher)
    {
        $res = $dispatcher->syncStatus($order);
        return $this->jsonOrBack($request, (bool) $res['ok'], $res['message'] ?? 'تمت المزامنة.');
    }



    public function invoice(Order $order)
    {
        $order->load('items.product:id,slug,name');
        return view('admin.orders.invoice', compact('order'));
    }

    public function destroy(Order $order)
    {
        $order->delete();
        cache()->forget('admin.orders.stats');
        return back()->with('success', 'تم حذف الطلب.');
    }

    private function safeMail(Order $order, string $kind): void
    {
        try {
            Mail::to($order->email)->send(new OrderStatusMail($order, $kind));
        } catch (\Throwable $e) {
            Log::warning('Order mail failed', ['id' => $order->id, 'err' => $e->getMessage()]);
        }
    }

    private function jsonOrBack(Request $request, bool $ok, string $msg)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['ok' => $ok, 'message' => $msg]);
        }
        return back()->with($ok ? 'success' : 'error', $msg);
    }
}
