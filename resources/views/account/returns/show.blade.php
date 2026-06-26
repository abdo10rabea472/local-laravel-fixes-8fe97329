@extends('account.layout')

@section('account-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-800">{{ $return->rma_number }}</h1>
        <a href="{{ route('account.returns.index') }}" class="text-violet-600 text-sm font-semibold">← مرتجعاتي</a>
    </div>

    <div class="bg-white rounded-2xl border p-6">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <span class="px-4 py-2 rounded-full text-sm font-bold bg-{{ $return->statusColor() }}-100 text-{{ $return->statusColor() }}-700">{{ $return->statusLabel() }}</span>
            <p class="text-sm text-slate-500">الطلب: <strong>{{ $return->order?->order_number }}</strong></p>
        </div>

        <table class="w-full text-sm">
            <thead class="text-xs text-slate-500 uppercase border-b">
                <tr>
                    <th class="py-2 text-right">المنتج</th>
                    <th class="py-2 text-right">الكمية</th>
                    <th class="py-2 text-right">السعر</th>
                    <th class="py-2 text-right">الإجمالي</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($return->items as $item)
                    <tr>
                        <td class="py-3 font-medium">{{ $item->product?->name }}</td>
                        <td class="py-3">{{ $item->quantity }}</td>
                        <td class="py-3">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="py-3 font-semibold">{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="border-t mt-4 pt-4 grid sm:grid-cols-2 gap-4 text-sm">
            <div><strong class="text-slate-500">السبب:</strong> {{ $return->reasonLabel() }}</div>
            <div><strong class="text-slate-500">المبلغ المتوقع:</strong> {{ number_format($return->refund_amount, 2) }} ج.م</div>
        </div>

        @if($return->customer_note)
            <div class="mt-4 p-4 bg-slate-50 rounded-xl text-sm">
                <strong class="text-slate-500 block mb-1">ملاحظتك:</strong>{{ $return->customer_note }}
            </div>
        @endif

        @if($return->admin_note)
            <div class="mt-4 p-4 bg-violet-50 rounded-xl text-sm">
                <strong class="text-violet-700 block mb-1">رد الإدارة:</strong>{{ $return->admin_note }}
            </div>
        @endif
    </div>
</div>
@endsection
