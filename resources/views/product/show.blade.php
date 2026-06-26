@extends('layouts.front')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/product.css') }}">
@endpush

@section('content')
@php
    $primaryImage = $product->images->first();
    $imageUrl = $primaryImage ? $primaryImage->getUrl('large') : site_setting_url('default_product_image', asset('imges/products/default.jpg'));
    $hasDiscount = $product->sale_price && $product->sale_price < $product->price;
    $displayPrice = $product->effective_price;
    $college = $product->category?->parent;
    $accent = $college?->primary_color ?? '#6366f1';
@endphp

<main class="bg-slate-50 min-h-screen py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex flex-wrap items-center gap-2 text-sm bg-white/80 backdrop-blur px-5 py-4 rounded-2xl border border-slate-100 mb-8 shadow-sm">
            <a href="{{ route('home') }}" class="text-slate-500 hover:text-violet-600 font-medium"><i class="fa-solid fa-house mr-1"></i> Home</a>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
            <a href="{{ route('products.index') }}" class="text-slate-500 hover:text-violet-600 font-medium">Products</a>
            @if($product->category)
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
            <a href="{{ route('category.show', $product->category->slug) }}" class="text-slate-500 hover:text-violet-600 font-medium">{{ $product->category->name }}</a>
            @endif
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
            <span class="text-slate-900 font-semibold truncate max-w-[200px]">{{ $product->name }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16" data-id="{{ $product->id }}" data-price="{{ $displayPrice }}">
            <div class="space-y-4">
                <div class="relative rounded-3xl overflow-hidden bg-white shadow-xl border border-slate-200">
                    <img id="main-product-image" src="{{ $imageUrl }}" alt="{{ $product->name }}" loading="eager" fetchpriority="high" decoding="async" class="w-full aspect-square object-contain bg-slate-50 p-6">
                    @if($product->category)
                    <div class="absolute top-4 left-4 text-white text-xs font-bold px-4 py-1.5 rounded-xl shadow-lg" style="background: {{ $accent }}">
                        {{ $product->category->name }}
                    </div>
                    @endif
                </div>
                @if($product->images->count() > 1)
                <div class="grid grid-cols-4 gap-3">
                    @foreach($product->images as $image)
                    <button type="button" onclick="document.getElementById('main-product-image').src='{{ $image->getUrl('large') }}'"
                        class="aspect-square rounded-2xl border-2 border-transparent hover:border-violet-300 overflow-hidden bg-white p-1">
                        <img src="{{ $image->getUrl('thumb') }}" alt="" loading="lazy" decoding="async" class="w-full h-full object-contain">
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            <div class="space-y-6">
                <div>
                    @if($product->featured)
                    <span class="inline-block px-3 py-1 bg-amber-100 text-amber-700 text-xs font-bold rounded-full mb-3">Featured</span>
                    @endif
                    <h1 class="text-3xl sm:text-4xl font-black text-slate-900 leading-tight">{{ $product->name }}</h1>
                    @if($product->sku)<p class="text-sm text-slate-400 mt-2">SKU: {{ $product->sku }}</p>@endif
                </div>

                <div class="flex items-end gap-3">
                    @if($hasDiscount)
                        <span class="text-4xl font-black" style="color: {{ $accent }}">{{ number_format($displayPrice, 2) }} <span class="text-xl font-normal text-slate-500">EGP</span></span>
                        <del class="text-lg text-slate-400">{{ number_format($product->price, 2) }} EGP</del>
                    @else
                        <span class="text-4xl font-black text-slate-900">{{ number_format($displayPrice, 2) }} <span class="text-xl font-normal text-slate-500">EGP</span></span>
                    @endif
                </div>

                @if($product->short_description)
                <p class="text-slate-600 leading-relaxed text-lg">{{ $product->short_description }}</p>
                @endif

                <p class="font-semibold {{ $product->isInStock() ? 'text-emerald-600' : 'text-rose-600' }}">
                    {{ $product->isInStock() ? 'In stock · ' . $product->stock . ' available' : 'Out of stock' }}
                </p>

                @if($product->isInStock())
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button" onclick="addToCart(this)" class="add-btn flex-1 text-white font-bold py-4 rounded-2xl text-lg shadow-lg transition active:scale-95" style="background: {{ $accent }}">
                        Add to Cart
                    </button>
                    <button type="button" onclick="addToCart(this); window.location.href='{{ route('checkout') }}'"
                        class="flex-1 bg-slate-900 hover:bg-black text-white font-bold py-4 rounded-2xl text-lg shadow-lg transition">
                        Buy Now
                    </button>
                </div>
                @endif

                <div class="grid grid-cols-3 gap-4 pt-6 border-t border-slate-200">
                    <div class="text-center"><i class="fa-solid fa-truck text-2xl mb-2" style="color: {{ $accent }}"></i><p class="text-xs font-semibold text-slate-600">Free Shipping</p></div>
                    <div class="text-center"><i class="fa-solid fa-shield-halved text-2xl mb-2" style="color: {{ $accent }}"></i><p class="text-xs font-semibold text-slate-600">Warranty</p></div>
                    <div class="text-center"><i class="fa-solid fa-rotate text-2xl mb-2" style="color: {{ $accent }}"></i><p class="text-xs font-semibold text-slate-600">30-Day Return</p></div>
                </div>

                @if($product->description)
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                    <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                        <span class="h-6 w-1 rounded-full" style="background: {{ $accent }}"></span>
                        Description
                    </h2>
                    <div class="text-slate-700 leading-relaxed whitespace-pre-line">{{ $product->description }}</div>
                </div>
                @endif
            </div>
        </div>

        @if($relatedProducts->isNotEmpty())
        <div class="mt-16">
            <h2 class="text-2xl font-black text-slate-900 mb-6">Related Products</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach($relatedProducts as $related)
                    @include('components.product-card', ['product' => $related])
                @endforeach
            </div>
        </div>
        @endif
    </div>
</main>
@endsection
