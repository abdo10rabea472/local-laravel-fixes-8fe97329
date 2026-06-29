@extends('layouts.front')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/All-product.css') }}">
@endpush

@section('content')
@php
    $isRtl = app()->getLocale() === 'ar';
@endphp
<section class="products-page bg-slate-50 min-h-screen">

    {{-- ════════════ HERO ════════════ --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-violet-700 via-indigo-700 to-violet-800 text-white">
        <div class="absolute -top-24 -right-24 w-80 h-80 bg-amber-400/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-fuchsia-500/20 rounded-full blur-3xl"></div>

        <div class="relative max-w-[1850px] mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <nav class="text-xs font-bold text-violet-100/80 mb-3 flex items-center gap-2">
                <a href="{{ route('home') }}" class="hover:text-white"><i class="fa-solid fa-house"></i> {{ __('app.shared_home') }}</a>
                <i class="fa-solid fa-chevron-{{ $isRtl ? 'left' : 'right' }} text-[8px]"></i>
                <span class="text-white">{{ __('app.shared_products') }}</span>
            </nav>
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 class="text-3xl sm:text-4xl font-black tracking-tight">{{ __('app.products_page_title') }}</h1>
                    <p class="text-violet-100 mt-2 max-w-2xl">{{ __('app.products_page_subtitle') }}</p>
                </div>
                <div class="flex items-center gap-2 text-xs font-bold">
                    <span class="inline-flex items-center gap-1.5 bg-white/15 backdrop-blur border border-white/20 px-3 py-1.5 rounded-full">
                        <i class="fa-solid fa-box-open"></i> {{ __('app.products_items_count', ['count' => $products->total()]) }}
                    </span>
                    @if($activeCollege)
                        <span class="inline-flex items-center gap-1.5 bg-amber-300 text-violet-900 px-3 py-1.5 rounded-full">
                            <i class="fa-solid fa-graduation-cap"></i> {{ $activeCollege->name }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════ MAIN ════════════ --}}
    <div class="max-w-[1850px] mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Toolbar --}}
        <form action="{{ route('products.index') }}" method="get" class="flex flex-wrap gap-3 items-center mb-8">
            <div class="relative flex-1 min-w-[220px]">
                <i class="fa-solid fa-magnifying-glass absolute {{ $isRtl ? 'right-4' : 'left-4' }} top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="{{ __('app.shared_search_placeholder') }}"
                    class="w-full h-11 {{ $isRtl ? 'pr-11 pl-4' : 'pl-11 pr-4' }} bg-white border border-slate-200 rounded-2xl text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 transition">
            </div>
            <select name="sort" onchange="this.form.submit()" aria-label="{{ __('app.products_sort_label') }}"
                class="h-11 px-4 bg-white border border-slate-200 rounded-2xl text-sm font-semibold outline-none focus:border-violet-400 cursor-pointer">
                <option value="newest" @selected(request('sort', 'newest') === 'newest')>{{ __('app.products_sort_newest') }}</option>
                <option value="price_asc" @selected(request('sort') === 'price_asc')>{{ __('app.products_sort_price_asc') }}</option>
                <option value="price_desc" @selected(request('sort') === 'price_desc')>{{ __('app.products_sort_price_desc') }}</option>
                <option value="name" @selected(request('sort') === 'name')>{{ __('app.products_sort_name') }}</option>
            </select>
            <button type="button" id="mobile-filter-btn"
                class="lg:hidden flex items-center gap-2 px-5 h-11 bg-white border border-slate-200 rounded-2xl text-sm font-bold hover:border-violet-300 transition">
                <i class="fa-solid fa-sliders text-violet-600"></i> {{ __('app.products_mobile_filters') }}
            </button>
            <button type="submit" class="h-11 px-5 bg-violet-600 hover:bg-violet-700 text-white font-bold rounded-2xl text-sm shadow-md shadow-violet-500/20 transition">
                {{ __('app.products_search_button') }}
            </button>
        </form>

        <div class="flex gap-5">
            {{-- ════════════ SIDEBAR (world-class filter) ════════════ --}}
            <aside id="filter-sidebar" class="hidden lg:block w-72 shrink-0">
                <div class="sticky top-28">
                    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">

                        {{-- Header --}}
                        <div class="flex items-center justify-between px-5 py-4 bg-gradient-to-r from-violet-50 to-indigo-50 border-b border-slate-100">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-violet-600 text-white shadow-md shadow-violet-500/30">
                                    <i class="fa-solid fa-sliders text-xs"></i>
                                </span>
                                <h3 class="text-sm font-black text-slate-900">{{ __('app.products_filters_title') }}</h3>
                            </div>
                            @if(request()->hasAny(['college','department','search','featured','in_stock','on_sale','min_price','max_price']))
                                <a href="{{ route('products.index') }}" class="text-[11px] font-bold text-rose-600 hover:text-rose-700 inline-flex items-center gap-1">
                                    <i class="fa-solid fa-rotate-left"></i> {{ __('app.products_filters_reset') }}
                                </a>
                            @endif
                        </div>

                        <form id="filter-form" action="{{ route('products.index') }}" method="get" class="p-5 space-y-6">
                            @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                            @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif

                            {{-- College --}}
                            <div>
                                <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-3">{{ __('app.products_filter_college') }}</label>
                                <div class="space-y-1 max-h-64 overflow-y-auto pr-1 -mr-1">
                                    <a href="{{ route('products.index', request()->except(['college','department','page'])) }}"
                                       class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold transition-all {{ !$activeCollege ? 'bg-violet-50 border border-violet-200 text-violet-700' : 'text-slate-600 hover:bg-slate-50 border border-transparent' }}">
                                        <span class="flex items-center gap-2"><i class="fa-solid fa-layer-group text-[10px] opacity-60"></i> {{ __('app.shared_all_colleges') }}</span>
                                        @if(!$activeCollege)<i class="fa-solid fa-check text-[10px]"></i>@endif
                                    </a>
                                    @foreach($colleges as $college)
                                        <a href="{{ route('products.index', array_merge(request()->except(['department','page']), ['college' => $college->slug])) }}"
                                           class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-xs font-bold transition-all {{ ($activeCollege?->id ?? null) === $college->id ? 'bg-violet-50 border border-violet-200 text-violet-700' : 'text-slate-600 hover:bg-slate-50 border border-transparent' }}">
                                            @if($college->icon_url ?? null)
                                                <img src="{{ $college->icon_url }}" alt="" class="h-5 w-5 object-contain">
                                            @else
                                                <span class="h-5 w-5 rounded-md flex items-center justify-center text-[10px]" style="background: {{ $college->primary_color ?? '#ede9fe' }}20; color: {{ $college->primary_color ?? '#7c3aed' }}">
                                                    <i class="fa-solid fa-graduation-cap"></i>
                                                </span>
                                            @endif
                                            <span class="flex-1 truncate">{{ $college->name }}</span>
                                            @if(($activeCollege?->id ?? null) === $college->id)
                                                <i class="fa-solid fa-check text-[10px]"></i>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            @if($activeCollege && $activeCollege->children->isNotEmpty())
                                <div>
                                    <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-3">{{ __('app.products_filter_department') }}</label>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($activeCollege->children as $dept)
                                            <a href="{{ route('products.index', array_merge(request()->except('page'), ['college' => $activeCollege->slug, 'department' => $dept->slug])) }}"
                                               class="px-3 py-1.5 text-[11px] font-bold rounded-xl border transition-all {{ ($activeDepartment?->id ?? null) === $dept->id ? 'bg-violet-600 text-white border-violet-600 shadow shadow-violet-500/30' : 'bg-white text-slate-600 border-slate-200 hover:border-violet-300 hover:text-violet-700' }}">
                                                {{ $dept->name }}
                                                @if($dept->products_count > 0)<span class="opacity-70">({{ $dept->products_count }})</span>@endif
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Price range --}}
                            <div class="pt-2 border-t border-slate-100">
                                <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-3">{{ __('app.products_filter_price') }}</label>
                                <div class="flex items-center gap-2">
                                    <div class="relative flex-1">
                                        <input type="number" name="min_price" value="{{ request('min_price') }}" min="0" placeholder="{{ __('app.products_filter_min') }}"
                                               class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold outline-none focus:border-violet-400 focus:bg-white">
                                    </div>
                                    <span class="text-slate-300 font-bold">—</span>
                                    <div class="relative flex-1">
                                        <input type="number" name="max_price" value="{{ request('max_price') }}" min="0" placeholder="{{ __('app.products_filter_max') }}"
                                               class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold outline-none focus:border-violet-400 focus:bg-white">
                                    </div>
                                </div>
                                <button type="submit" class="mt-3 w-full h-9 bg-slate-900 hover:bg-violet-600 text-white text-[11px] font-black uppercase tracking-wider rounded-xl transition">
                                    {{ __('app.products_filter_apply') }}
                                </button>
                            </div>

                            {{-- Options toggles --}}
                            <div class="pt-4 border-t border-slate-100">
                                <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-3">{{ __('app.products_filter_options') }}</label>
                                <div class="space-y-2">
                                    <label class="flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl border border-slate-200 hover:border-violet-300 cursor-pointer transition group">
                                        <span class="flex items-center gap-2 text-xs font-bold text-slate-700">
                                            <i class="fa-solid fa-star text-amber-500 group-hover:scale-110 transition"></i> {{ __('app.products_filter_featured') }}
                                        </span>
                                        <input type="checkbox" name="featured" value="1" @checked(request('featured')) onchange="this.form.submit()" class="rounded text-violet-600 focus:ring-violet-500">
                                    </label>
                                    <label class="flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl border border-slate-200 hover:border-violet-300 cursor-pointer transition group">
                                        <span class="flex items-center gap-2 text-xs font-bold text-slate-700">
                                            <i class="fa-solid fa-circle-check text-emerald-500 group-hover:scale-110 transition"></i> {{ __('app.products_filter_in_stock') }}
                                        </span>
                                        <input type="checkbox" name="in_stock" value="1" @checked(request('in_stock')) onchange="this.form.submit()" class="rounded text-violet-600 focus:ring-violet-500">
                                    </label>
                                    <label class="flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl border border-rose-200/70 bg-rose-50/40 hover:border-rose-300 cursor-pointer transition group">
                                        <span class="flex items-center gap-2 text-xs font-bold text-rose-700">
                                            <i class="fa-solid fa-tags text-rose-500 group-hover:scale-110 transition"></i> {{ __('app.products_filter_on_sale') }}
                                        </span>
                                        <input type="checkbox" name="on_sale" value="1" @checked(request('on_sale')) onchange="this.form.submit()" class="rounded text-rose-600 focus:ring-rose-500">
                                    </label>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </aside>

            {{-- Mobile drawer --}}
            <div id="filter-drawer" class="fixed inset-0 z-50 hidden lg:hidden">
                <div class="absolute inset-0 bg-black/40" id="filter-backdrop"></div>
                <div class="absolute {{ $isRtl ? 'left-0' : 'right-0' }} top-0 h-full w-[min(340px,92vw)] bg-white shadow-2xl p-6 overflow-y-auto">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-black text-slate-900">{{ __('app.products_filters_title') }}</h3>
                        <button type="button" id="close-filter" class="p-2 hover:bg-slate-100 rounded-xl"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    @include('products.partials.filters-mobile', compact('colleges', 'activeCollege', 'activeDepartment'))
                </div>
            </div>

            {{-- ════════════ RESULTS ════════════ --}}
            <div id="products-results" class="flex-1 min-w-0 transition-opacity">

                @if($activeCollege || $activeDepartment || request('search') || request('on_sale') || request('featured') || request('in_stock'))
                    <div class="flex flex-wrap items-center gap-2 mb-6">
                        <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">{{ __('app.products_active_filters') }}:</span>
                        @if(request('search'))
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-violet-100 text-violet-700 rounded-full text-xs font-bold">
                                {{ __('app.products_search_chip') }}: {{ request('search') }}
                                <a href="{{ route('products.index', request()->except(['search','page'])) }}" class="hover:text-violet-900"><i class="fa-solid fa-xmark"></i></a>
                            </span>
                        @endif
                        @if($activeCollege)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-violet-100 text-violet-700 rounded-full text-xs font-bold">
                                {{ $activeCollege->name }}
                                <a href="{{ route('products.index', request()->except(['college','department','page'])) }}"><i class="fa-solid fa-xmark"></i></a>
                            </span>
                        @endif
                        @if($activeDepartment)
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-slate-200 text-slate-700 rounded-full text-xs font-bold">{{ $activeDepartment->name }}</span>
                        @endif
                        @if(request('featured'))
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-bold"><i class="fa-solid fa-star"></i> {{ __('app.products_filter_featured') }}
                                <a href="{{ route('products.index', request()->except(['featured','page'])) }}"><i class="fa-solid fa-xmark"></i></a>
                            </span>
                        @endif
                        @if(request('in_stock'))
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold"><i class="fa-solid fa-circle-check"></i> {{ __('app.products_filter_in_stock') }}
                                <a href="{{ route('products.index', request()->except(['in_stock','page'])) }}"><i class="fa-solid fa-xmark"></i></a>
                            </span>
                        @endif
                        @if(request('on_sale'))
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-100 text-rose-700 rounded-full text-xs font-bold"><i class="fa-solid fa-tags"></i> {{ __('app.products_filter_on_sale') }}
                                <a href="{{ route('products.index', request()->except(['on_sale','page'])) }}"><i class="fa-solid fa-xmark"></i></a>
                            </span>
                        @endif
                    </div>
                @endif

                @if($products->isNotEmpty())
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-7 gap-3 sm:gap-4">
                        @foreach($products as $product)
                            @include('products.partials.compact-card', ['product' => $product])
                        @endforeach
                    </div>
                    <div class="mt-10 flex justify-center">{{ $products->links() }}</div>
                @else
                    <div class="text-center py-20 bg-white rounded-3xl border border-slate-200">
                        <i class="fa-solid fa-box-open text-4xl text-slate-300 mb-4"></i>
                        <h3 class="text-lg font-bold text-slate-800">{{ __('app.products_empty_title') }}</h3>
                        <p class="text-slate-500 text-sm mt-2">{{ __('app.products_empty_subtitle') }}</p>
                        <a href="{{ route('products.index') }}" class="inline-block mt-4 text-violet-600 font-bold text-sm">{{ __('app.products_empty_clear') }}</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.getElementById('mobile-filter-btn')?.addEventListener('click', () => document.getElementById('filter-drawer')?.classList.remove('hidden'));
document.getElementById('close-filter')?.addEventListener('click', () => document.getElementById('filter-drawer')?.classList.add('hidden'));
document.getElementById('filter-backdrop')?.addEventListener('click', () => document.getElementById('filter-drawer')?.classList.add('hidden'));

// ===== AJAX: search / sort / filters / pagination =====
(function () {
    if (!window.UL) return;
    const SELECTOR = '#products-results';

    function formToUrl(form) {
        const action = form.getAttribute('action') || window.location.pathname;
        const params = new URLSearchParams(new FormData(form));
        for (const [k, v] of [...params.entries()]) {
            if (v === '' || v === null) params.delete(k);
        }
        const qs = params.toString();
        return qs ? `${action}?${qs}` : action;
    }

    async function go(url) {
        try { await window.UL.swap(url, SELECTOR, { scrollTo: SELECTOR }); } catch (_) {}
    }

    document.addEventListener('submit', (e) => {
        const form = e.target.closest('form');
        if (!form) return;
        const method = (form.getAttribute('method') || 'get').toLowerCase();
        if (method !== 'get') return;
        const action = form.getAttribute('action') || '';
        if (!action.includes('/products')) return;
        e.preventDefault();
        document.getElementById('filter-drawer')?.classList.add('hidden');
        go(formToUrl(form));
    });

    document.addEventListener('change', (e) => {
        const el = e.target;
        if (!el.matches('select[name="sort"], #filter-form input, #filter-form select')) return;
        const form = el.form || el.closest('form');
        if (!form) return;
        e.preventDefault();
        go(formToUrl(form));
    });

    document.addEventListener('click', (e) => {
        const a = e.target.closest(`${SELECTOR} a[href]`);
        if (!a) return;
        const href = a.getAttribute('href');
        if (!href || href.startsWith('#') || a.target === '_blank') return;
        try {
            const u = new URL(href, window.location.origin);
            if (u.origin !== window.location.origin) return;
            if (!u.pathname.includes('/products')) return;
        } catch (_) { return; }
        e.preventDefault();
        go(href);
    });
})();
</script>
@endpush
