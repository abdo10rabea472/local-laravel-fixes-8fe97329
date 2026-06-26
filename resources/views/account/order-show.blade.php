@extends('account.layout')
@section('account_content')
<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <h1 class="text-2xl font-black text-slate-900">طلب <span class="font-mono text-violet-700">{{ $order->order_number }}</span></h1>
    <a href="{{ route('account.orders') }}" class="text-sm text-slate-500">← العودة للطلبات</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-100 font-bold">المنتجات</div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs"><tr><th class="p-3 text-right">المنتج</th><th class="p-3">الكمية</th><th class="p-3">السعر</th><th class="p-3">الإجمالي</th></tr></thead>
            <tbody>
                @foreach($order->items as $it)
                <tr class="border-t border-slate-100">
                    <td class="p-3">
                        @if($it->product)<a href="{{ route('product.show', $it->product->slug) }}" class="text-violet-700">{{ $it->product_name }}</a>
                        @else {{ $it->product_name }} @endif
                    </td>
                    <td class="p-3 text-center">{{ $it->quantity }}</td>
                    <td class="p-3 text-center">{{ number_format($it->unit_price, 2) }}</td>
                    <td class="p-3 text-center font-bold">{{ number_format($it->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-slate-50">
                <tr><td colspan="3" class="p-2 text-left">الإجمالي الفرعي</td><td class="p-2 text-center">{{ number_format($order->subtotal, 2) }}</td></tr>
                @if($order->discount_amount > 0)
                <tr><td colspan="3" class="p-2 text-left">الخصم</td><td class="p-2 text-center text-rose-600">-{{ number_format($order->discount_amount, 2) }}</td></tr>
                @endif
                <tr><td colspan="3" class="p-2 text-left">الشحن</td><td class="p-2 text-center">{{ number_format($order->shipping_cost, 2) }}</td></tr>
                <tr><td colspan="3" class="p-2 text-left font-bold">الإجمالي</td><td class="p-2 text-center font-black text-violet-700">{{ number_format($order->total, 2) }} {{ $order->currency }}</td></tr>
            </tfoot>
        </table>
    </div>
    <div class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 text-sm">
            <h3 class="font-bold mb-3">حالة الطلب</h3>
            <span class="px-3 py-1.5 rounded-full bg-{{ $order->statusBadgeColor() }}-50 text-{{ $order->statusBadgeColor() }}-700 font-bold text-sm">{{ $order->statusLabel() }}</span>
            @if($order->tracking_number)
            <p class="mt-3 text-xs">رقم التتبع: <b>{{ $order->tracking_number }}</b></p>
            @php $carrier = $order->relationLoaded('carrier') ? $order->carrier : null; $trackUrl = $carrier?->buildTrackingUrl($order->tracking_number); @endphp
            <p class="text-xs">شركة الشحن: {{ $carrier?->name ?? $order->shipping_carrier ?: '—' }}</p>
            @if($trackUrl)
                <a href="{{ $trackUrl }}" target="_blank" class="inline-block mt-2 text-violet-600 text-xs font-semibold hover:underline">
                    <i class="fa-solid fa-truck-fast ml-1"></i> تتبع الشحنة
                </a>
            @endif
            @endif

            @if(in_array($order->status, ['paid','shipped','delivered']))
                <a href="{{ route('account.returns.create', $order) }}" class="mt-4 block text-center px-4 py-2 rounded-xl bg-amber-50 text-amber-700 font-semibold text-xs hover:bg-amber-100">
                    <i class="fa-solid fa-rotate-left ml-1"></i> طلب إرجاع
                </a>
            @endif
            <hr class="my-3">
            <h4 class="font-bold text-xs mb-2">السجل</h4>
            <ul class="space-y-1 text-xs">
                @foreach($order->history as $h)
                    <li>{{ $h->created_at->format('Y-m-d H:i') }} — <b>{{ $h->to_status }}</b></li>
                @endforeach
            </ul>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 text-sm">
            <h3 class="font-bold mb-3">الشحن إلى</h3>
            <p>{{ $order->customer_name ?: auth()->user()->name }}</p>
            <p>{{ $order->email }}</p>
            <p>{{ $order->phone }}</p>
            <p class="text-slate-600 mt-2">{{ $order->shipping_address }}, {{ $order->shipping_city }}, {{ $order->shipping_region }}, {{ $order->shipping_country }}</p>
        </div>
    </div>
</div>
@endsection
