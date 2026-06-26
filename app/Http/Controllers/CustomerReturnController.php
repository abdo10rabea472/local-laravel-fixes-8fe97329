<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ReturnRequest;
use App\Models\ReturnRequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerReturnController extends Controller
{
    /**
     * List the current user's RMAs.
     */
    public function index()
    {
        $returns = ReturnRequest::where('user_id', Auth::id())
            ->with('order:id,order_number')
            ->latest()
            ->paginate(15);

        return view('account.returns.index', compact('returns'));
    }

    /**
     * Show "request return" form for a specific order.
     */
    public function create(Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);
        abort_unless(in_array($order->status, ['paid','shipped','delivered']), 422, 'لا يمكن إرجاع هذا الطلب.');

        $order->load('items');
        return view('account.returns.create', compact('order'));
    }

    public function store(Request $request, Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);
        abort_unless(in_array($order->status, ['paid','shipped','delivered']), 422);

        $data = $request->validate([
            'reason' => ['required','in:defective,wrong_item,not_as_described,damaged,no_longer_wanted,other'],
            'customer_note' => ['nullable','string','max:1000'],
            'items' => ['required','array','min:1'],
            'items.*.order_item_id' => ['required','integer','exists:order_items,id'],
            'items.*.quantity' => ['required','integer','min:1'],
        ]);

        $orderItems = $order->items()->get()->keyBy('id');

        $return = DB::transaction(function () use ($order, $data, $orderItems) {
            $return = ReturnRequest::create([
                'rma_number' => ReturnRequest::generateNumber(),
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'status' => 'pending',
                'reason' => $data['reason'],
                'customer_note' => $data['customer_note'] ?? null,
            ]);

            $total = 0;
            foreach ($data['items'] as $row) {
                $orderItem = $orderItems[$row['order_item_id']] ?? null;
                if (!$orderItem || $orderItem->order_id !== $order->id) continue;
                $qty = min((int) $row['quantity'], (int) $orderItem->quantity);
                if ($qty <= 0) continue;

                $line = (float) $orderItem->unit_price * $qty;
                $total += $line;

                ReturnRequestItem::create([
                    'return_request_id' => $return->id,
                    'order_item_id' => $orderItem->id,
                    'product_id' => $orderItem->product_id,
                    'quantity' => $qty,
                    'unit_price' => $orderItem->unit_price,
                    'line_total' => $line,
                    'restock' => true,
                ]);
            }
            $return->update(['refund_amount' => $total]);
            return $return;
        });

        return redirect()->route('account.returns.index')->with('success', "تم إنشاء طلب الإرجاع {$return->rma_number} بنجاح.");
    }

    public function show(ReturnRequest $return)
    {
        abort_unless($return->user_id === Auth::id(), 403);
        $return->load('items.product:id,name','order');
        return view('account.returns.show', compact('return'));
    }
}
