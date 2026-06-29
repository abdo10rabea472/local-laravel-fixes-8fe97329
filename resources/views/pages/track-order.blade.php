@extends('layouts.front')

@section('content')
<section class="py-12 bg-slate-50 min-h-[60vh]">
    <div class="max-w-3xl mx-auto px-4">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-slate-800">{{ __('app.track_title') }}</h1>
            <p class="text-slate-600 mt-2">{{ __('app.track_subtitle') }}</p>
        </div>

        <form method="GET" action="{{ route('track-order') }}" class="bg-white p-6 rounded-2xl shadow-sm grid md:grid-cols-3 gap-3">
            <input type="text" name="order_number" value="{{ request('order_number') }}" placeholder="{{ __('app.track_order_number') }}" required
                   class="md:col-span-1 px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500">
            <input type="email" name="email" value="{{ request('email') }}" placeholder="{{ __('app.track_email') }}" required
                   class="md:col-span-1 px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500">
            <button class="md:col-span-1 bg-violet-600 hover:bg-violet-700 text-white py-3 rounded-lg font-semibold">
                <i class="fas fa-search"></i> {{ __('app.track_button') }}
            </button>
        </form>

        @if($notFound)
            <div class="mt-6 bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg text-center">
                <i class="fas fa-exclamation-triangle"></i> {{ __('app.track_not_found') }}
            </div>
        @endif

        @if($order)
            @php
                $statusLabels = [
                    'pending'   => __('app.track_status_pending'),
                    'paid'      => __('app.track_status_paid'),
                    'shipped'   => __('app.track_status_shipped'),
                    'delivered' => __('app.track_status_delivered'),
                    'cancelled' => __('app.track_status_cancelled'),
                ];
            @endphp
            <div class="mt-8 bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 border-b">
                    <div class="flex justify-between items-center flex-wrap gap-2">
                        <div>
                            <h2 class="text-xl font-bold text-slate-800">{{ __('app.track_order_label') }} #{{ $order->order_number }}</h2>
                            <p class="text-sm text-slate-500">{{ $order->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                        <span class="px-4 py-1.5 rounded-full text-sm font-semibold
                            @if($order->status==='delivered') bg-emerald-100 text-emerald-700
                            @elseif($order->status==='shipped') bg-blue-100 text-blue-700
                            @elseif($order->status==='paid') bg-amber-100 text-amber-700
                            @elseif($order->status==='cancelled') bg-red-100 text-red-700
                            @else bg-slate-100 text-slate-700 @endif">
                            {{ $statusLabels[$order->status] ?? $order->status }}
                        </span>
                    </div>
                </div>

                <div class="p-6">
                    <h3 class="font-semibold text-slate-700 mb-3">{{ __('app.track_products') }}</h3>
                    <div class="space-y-2">
                        @foreach($order->items as $item)
                        <div class="flex justify-between items-center py-2 border-b last:border-0">
                            <div>{{ $item->product_name ?? $item->product?->name }}</div>
                            <div class="text-sm text-slate-500">{{ $item->quantity }} × {{ number_format($item->unit_price, 2) }}</div>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-4 pt-4 border-t flex justify-between font-bold text-lg">
                        <span>{{ __('app.track_total') }}</span>
                        <span class="text-violet-700">{{ number_format($order->total, 2) }} {{ __('app.shared_currency_egp') }}</span>
                    </div>
                </div>

                @if($order->history && $order->history->count())
                <div class="p-6 border-t bg-slate-50">
                    <h3 class="font-semibold text-slate-700 mb-3">{{ __('app.track_history') }}</h3>
                    <ul class="space-y-2">
                        @foreach($order->history as $h)
                        <li class="flex justify-between text-sm">
                            <span class="text-slate-700">{{ $statusLabels[$h->status] ?? $h->status }}</span>
                            <span class="text-slate-500">{{ $h->created_at->format('Y-m-d H:i') }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        @endif
    </div>
</section>
@endsection
