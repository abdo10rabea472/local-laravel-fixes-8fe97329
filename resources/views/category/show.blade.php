@extends('layouts.front')

@section('content')
@php
    $primary = $themeCategory?->primary_color ?? '#7c3aed';   // emerald-500
    $secondary = $themeCategory?->secondary_color ?? '#6366f1'; // cyan-400
    $college = $isCollege ? $category : $category->parent;
    $totalProducts = $products->total();

    // Icon resolution: explicit image > fallback emoji by category name
    $iconUrl = $category->icon_url ?? $college?->icon_url;
    $iconEmoji = (function () use ($category, $college) {
        $name = strtolower(($category?->name ?? '') . ' ' . ($college?->name ?? ''));
        return match (true) {
            str_contains($name, 'med')      => '🩺',
            str_contains($name, 'eng')      => '⚙️',
            str_contains($name, 'art')      => '🎨',
            str_contains($name, 'nurs')     => '👩‍⚕️',
            str_contains($name, 'pharm')    => '💊',
            str_contains($name, 'sci')      => '🔬',
            str_contains($name, 'comput')   => '💻',
            default                          => '🎓',
        };
    })();
@endphp

{{-- =================== PAGE HEADER =================== --}}
<section class="relative overflow-hidden"
    style="background: linear-gradient(135deg, {{ $primary }}, {{ $secondary }});">
    @if($category->banner_url ?? $college?->banner_url)
        <img src="{{ $category->banner_url ?? $college?->banner_url }}" alt=""
            onerror="this.onerror=null;this.src='{{ site_setting_url('default_product_image') ?: asset('imges/products/default.jpg') }}'"
            class="absolute inset-0 w-full h-full object-cover opacity-20 mix-blend-overlay" loading="eager">

    @endif
    <div class="absolute inset-0 bg-gradient-to-b from-black/10 via-transparent to-black/20"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-10 py-12 sm:py-16">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm text-white/80 mb-6">
            <a class="hover:text-white transition-colors" href="{{ route('home') }}">Home</a>
            <i class="fa-solid fa-chevron-right text-[10px] text-white/60"></i>
            @if(!$isCollege && $college)
                <a class="hover:text-white transition-colors" href="{{ route('category.show', $college->slug) }}">{{ $college->name }}</a>
                <i class="fa-solid fa-chevron-right text-[10px] text-white/60"></i>
            @endif
            <span class="text-white font-semibold">{{ $category->name }}</span>
        </nav>

        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
            {{-- Icon box --}}
            <div class="h-16 w-16 sm:h-20 sm:w-20 rounded-2xl bg-white/15 backdrop-blur-md border border-white/25
                        flex items-center justify-center shadow-xl shrink-0">
                @if($iconUrl)
                    <img src="{{ $iconUrl }}" alt="" class="max-h-12 max-w-12 object-contain" onerror="this.onerror=null;this.src='{{ site_setting_url('default_product_image') ?: asset('imges/products/default.jpg') }}'">
                @else
                    <span class="text-3xl sm:text-4xl">{{ $iconEmoji }}</span>
                @endif
            </div>

            <div class="flex-1 min-w-0">
                @if(!$isCollege && $college)
                    <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-white/70">{{ $college->name }}</span>
                @endif
                <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight mt-1">{{ $category->name }}</h1>
                @if($category->description)
                    <p class="text-white/85 mt-2 text-sm sm:text-base leading-relaxed max-w-2xl">{{ $category->description }}</p>
                @endif

                <div class="flex flex-wrap gap-2 mt-4">
                    <span class="px-3 py-1 rounded-full bg-white/15 text-white text-xs font-bold border border-white/20 backdrop-blur-sm">
                        <i class="fa-solid fa-cube mr-1.5 text-[10px]"></i>{{ $totalProducts }} products
                    </span>
                    @if($departments->isNotEmpty())
                    <span class="px-3 py-1 rounded-full bg-white/15 text-white text-xs font-bold border border-white/20 backdrop-blur-sm">
                        <i class="fa-solid fa-layer-group mr-1.5 text-[10px]"></i>{{ $departments->count() }} departments
                    </span>
                    @endif
                </div>
            </div>

            {{-- Search --}}
            <form action="{{ route('category.show', $category->slug) }}" method="get"
                class="w-full sm:w-auto sm:min-w-[320px]">
                <div class="bg-white/15 backdrop-blur-md border border-white/25 rounded-2xl p-1.5 flex gap-1.5 shadow-lg">
                    <div class="relative flex-1">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="search" name="search" value="{{ request('search') }}"
                            placeholder="Search products..."
                            class="w-full h-11 pl-10 pr-3 bg-white rounded-xl text-sm text-slate-800 outline-none placeholder:text-slate-400">
                    </div>
                    <button type="submit"
                        class="h-11 px-5 bg-white font-bold text-sm rounded-xl shrink-0 hover:bg-slate-50 transition-colors"
                        style="color: {{ $primary }}">
                        Go
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

{{-- =================== DEPARTMENT FILTER BAR =================== --}}
@if($departments->isNotEmpty())
<section class="bg-slate-50 border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10 py-6">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 items-center gap-2
                    bg-slate-200/50 p-1.5 rounded-2xl shadow-[inset_0_2px_4px_rgba(0,0,0,0.04)]
                    border border-slate-200/30 backdrop-blur-md w-full">
            @if($college)
                @php $allActive = $isCollege; @endphp
                <a href="{{ route('category.show', $college->slug) }}"
                   class="px-4 py-3 text-[11px] sm:text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300 text-center transform active:scale-95
                          {{ $allActive
                              ? 'text-white shadow-[0_8px_20px_-6px_rgba(0,0,0,0.15)]'
                              : 'text-slate-500 hover:text-slate-900 hover:bg-white/70' }}"
                   @if($allActive) style="background: linear-gradient(135deg, {{ $primary }}, {{ $secondary }});" @endif>
                    All {{ $college->name }}
                </a>
            @endif

            @foreach($departments as $dept)
                @php $isActive = !$isCollege && $category->slug === $dept->slug; @endphp
                <a href="{{ route('category.show', $dept->slug) }}"
                   class="px-4 py-3 text-[11px] sm:text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300 text-center transform active:scale-95
                          {{ $isActive
                              ? 'text-white shadow-[0_8px_20px_-6px_rgba(0,0,0,0.15)]'
                              : 'text-slate-500 hover:text-slate-900 hover:bg-white/70' }}"
                   @if($isActive) style="background: linear-gradient(135deg, {{ $primary }}, {{ $secondary }});" @endif>
                    {{ $dept->name }}
                    @if($dept->products_count > 0)
                        <span class="opacity-70">({{ $dept->products_count }})</span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- =================== DYNAMIC SECTIONS =================== --}}
@if($category->sections && $category->sections->isNotEmpty())
<section class="py-10 sm:py-14 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10 space-y-8">
        @foreach($category->sections as $section)
            @include('category.sections.' . (in_array($section->section_type, ['banner', 'text_block', 'html_block']) ? $section->section_type : 'generic'), [
                'section' => $section,
                'college' => $college,
                'primary' => $primary,
                'secondary' => $secondary,
            ])
        @endforeach
    </div>
</section>
@endif

{{-- =================== PRODUCTS =================== --}}
<section class="py-10 sm:py-14 bg-slate-50" id="products">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
            <div>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                    {{ $isCollege ? 'All ' . $category->name . ' Products' : $category->name . ' Products' }}
                </h2>
                <p class="text-slate-500 text-sm mt-1">
                    @if(request('search'))
                        {{ $totalProducts }} results for "{{ request('search') }}"
                    @else
                        {{ $totalProducts }} product{{ $totalProducts !== 1 ? 's' : '' }} available
                    @endif
                </p>
            </div>
            <form action="{{ route('category.show', $category->slug) }}#products" method="get" class="flex items-center gap-2">
                @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                <select name="sort" onchange="this.form.submit()"
                    class="h-11 px-4 bg-white border border-slate-200 rounded-xl text-sm font-semibold outline-none shadow-sm hover:border-slate-300 transition-colors">
                    <option value="newest" @selected(request('sort', 'newest') === 'newest')>Newest</option>
                    <option value="price_asc" @selected(request('sort') === 'price_asc')>Price ↑</option>
                    <option value="price_desc" @selected(request('sort') === 'price_desc')>Price ↓</option>
                    <option value="name" @selected(request('sort') === 'name')>A → Z</option>
                </select>
            </form>
        </div>

        <div id="products-results" class="transition-opacity">
        @if($products->isNotEmpty())
        <div class="cards grid gap-6 sm:gap-8 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($products as $product)
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
                    $lowStock = $inStock && $product->stock <= 12;
                    $catName = $product->relationLoaded('category') ? $product->category?->name : null;
                @endphp

                @if(! $inStock)
                    {{-- ============ OUT OF STOCK CARD ============ --}}
                    <article
                        class="group relative flex flex-col rounded-3xl bg-white border border-slate-200/40 overflow-hidden opacity-80 transition-all duration-300"
                        data-id="{{ $product->id }}">
                        <div class="absolute inset-0 bg-slate-100/20 backdrop-blur-[1px] z-20 pointer-events-none"></div>
                        <a href="{{ route('product.show', $product->slug) }}"
                            class="block flex-1 flex flex-col cursor-not-allowed focus:outline-none">
                            <div class="relative aspect-square w-full bg-slate-50/70 overflow-hidden flex items-center justify-center p-8 border-b border-slate-100">
                                @if($catName)
                                    <span class="absolute top-4 left-4 z-10 px-3 py-1.5 text-[10px] font-black rounded-lg bg-white/80 text-slate-400 border border-slate-200/40 uppercase tracking-widest">
                                        {{ $catName }}
                                    </span>
                                @endif
                                <span class="absolute inset-0 m-auto h-fit w-fit z-30 px-4 py-2 text-xs font-black rounded-xl bg-slate-900/90 text-white shadow-xl uppercase tracking-widest backdrop-blur-sm border border-white/10 transform -rotate-3">
                                    Sold Out
                                </span>
                                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" loading="lazy"
                                    onerror="this.onerror=null;this.src='{{ site_setting_url('default_product_image') ?: asset('imges/products/default.jpg') }}'"
                                    class="max-h-full max-w-full object-contain filter grayscale opacity-40">

                            </div>
                            <div class="p-6 flex-1 flex flex-col justify-between bg-slate-50/30">
                                <div>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200 mb-2">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span> Out of Stock
                                    </span>
                                    <h2 class="text-base font-extrabold text-slate-400 tracking-tight line-clamp-1">{{ $product->name }}</h2>
                                    @if($product->short_description)
                                        <p class="text-xs text-slate-400/80 mt-2 font-medium leading-relaxed line-clamp-2">{{ $product->short_description }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between mt-6 pt-5 border-t border-slate-200/60">
                                    <div class="flex flex-col">
                                        <span class="text-[10px] text-slate-300 font-bold uppercase tracking-wider">Investment</span>
                                        <span class="text-xl font-black text-slate-400 font-mono tracking-tight">
                                            {{ number_format($displayPrice, 2) }}
                                            <span class="text-[11px] font-extrabold text-slate-300 ml-0.5">EGP</span>
                                        </span>
                                    </div>
                                    <button disabled
                                        class="h-12 w-12 rounded-2xl bg-slate-100 text-slate-300 flex items-center justify-center border border-slate-200 cursor-not-allowed">
                                        <i class="fa-solid fa-ban text-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </a>
                    </article>
                @else
                    {{-- ============ IN-STOCK PRODUCT CARD ============ --}}
                    <article
                        class="group relative flex flex-col rounded-3xl bg-white border border-slate-200/70 overflow-hidden transition-all duration-500 ease-[cubic-bezier(0.175,0.885,0.32,1.275)] hover:-translate-y-2 hover:shadow-[0_30px_60px_-15px_rgba(15,23,42,0.12),0_20px_40px_-20px_rgba(16,185,129,0.15)]"
                        data-id="{{ $product->id }}">
                        <a href="{{ route('product.show', $product->slug) }}" class="block flex-1 flex flex-col focus:outline-none">
                            <div class="relative aspect-square w-full bg-slate-50 overflow-hidden flex items-center justify-center p-8 border-b border-slate-100 shadow-[inset_0_-10px_20px_rgba(0,0,0,0.01)]">
                                @if($catName)
                                    <span class="absolute top-4 left-4 z-10 px-3 py-1.5 text-[10px] font-black rounded-lg bg-white text-slate-700 shadow-[0_4px_10px_rgba(0,0,0,0.05)] border border-slate-200/60 uppercase tracking-widest"
                                          style="color: {{ $primary }}">
                                        {{ $catName }}
                                    </span>
                                @endif

                                @if($hasDiscount && $discountPercent > 0)
                                    <div class="absolute top-4 right-4 z-10 bg-gradient-to-br from-rose-500 to-red-600 text-white font-black text-[11px] px-3 py-1.5 rounded-xl shadow-[0_8px_16px_rgba(244,63,94,0.3),inset_0_2px_4px_rgba(255,255,255,0.2)] border-t border-white/20 transform rotate-6 group-hover:scale-110 group-hover:rotate-0 transition-all duration-300">
                                        SAVE {{ $discountPercent }}%
                                    </div>
                                @elseif($product->featured)
                                    <div class="absolute top-4 right-4 z-10 bg-gradient-to-br from-amber-500 to-orange-600 text-white font-black text-[10px] px-2.5 py-1.5 rounded-xl shadow-[0_8px_16px_rgba(245,158,11,0.3),inset_0_2px_4px_rgba(255,255,255,0.2)] border-t border-white/20 transform rotate-2 group-hover:scale-110 group-hover:rotate-0 transition-all duration-300">
                                        BEST SELLER
                                    </div>
                                @endif

                                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" loading="lazy"
                                    onerror="this.onerror=null;this.src='{{ site_setting_url('default_product_image') ?: asset('imges/products/default.jpg') }}'"
                                    class="max-h-full max-w-full object-contain transition-transform duration-500 group-hover:scale-110 group-hover:rotate-1">

                            </div>

                            <div class="p-6 flex-1 flex flex-col justify-between bg-gradient-to-b from-white to-slate-50/50">
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-2">
                                        @if($lowStock)
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-100/70">
                                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                Only {{ $product->stock }} left
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100/70">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                In Stock ({{ $product->stock }} items)
                                            </span>
                                        @endif
                                    </div>

                                    <h2 class="text-base font-extrabold text-slate-900 tracking-tight transition-colors duration-300 line-clamp-1 group-hover:opacity-90"
                                        style="--tw-text-opacity:1;">
                                        {{ $product->name }}
                                    </h2>

                                    @if($product->short_description)
                                        <p class="text-xs text-slate-500 mt-2 font-medium leading-relaxed line-clamp-2">{{ $product->short_description }}</p>
                                    @endif
                                </div>

                                <div class="flex items-center justify-between mt-6 pt-5 border-t border-slate-100">
                                    <div class="flex flex-col">
                                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Investment</span>
                                        @if($hasDiscount)
                                            <del class="text-[11px] text-slate-400 font-bold">{{ number_format($compareAt, 2) }} EGP</del>
                                        @endif
                                        <span class="text-xl font-black text-slate-900 font-mono tracking-tight">
                                            {{ number_format($displayPrice, 2) }}
                                            <span class="text-[11px] font-extrabold ml-0.5" style="color: {{ $primary }}">EGP</span>
                                        </span>
                                    </div>
                                    <button type="button"
                                        onclick="event.preventDefault(); event.stopPropagation(); addToCart(this);"
                                        class="add-btn h-12 w-12 rounded-2xl bg-gradient-to-b from-white to-slate-100 text-slate-600 flex items-center justify-center border border-slate-200 shadow-[0_4px_6px_-1px_rgba(0,0,0,0.05),inset_0_-2px_4px_rgba(0,0,0,0.05)] transition-all duration-300 group-hover:text-white group-hover:scale-105 active:scale-95"
                                        style="--hover-bg: linear-gradient(180deg, {{ $primary }}, {{ $secondary }});"
                                        onmouseover="this.style.background=this.style.getPropertyValue('--hover-bg');this.style.borderColor='{{ $primary }}';"
                                        onmouseout="this.style.background='';this.style.borderColor='';">
                                        <i class="fa-solid fa-cart-shopping text-sm transform group-hover:scale-110 transition-transform"></i>
                                    </button>
                                </div>
                            </div>
                        </a>
                    </article>
                @endif
            @endforeach
        </div>

        <div class="mt-10 flex justify-center">{{ $products->onEachSide(1)->links() }}</div>
        @else
        <div class="text-center py-20 bg-white rounded-3xl border border-slate-200 shadow-sm">
            <div class="h-16 w-16 mx-auto rounded-2xl flex items-center justify-center mb-4"
                style="background: linear-gradient(135deg, {{ $primary }}22, {{ $secondary }}22);">
                <i class="fa-solid fa-box-open text-3xl" style="color: {{ $primary }}"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800">No products found</h3>
            <p class="text-slate-500 text-sm mt-2">
                @if(request('search'))
                    Try another search term.
                @else
                    Products will appear here once added to this {{ $isCollege ? 'college' : 'department' }}.
                @endif
            </p>
            @if(request('search'))
                <a href="{{ route('category.show', $category->slug) }}"
                   class="inline-block mt-4 font-bold text-sm" style="color: {{ $primary }}">Clear search</a>
            @endif
        </div>
        @endif
        </div>{{-- /#products-results --}}
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    if (!window.UL) return;
    const SELECTOR = '#products-results';
    const BASE = @json(route('category.show', $category->slug));

    function formToUrl(form) {
        const action = (form.getAttribute('action') || BASE).split('#')[0];
        const params = new URLSearchParams(new FormData(form));
        for (const [k, v] of [...params.entries()]) if (v === '' || v === null) params.delete(k);
        const qs = params.toString();
        return qs ? `${action}?${qs}` : action;
    }
    async function go(url) { try { await window.UL.swap(url, SELECTOR, { scrollTo: '#products' }); } catch (_) {} }

    document.addEventListener('submit', (e) => {
        const form = e.target.closest('form');
        if (!form) return;
        if ((form.getAttribute('method') || 'get').toLowerCase() !== 'get') return;
        const action = form.getAttribute('action') || '';
        if (!action.startsWith(BASE)) return;
        e.preventDefault();
        go(formToUrl(form));
    });
    document.addEventListener('change', (e) => {
        if (!e.target.matches('select[name="sort"]')) return;
        const form = e.target.form;
        if (!form || !(form.getAttribute('action') || '').startsWith(BASE)) return;
        e.preventDefault();
        go(formToUrl(form));
    });
    document.addEventListener('click', (e) => {
        const a = e.target.closest(`${SELECTOR} a[href]`);
        if (!a || a.target === '_blank') return;
        const href = a.getAttribute('href');
        if (!href || href.startsWith('#')) return;
        try {
            const u = new URL(href, window.location.origin);
            if (u.origin !== window.location.origin) return;
            if (!u.pathname.startsWith(new URL(BASE).pathname)) return;
        } catch (_) { return; }
        e.preventDefault();
        go(href);
    });
})();
</script>
@endpush
