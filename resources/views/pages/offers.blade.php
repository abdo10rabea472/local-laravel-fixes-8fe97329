@extends('layouts.front')

@section('content')
<section class="bg-gradient-to-br from-rose-500 via-pink-600 to-red-700 text-white py-16">
    <div class="max-w-6xl mx-auto px-4 text-center">
        <h1 class="text-4xl font-bold mb-3"><i class="fas fa-fire"></i> {{ __('app.offers_hero_title') }}</h1>
        <p class="text-rose-100">{{ __('app.offers_hero_subtitle') }}</p>
    </div>
</section>

@if($coupons->count())
<section class="py-12 bg-slate-50">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-2xl font-bold text-slate-800 mb-6"><i class="fas fa-ticket text-violet-600"></i> {{ __('app.offers_available_coupons') }}</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($coupons as $c)
            <div class="bg-white rounded-2xl border-2 border-dashed border-violet-300 p-5 hover:shadow-lg transition">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <div class="text-xs text-slate-500">{{ __('app.offers_coupon_code') }}</div>
                        <div class="text-2xl font-bold text-violet-700">{{ $c->code }}</div>
                    </div>
                    <span class="bg-rose-100 text-rose-700 px-3 py-1 rounded-full text-sm font-bold">
                        @if($c->type==='percent') {{ rtrim(rtrim($c->value,'0'),'.') }}% @else {{ number_format($c->value,2) }} {{ __('app.shared_currency_egp') }} @endif
                    </span>
                </div>
                @if($c->description)<p class="text-sm text-slate-600 mb-2">{{ $c->description }}</p>@endif
                @if($c->ends_at)<p class="text-xs text-slate-400"><i class="far fa-clock"></i> {{ __('app.offers_ends_at') }} {{ $c->ends_at->format('Y-m-d') }}</p>@endif
                <button onclick="navigator.clipboard.writeText('{{ $c->code }}'); this.innerText='{{ __('app.offers_copied') }}'"
                        class="mt-3 w-full bg-violet-600 text-white py-2 rounded-lg text-sm hover:bg-violet-700">{{ __('app.offers_copy_code') }}</button>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-2xl font-bold text-slate-800 mb-6"><i class="fas fa-tags text-rose-500"></i> {{ __('app.offers_discounted_products') }}</h2>

        @if($products->isEmpty())
            <p class="text-center text-slate-500 py-12">{{ __('app.offers_empty') }}</p>
        @else
            <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($products as $p)
                @php
                    $discount = $p->price > 0 ? round((($p->price - $p->sale_price) / $p->price) * 100) : 0;
                @endphp
                <a href="{{ route('product.show', $p->slug) }}" class="bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-lg transition group relative">
                    @if($discount > 0)
                        <span class="absolute top-3 right-3 bg-rose-500 text-white text-xs font-bold px-2 py-1 rounded-lg z-10">-{{ $discount }}%</span>
                    @endif
                    <div class="aspect-square bg-slate-100 overflow-hidden">
                        <img src="{{ $p->images->first() ? asset('storage/'.$p->images->first()->path) : (site_setting_url('default_product_image') ?: asset('imges/products/default.jpg')) }}" alt="{{ $p->name }}" onerror="this.onerror=null;this.src='{{ site_setting_url('default_product_image') ?: asset('imges/products/default.jpg') }}'" class="w-full h-full object-cover group-hover:scale-105 transition">

                    </div>
                    <div class="p-4">
                        <div class="text-xs text-slate-500">{{ $p->category?->name }}</div>
                        <div class="font-semibold text-slate-800 line-clamp-2 my-1">{{ $p->name }}</div>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-rose-600 font-bold">{{ number_format($p->sale_price, 2) }}</span>
                            <span class="text-slate-400 line-through text-sm">{{ number_format($p->price, 2) }}</span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            <div class="mt-8">{{ $products->links() }}</div>
        @endif
    </div>
</section>
@endsection
