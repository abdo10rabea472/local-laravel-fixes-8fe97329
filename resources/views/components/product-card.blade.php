@php
    $primaryImage = $product->images->first();
    $imageUrl = $primaryImage
        ? $primaryImage->getUrl('medium')
        : site_setting_url('default_product_image', asset('imges/products/default.jpg'));
    $displayPrice = $product->effective_price;
    $compareAt = $product->compare_at_price;
    $hasDiscount = $compareAt !== null && $compareAt > $displayPrice;
    $discountPercent = $product->discount_percent;
    $inStock = $product->stock > 0;
@endphp

<article class="group flex flex-col rounded-2xl bg-white border border-slate-200/80 overflow-hidden shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300" data-id="{{ $product->id }}" data-price="{{ $displayPrice }}">
    <a href="{{ route('product.show', $product->slug) }}" class="relative aspect-square bg-slate-50 flex items-center justify-center p-4 overflow-hidden">
        @if($hasDiscount && $discountPercent > 0)
            <span class="absolute top-3 left-3 z-10 bg-rose-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-full">-{{ $discountPercent }}%</span>
        @endif
        @if($product->featured ?? false)
            <span class="absolute top-3 right-3 z-10 bg-amber-400 text-slate-900 text-[10px] font-bold px-2.5 py-1 rounded-full">{{ __('app.shared_featured') }}</span>
        @endif
        <img src="{{ $imageUrl }}" alt="{{ $product->name }}" loading="lazy"
            onerror="this.onerror=null;this.src='{{ site_setting_url('default_product_image') ?: asset('imges/products/default.jpg') }}'"
            class="max-h-full max-w-full object-contain transition-transform duration-500 group-hover:scale-105">
    </a>
    <div class="p-4 flex flex-col flex-1">
        @if($product->relationLoaded('category') && $product->category)
            <span class="text-[10px] font-bold uppercase tracking-wider text-violet-600 mb-1">{{ $product->category->name }}</span>
        @endif
        <h3 class="font-bold text-slate-900 line-clamp-1 group-hover:text-violet-700 transition-colors">
            <a href="{{ route('product.show', $product->slug) }}">{{ $product->name }}</a>
        </h3>
        @if($product->short_description)
            <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $product->short_description }}</p>
        @endif
        <div class="mt-auto pt-4 flex items-end justify-between gap-2 border-t border-slate-100 mt-3">
            <div>
                @if($hasDiscount)
                    <del class="text-xs text-slate-400 block">{{ number_format($compareAt, 2) }} EGP</del>
                @endif
                <span class="text-lg font-black text-slate-900">{{ number_format($displayPrice, 2) }} <span class="text-xs font-bold text-slate-500">EGP</span></span>
            </div>
            @if($inStock)
                <button type="button" onclick="event.preventDefault(); addToCart(this);"
                    class="add-btn shrink-0 h-9 px-3 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl transition-colors">
                    {{ __('app.product_add') }}
                </button>
            @else
                <span class="text-[10px] font-bold text-rose-500 bg-rose-50 px-2 py-1 rounded-lg">{{ __('app.shared_out_of_stock') }}</span>
            @endif
        </div>
    </div>
</article>
