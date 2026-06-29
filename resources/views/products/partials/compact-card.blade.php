@php
    $primaryImage = $product->images->first();
    $imageUrl = $primaryImage
        ? $primaryImage->getUrl('thumb')
        : site_setting_url('default_product_image', asset('imges/products/default.jpg'));
    $displayPrice = $product->effective_price;
    $compareAt = $product->compare_at_price;
    $hasDiscount = $compareAt !== null && $compareAt > $displayPrice;
    $discountPercent = $product->discount_percent;
    $inStock = $product->stock > 0;
@endphp
@php $convertedPrice = convert_price($displayPrice); @endphp

<article class="group flex flex-col rounded-xl bg-white border border-slate-200/80 overflow-hidden hover:shadow-md hover:-translate-y-0.5 hover:border-violet-300 transition-all duration-200" data-id="{{ $product->id }}" data-price="{{ $convertedPrice }}">
    <a href="{{ route('product.show', $product->slug) }}" class="relative aspect-square bg-slate-50 flex items-center justify-center p-2 overflow-hidden">
        @if($hasDiscount && $discountPercent > 0)
            <span class="absolute top-1.5 left-1.5 z-10 bg-rose-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-md">-{{ $discountPercent }}%</span>
        @endif
        @if($product->featured ?? false)
            <span class="absolute top-1.5 right-1.5 z-10 bg-amber-400 text-slate-900 text-[9px] font-bold px-1.5 py-0.5 rounded-md">★</span>
        @endif
        <img src="{{ $imageUrl }}" alt="{{ $product->name }}" loading="lazy"
            onerror="this.onerror=null;this.src='{{ site_setting_url('default_product_image') ?: asset('imges/products/default.jpg') }}'"
            class="max-h-full max-w-full object-contain transition-transform duration-300 group-hover:scale-105">
    </a>
    <div class="p-2.5 flex flex-col flex-1">
        @if($product->relationLoaded('category') && $product->category)
            <span class="text-[9px] font-bold uppercase tracking-wider text-violet-600 line-clamp-1">{{ $product->category->name }}</span>
        @endif
        <h3 class="text-[13px] font-bold text-slate-900 line-clamp-2 leading-tight mt-0.5 min-h-[2.2rem] group-hover:text-violet-700 transition-colors">
            <a href="{{ route('product.show', $product->slug) }}">{{ $product->name }}</a>
        </h3>
        <div class="mt-2 pt-2 flex items-center justify-between gap-1 border-t border-slate-100">
            <div class="min-w-0">
                @if($hasDiscount)
                    <del class="text-[10px] text-slate-400 block leading-none">{{ money($compareAt) }}</del>
                @endif
                <span class="text-sm font-black text-slate-900 leading-tight">{{ money($displayPrice) }}</span>
            </div>
            @if($inStock)
                <button type="button" onclick="event.preventDefault(); addToCart(this);"
                    class="add-btn shrink-0 h-8 w-8 grid place-items-center bg-violet-600 hover:bg-violet-700 text-white rounded-lg transition-colors"
                    aria-label="{{ __('app.shared_add_to_cart') }}">
                    <i class="fa-solid fa-plus text-xs"></i>
                </button>
            @else
                <span class="text-[9px] font-bold text-rose-500 bg-rose-50 px-1.5 py-1 rounded">{{ __('app.products_card_out_of_stock') }}</span>
            @endif
        </div>
    </div>
</article>
