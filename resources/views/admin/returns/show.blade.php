@extends('admin.layouts.app')

@section('title', 'Return #' . $return->rma_number)

@section('content')
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-gray-100">{{ $return->rma_number }}</h1>
            <p class="text-sm text-slate-500 dark:text-gray-400 mt-1">
                Order: <a href="{{ route('admin.orders.show', $return->order_id) }}" class="text-violet-600 dark:text-violet-400 font-semibold hover:underline">{{ $return->order?->order_number }}</a>
            </p>
        </div>
        <a href="{{ route('admin.returns.index') }}" class="text-violet-600 dark:text-violet-400 hover:underline text-sm font-semibold">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 text-emerald-700 dark:text-emerald-300">{{ session('success') }}</div>
    @endif

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Items + customer info --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-sm border p-6">
                <h2 class="font-bold text-slate-800 dark:text-gray-100 mb-4">Return Items</h2>
                <table class="w-full text-sm">
                    <thead class="text-xs text-slate-500 dark:text-gray-400 uppercase border-b">
                        <tr>
                            <th class="py-2 text-left">Product</th>
                            <th class="py-2 text-left">Qty</th>
                            <th class="py-2 text-left">Price</th>
                            <th class="py-2 text-left">Total</th>
                            <th class="py-2 text-left">Restock</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($return->items as $item)
                            <tr>
                                <td class="py-3 font-medium text-slate-800 dark:text-gray-100">{{ $item->product?->name ?? '(deleted)' }}</td>
                                <td class="py-3 text-slate-600 dark:text-gray-300">{{ $item->quantity }}</td>
                                <td class="py-3 text-slate-600 dark:text-gray-300">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="py-3 font-semibold text-slate-800 dark:text-gray-100">{{ number_format($item->line_total, 2) }}</td>
                                <td class="py-3">
                                    @if($item->restock)
                                        <span class="text-emerald-600 text-xs font-bold">✓ Yes</span>
                                    @else
                                        <span class="text-slate-400 dark:text-gray-500 dark:text-gray-400 text-xs">No</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t-2 border-slate-200 dark:border-gray-800">
                        <tr>
                            <td colspan="3" class="py-3 text-left font-bold">Total Refund</td>
                            <td colspan="2" class="py-3 font-bold text-emerald-600 text-lg">{{ number_format($return->refund_amount, 2) }} EGP</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-sm border p-6">
                <h2 class="font-bold text-slate-800 dark:text-gray-100 mb-3">Return Reason</h2>
                <p class="text-slate-700 dark:text-gray-200 font-semibold mb-2">{{ $return->reasonLabel() }}</p>
                @if($return->customer_note)
                    <div class="bg-slate-50 dark:bg-dark-800 rounded-xl p-4 mt-3">
                        <p class="text-xs text-slate-500 dark:text-gray-400 mb-1 font-semibold">Customer note:</p>
                        <p class="text-sm text-slate-700 dark:text-gray-200">{{ $return->customer_note }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Status panel --}}
        <div class="space-y-6">
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-sm border p-6">
                <h2 class="font-bold text-slate-800 dark:text-gray-100 mb-4">Current Status</h2>
                <span class="inline-block px-4 py-2 rounded-full text-sm font-bold bg-{{ $return->statusColor() }}-100 text-{{ $return->statusColor() }}-700">
                    {{ $return->statusLabel() }}
                </span>

                <form method="POST" action="{{ route('admin.returns.status', $return) }}" class="mt-6 space-y-4">
                    @csrf @method('PATCH')
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-gray-200 mb-1">Change To</label>
                        <select name="status" class="w-full rounded-xl border-slate-200 dark:border-gray-800 text-sm">
                            @foreach(\App\Models\ReturnRequest::STATUSES as $s)
                                <option value="{{ $s }}" @selected($return->status === $s)>{{ (new \App\Models\ReturnRequest(['status'=>$s]))->statusLabel() }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-amber-600 mt-1">Selecting "Received" will restock the items automatically.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-gray-200 mb-1">Refund Amount</label>
                        <input type="number" step="0.01" min="0" name="refund_amount" value="{{ $return->refund_amount }}" class="w-full rounded-xl border-slate-200 dark:border-gray-800 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-gray-200 mb-1">Admin Note</label>
                        <textarea name="admin_note" rows="3" class="w-full rounded-xl border-slate-200 dark:border-gray-800 text-sm">{{ $return->admin_note }}</textarea>
                    </div>
                    <button class="w-full px-4 py-2 rounded-xl bg-violet-600 text-white font-semibold hover:bg-violet-700">Save Changes</button>
                </form>
            </div>

            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-sm border p-6 text-sm">
                <h3 class="font-bold text-slate-800 dark:text-gray-100 mb-3">Timeline</h3>
                <ul class="space-y-2 text-slate-600 dark:text-gray-300">
                    <li>Created: <span class="font-semibold">{{ $return->created_at->format('Y-m-d H:i') }}</span></li>
                    @if($return->approved_at)<li>Approved: <span class="font-semibold">{{ $return->approved_at->format('Y-m-d H:i') }}</span></li>@endif
                    @if($return->received_at)<li>Received: <span class="font-semibold">{{ $return->received_at->format('Y-m-d H:i') }}</span></li>@endif
                    @if($return->refunded_at)<li>Refunded: <span class="font-semibold">{{ $return->refunded_at->format('Y-m-d H:i') }}</span></li>@endif
                </ul>
            </div>

            {{-- Aramex pickup --}}
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-sm border p-6">
                <h3 class="font-bold text-slate-800 dark:text-gray-100 mb-3 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-orange-500 text-white flex items-center justify-center text-xs font-bold">A</span>
                    Aramex Pickup
                </h3>
                @if($return->pickup_guid)
                    <div class="bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 rounded-xl p-3 text-xs space-y-1">
                        <p class="font-bold text-emerald-700 dark:text-emerald-300">✓ Pickup scheduled</p>
                        <p>Reference: <span class="font-mono font-bold">{{ $return->pickup_reference }}</span></p>
                        <p>Time: {{ $return->pickup_scheduled_at?->format('Y-m-d H:i') }}</p>
                    </div>
                @else
                    <p class="text-xs text-slate-500 dark:text-gray-400 mb-3">Schedule a pickup from the customer via Aramex.</p>
                    <form method="POST" action="{{ route('admin.returns.aramex-pickup', $return) }}" onsubmit="return confirm('Schedule Aramex pickup now?')">
                        @csrf
                        <button class="w-full px-4 py-2 rounded-xl bg-orange-600 text-white font-semibold hover:bg-orange-700 text-sm">
                            <i class="fa-solid fa-truck-pickup mr-2"></i> Schedule Pickup
                        </button>
                    </form>
                @endif
                @if(session('error'))
                    <p class="mt-3 text-xs text-rose-600 dark:text-rose-400">{{ session('error') }}</p>
                @endif
            </div>

            <form method="POST" action="{{ route('admin.returns.destroy', $return) }}" onsubmit="return confirm('Permanently delete?')">
                @csrf @method('DELETE')
                <button class="w-full px-4 py-2 rounded-xl bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300 font-semibold hover:bg-rose-100">
                    <i class="fa-solid fa-trash mr-2"></i> Delete This Request
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
