@extends('account.layout')
@section('account_content')

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    @php
        $cards = [
            ['fa-receipt', __('app.acc_total_orders'), $ordersCount, 'from-violet-500 to-indigo-600'],
            ['fa-wallet', __('app.acc_total_spent'), number_format($totalSpent, 0) . ' ' . __('app.cat_egp'), 'from-emerald-500 to-teal-600'],
            ['fa-star', __('app.acc_my_reviews'), $reviewsCount, 'from-amber-500 to-orange-600'],
        ];
    @endphp
    @foreach($cards as [$ic, $lbl, $val, $grad])
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br {{ $grad }} text-white grid place-items-center text-xl shadow-lg">
            <i class="fa-solid {{ $ic }}"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-bold">{{ $lbl }}</p>
            <p class="text-2xl font-black text-slate-900 mt-0.5">{{ $val }}</p>
        </div>
    </div>
    @endforeach
</div>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-violet-50/50 to-transparent">
        <h3 class="font-black text-slate-900 flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-violet-600"></i> {{ __('app.acc_recent_orders') }}
        </h3>
        <a href="{{ route('account.orders') }}" class="text-xs text-violet-600 font-bold hover:underline">{{ __('app.acc_view_all') }}</a>
    </div>
    @if($recentOrders->count())
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-xs text-slate-600"><tr>
            <th class="p-3 text-right">{{ __('app.acc_order_number') }}</th><th class="p-3">{{ __('app.acc_items') }}</th><th class="p-3">{{ __('app.acc_total') }}</th><th class="p-3">{{ __('app.acc_status') }}</th><th class="p-3">{{ __('app.acc_date') }}</th><th class="p-3"></th>
        </tr></thead>
        <tbody>
            @foreach($recentOrders as $o)
            <tr class="border-t border-slate-100 hover:bg-violet-50/40 transition">
                <td class="p-3 font-mono font-bold text-violet-700">{{ $o->order_number }}</td>
                <td class="p-3 text-center">{{ $o->items_count }}</td>
                <td class="p-3 text-center font-bold">{{ number_format($o->total, 2) }}</td>
                <td class="p-3 text-center"><span class="text-xs px-2 py-1 rounded-full bg-{{ $o->statusBadgeColor() }}-50 text-{{ $o->statusBadgeColor() }}-700 font-bold">{{ $o->statusLabel() }}</span></td>
                <td class="p-3 text-center text-xs text-slate-500">{{ $o->created_at->format('Y-m-d') }}</td>
                <td class="p-3 text-center"><a href="{{ route('account.orders.show', $o) }}" class="text-violet-600 text-xs font-bold hover:underline">{{ __('app.acc_details') }}</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @else
    <div class="p-12 text-center">
        <div class="w-16 h-16 rounded-2xl bg-violet-50 text-violet-600 grid place-items-center text-2xl mx-auto mb-3">
            <i class="fa-solid fa-box-open"></i>
        </div>
        <p class="text-slate-500 mb-4">{{ __('app.acc_no_orders_yet') }}</p>
        <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-violet-600 to-indigo-600 text-white font-bold rounded-xl shadow-md shadow-violet-500/30 hover:opacity-90">
            <i class="fa-solid fa-bag-shopping"></i> {{ __('app.acc_shop_now') }}
        </a>
    </div>
    @endif
</div>
@endsection
