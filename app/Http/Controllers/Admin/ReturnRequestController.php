<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReturnRequestController extends Controller
{
    public function __construct(private StockService $stock) {}

    public function index(Request $request)
    {
        $status = $request->string('status')->toString();
        $search = trim((string) $request->get('q', ''));

        $returns = ReturnRequest::query()
            ->with(['order:id,order_number,email,total', 'user:id,name'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('rma_number', 'like', "%{$search}%")
                      ->orWhereHas('order', fn ($o) => $o->where('order_number', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total'     => ReturnRequest::count(),
            'pending'   => ReturnRequest::where('status', 'pending')->count(),
            'approved'  => ReturnRequest::where('status', 'approved')->count(),
            'refunded'  => ReturnRequest::where('status', 'refunded')->count(),
            'total_refunded' => (float) ReturnRequest::where('status', 'refunded')->sum('refund_amount'),
        ];

        return view('admin.returns.index', compact('returns','stats','status','search'));
    }

    public function show(ReturnRequest $return)
    {
        $return->load(['items.product:id,name,sku','items.orderItem','order','user']);
        return view('admin.returns.show', compact('return'));
    }

    public function updateStatus(Request $request, ReturnRequest $return)
    {
        $data = $request->validate([
            'status' => ['required','in:' . implode(',', ReturnRequest::STATUSES)],
            'admin_note' => ['nullable','string','max:1000'],
            'refund_amount' => ['nullable','numeric','min:0'],
        ]);

        $adminId = Auth::guard('admin')->id();

        DB::transaction(function () use ($return, $data, $adminId) {
            $oldStatus = $return->status;
            $newStatus = $data['status'];

            $update = ['status' => $newStatus];
            if (array_key_exists('admin_note', $data)) $update['admin_note'] = $data['admin_note'];
            if (array_key_exists('refund_amount', $data)) $update['refund_amount'] = $data['refund_amount'];

            if ($newStatus === 'approved' && !$return->approved_at) $update['approved_at'] = now();
            if ($newStatus === 'received' && !$return->received_at) $update['received_at'] = now();
            if ($newStatus === 'refunded' && !$return->refunded_at) $update['refunded_at'] = now();

            $return->update($update);

            // Auto-restock when transitioning into "received" (only once)
            if ($newStatus === 'received' && $oldStatus !== 'received') {
                foreach ($return->items()->with('product')->get() as $item) {
                    if ($item->restock && $item->product) {
                        $this->stock->apply(
                            $item->product,
                            (int) $item->quantity,
                            'return',
                            'ReturnRequest',
                            $return->id,
                            "RMA {$return->rma_number}",
                            'admin',
                            $adminId
                        );
                    }
                }
            }
        });

        if ($request->expectsJson() || $request->ajax()) {
            $return->refresh();
            return response()->json([
                'ok' => true,
                'status' => $return->status,
                'label' => $return->statusLabel(),
                'color' => $return->statusColor(),
            ]);
        }

        return back()->with('success', 'تم تحديث حالة المرتجع.');
    }

    public function destroy(ReturnRequest $return)
    {
        $return->delete();
        return redirect()->route('admin.returns.index')->with('success', 'تم حذف طلب الإرجاع.');
    }
}
