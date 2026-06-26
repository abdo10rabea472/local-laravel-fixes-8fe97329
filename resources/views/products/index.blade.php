@extends('layouts.front')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/All-product.css') }}">
@endpush

@section('content')
<section class="products-page bg-slate-50 min-h-screen py-6 sm:py-8">
    <div class="w-full px-3 sm:px-5 lg:px-8 2xl:px-12">

        <div class="mb-8">
            <h1 class="text-3xl sm:text-4xl font-black text-slate-900">{{ $pageTitle }}</h1>
            <p class="text-slate-500 mt-2">{{ $pageSubtitle ?: ($products->total() . ' products across all colleges') }}</p>
        </div>

        <form action="{{ route('products.index') }}" method="get" class="flex flex-wrap gap-3 items-center mb-8">
            <div class="relative flex-1 min-w-[220px]">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Search products..."
                    class="w-full h-11 pl-11 pr-4 bg-white border border-slate-200 rounded-2xl text-sm outline-none focus:border-violet-400">
            </div>
            <select name="sort" onchange="this.form.submit()"
                class="h-11 px-4 bg-white border border-slate-200 rounded-2xl text-sm font-semibold outline-none">
                <option value="newest" @selected(request('sort', 'newest') === 'newest')>Newest</option>
                <option value="price_asc" @selected(request('sort') === 'price_asc')>Price ↑</option>
                <option value="price_desc" @selected(request('sort') === 'price_desc')>Price ↓</option>
                <option value="name" @selected(request('sort') === 'name')>A → Z</option>
            </select>
            <button type="button" id="mobile-filter-btn"
                class="lg:hidden flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-200 rounded-2xl text-sm font-bold">
                <i class="fa-solid fa-sliders text-cyan-600"></i> Filters
            </button>
            <button type="submit" class="h-11 px-5 bg-violet-600 hover:bg-violet-700 text-white font-bold rounded-2xl text-sm">Search</button>
        </form>

        <div class="flex gap-5">
            {{-- Sidebar --}}
            <aside id="filter-sidebar" class="hidden lg:block w-60 shrink-0">
                <div class="sticky top-28 bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">

                    <div class="flex items-center gap-2 mb-6">
                        <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600">
                            <i class="fa-solid fa-filter text-xs"></i>
                        </span>
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Filters</h3>
                    </div>

                    <div class="mb-6">
                        <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-3">College</label>
                        <div class="space-y-1">
                            <a href="{{ route('products.index', request()->except(['college', 'department', 'page'])) }}"
                               class="block px-4 py-2.5 rounded-xl text-xs font-bold transition-all {{ !$activeCollege ? 'bg-cyan-50 border border-cyan-100 text-cyan-700' : 'text-slate-600 hover:bg-slate-50' }}">
                                All Colleges
                            </a>
                            @foreach($colleges as $college)
                            <a href="{{ route('products.index', array_merge(request()->except(['department', 'page']), ['college' => $college->slug])) }}"
                               class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all {{ ($activeCollege?->id ?? null) === $college->id ? 'bg-cyan-50 border border-cyan-100 text-cyan-700' : 'text-slate-600 hover:bg-slate-50' }}">
                                @if($college->icon_url)
                                    <img src="{{ $college->icon_url }}" alt="" class="h-5 w-5 object-contain">
                                @endif
                                {{ $college->name }}
                            </a>
                            @endforeach
                        </div>
                    </div>

                    @if($activeCollege && $activeCollege->children->isNotEmpty())
                    <div class="mb-6">
                        <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-3">Department</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($activeCollege->children as $dept)
                            <a href="{{ route('products.index', array_merge(request()->except('page'), ['college' => $activeCollege->slug, 'department' => $dept->slug])) }}"
                               class="px-3 py-1.5 text-[11px] font-semibold rounded-xl border transition-all {{ ($activeDepartment?->id ?? null) === $dept->id ? 'bg-violet-600 text-white border-violet-600' : 'bg-white text-slate-600 border-slate-200 hover:border-violet-300' }}">
                                {{ $dept->name }}
                                @if($dept->products_count > 0)<span class="opacity-70">({{ $dept->products_count }})</span>@endif
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="space-y-3 pt-4 border-t border-slate-100">
                        <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 cursor-pointer">
                            <input type="checkbox" form="filter-form" name="featured" value="1" @checked(request('featured')) onchange="document.getElementById('filter-form').submit()" class="rounded text-violet-600">
                            Featured only
                        </label>
                        <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 cursor-pointer">
                            <input type="checkbox" form="filter-form" name="in_stock" value="1" @checked(request('in_stock')) onchange="document.getElementById('filter-form').submit()" class="rounded text-violet-600">
                            In stock only
                        </label>
                    </div>
                </div>
            </aside>

            {{-- Mobile filter drawer --}}
            <div id="filter-drawer" class="fixed inset-0 z-50 hidden lg:hidden">
                <div class="absolute inset-0 bg-black/40" id="filter-backdrop"></div>
                <div class="absolute right-0 top-0 h-full w-[min(320px,90vw)] bg-white shadow-2xl p-6 overflow-y-auto">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-black text-slate-900">Filters</h3>
                        <button type="button" id="close-filter" class="p-2 hover:bg-slate-100 rounded-xl"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    @include('products.partials.filters-mobile', compact('colleges', 'activeCollege', 'activeDepartment'))
                </div>
            </div>

            {{-- Products grid --}}
            <div class="flex-1 min-w-0">
                @if($activeCollege || $activeDepartment || request('search'))
                <div class="flex flex-wrap gap-2 mb-6">
                    @if(request('search'))
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-violet-100 text-violet-700 rounded-full text-xs font-bold">
                        Search: {{ request('search') }}
                        <a href="{{ route('products.index', request()->except(['search', 'page'])) }}" class="hover:text-violet-900"><i class="fa-solid fa-xmark"></i></a>
                    </span>
                    @endif
                    @if($activeCollege)
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-cyan-100 text-cyan-700 rounded-full text-xs font-bold">
                        {{ $activeCollege->name }}
                        <a href="{{ route('products.index', request()->except(['college', 'department', 'page'])) }}"><i class="fa-solid fa-xmark"></i></a>
                    </span>
                    @endif
                    @if($activeDepartment)
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-slate-200 text-slate-700 rounded-full text-xs font-bold">
                        {{ $activeDepartment->name }}
                    </span>
                    @endif
                </div>
                @endif

                <form id="filter-form" action="{{ route('products.index') }}" method="get" class="hidden">
                    @foreach(request()->except(['featured', 'in_stock']) as $key => $val)
                        @if(is_string($val))<input type="hidden" name="{{ $key }}" value="{{ $val }}">@endif
                    @endforeach
                    @if(request('featured'))<input type="hidden" name="featured" value="1">@endif
                    @if(request('in_stock'))<input type="hidden" name="in_stock" value="1">@endif
                </form>

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
                    <h3 class="text-lg font-bold text-slate-800">No products found</h3>
                    <p class="text-slate-500 text-sm mt-2">Try adjusting your filters or search term.</p>
                    <a href="{{ route('products.index') }}" class="inline-block mt-4 text-violet-600 font-bold text-sm">Clear all filters</a>
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
</script>
@endpush
