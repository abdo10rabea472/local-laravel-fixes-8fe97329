@extends('layouts.front')

@section('content')

{{-- ═══════════════ HERO ═══════════════ --}}
<section class="relative overflow-hidden bg-gradient-to-br from-indigo-700 via-violet-700 to-fuchsia-700 text-white">
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-amber-400/20 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-32 -right-32 w-[500px] h-[500px] bg-cyan-400/20 rounded-full blur-3xl"></div>

    <div class="relative max-w-[1850px] mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20 grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
        <div>
            <span class="inline-flex items-center gap-2 bg-white/15 backdrop-blur border border-white/20 text-xs font-bold px-3 py-1.5 rounded-full mb-5">
                <i class="fa-solid fa-flask-vial text-amber-300"></i>
                {{ __('app.home_hero_badge') }}
            </span>
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black leading-[1.1] mb-5">
                {{ __('app.home_hero_title') }}
            </h1>
            <p class="text-violet-100 text-base sm:text-lg max-w-xl mb-7 leading-relaxed">
                {{ __('app.home_hero_subtitle') }}
            </p>

            <div class="flex flex-wrap gap-3 mb-8">
                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 bg-white text-violet-800 font-black px-7 py-3.5 rounded-xl hover:bg-amber-300 hover:text-violet-900 transition shadow-xl">
                    <i class="fa-solid fa-bag-shopping"></i> {{ __('app.home_hero_shop_all') }}
                </a>
                <a href="#colleges" class="inline-flex items-center gap-2 bg-white/10 backdrop-blur border border-white/25 text-white font-black px-7 py-3.5 rounded-xl hover:bg-white/20 transition">
                    <i class="fa-solid fa-graduation-cap"></i> {{ __('app.home_hero_browse_colleges') }}
                </a>
            </div>

            <div class="grid grid-cols-3 gap-4 max-w-md">
                <div class="text-center">
                    <div class="text-2xl sm:text-3xl font-black text-amber-300">{{ $hero['stat_products'] ?? '0' }}</div>
                    <div class="text-xs text-violet-200 font-semibold">{{ __('app.home_hero_stat_products') }}</div>
                </div>
                <div class="text-center border-x border-white/15">
                    <div class="text-2xl sm:text-3xl font-black text-amber-300">{{ $hero['stat_colleges'] ?? '0' }}</div>
                    <div class="text-xs text-violet-200 font-semibold">{{ __('app.home_hero_stat_colleges') }}</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl sm:text-3xl font-black text-amber-300">{{ $hero['stat_departments'] ?? '0' }}</div>
                    <div class="text-xs text-violet-200 font-semibold">{{ __('app.home_hero_stat_departments') }}</div>
                </div>
            </div>
        </div>

        <div class="relative hidden lg:flex items-center justify-center">
            <div class="absolute inset-0 bg-white/10 backdrop-blur rounded-[3rem] blur-2xl"></div>
            <div class="relative grid grid-cols-2 gap-4 w-full max-w-lg">
                @php
                    $heroIcons = [
                        ['fa-microscope', 'from-cyan-400 to-blue-500', __('app.home_hero_card_microscopes')],
                        ['fa-vial', 'from-emerald-400 to-teal-500', __('app.home_hero_card_glassware')],
                        ['fa-stethoscope', 'from-rose-400 to-pink-500', __('app.home_hero_card_medical')],
                        ['fa-screwdriver-wrench', 'from-amber-400 to-orange-500', __('app.home_hero_card_engineering')],
                    ];
                @endphp
                @foreach($heroIcons as [$ic, $grad, $lbl])
                <div class="rounded-3xl bg-white/10 backdrop-blur border border-white/20 p-6 hover:bg-white/15 transition">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br {{ $grad }} flex items-center justify-center text-2xl text-white mb-3 shadow-lg">
                        <i class="fa-solid {{ $ic }}"></i>
                    </div>
                    <div class="font-black">{{ $lbl }}</div>
                    <div class="text-xs text-violet-100 mt-0.5">{{ __('app.home_hero_card_subtitle') }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════ FEATURES STRIP ═══════════════ --}}
<section class="bg-white border-b border-slate-100">
    <div class="max-w-[1850px] mx-auto px-4 sm:px-6 lg:px-8 py-6 grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $features = [
                ['fa-truck-fast', __('app.home_features_fast_title'), __('app.home_features_fast_sub')],
                ['fa-shield-halved', __('app.home_features_original_title'), __('app.home_features_original_sub')],
                ['fa-rotate-left', __('app.home_features_returns_title'), __('app.home_features_returns_sub')],
                ['fa-headset', __('app.home_features_support_title'), __('app.home_features_support_sub')],
            ];
        @endphp
        @foreach($features as [$ic, $title, $sub])
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 shrink-0 rounded-xl bg-violet-50 text-violet-700 flex items-center justify-center text-xl">
                <i class="fa-solid {{ $ic }}"></i>
            </div>
            <div class="min-w-0">
                <div class="font-black text-slate-900 text-sm truncate">{{ $title }}</div>
                <div class="text-xs text-slate-500 truncate">{{ $sub }}</div>
            </div>
        </div>
        @endforeach
    </div>
</section>

{{-- ═══════════════ COLLEGES (CATEGORY CIRCLES) ═══════════════ --}}
@if($mainCategories->isNotEmpty())
<section id="colleges" class="bg-slate-50 py-10 overflow-hidden">
    <div class="max-w-[1850px] mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-end justify-between mb-6">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-violet-600">
                    {{ __('app.home_colleges_eyebrow') }}
                </span>

                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">
                    {{ __('app.home_colleges_title') }}
                </h2>
            </div>

            <div class="hidden md:flex gap-3">
                <button class="college-icons-prev w-11 h-11 rounded-full bg-white shadow hover:bg-violet-600 hover:text-white transition">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>

                <button class="college-icons-next w-11 h-11 rounded-full bg-white shadow hover:bg-violet-600 hover:text-white transition">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <div class="swiper collegeIconsSwiper">

            <div class="swiper-wrapper">

                @foreach($mainCategories as $cat)

                    <div class="swiper-slide !w-[150px]">

                        <a href="{{ route('category.show',$cat->slug) }}"
                           class="group flex flex-col items-center gap-2.5">

                            <div class="relative w-full aspect-square rounded-2xl bg-white border border-slate-200 group-hover:border-violet-400 transition-all duration-300 p-2 flex items-center justify-center overflow-hidden shadow-sm group-hover:shadow-lg group-hover:-translate-y-1">

                                @if($cat->image)

                                    <img src="{{ asset('storage/'.$cat->image) }}"
                                         alt="{{ $cat->name }}"
                                         loading="lazy"
                                         class="w-full h-full object-contain"
                                         onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex';">

                                    <div class="hidden absolute inset-0 items-center justify-center text-2xl text-white"
                                         style="background:linear-gradient(135deg,
                                         {{ $cat->primary_color ?? '#6366f1' }},
                                         {{ $cat->secondary_color ?? '#8b5cf6' }});">

                                        <i class="fa-solid fa-graduation-cap"></i>

                                    </div>

                                @else

                                    <div class="w-full h-full rounded-xl flex items-center justify-center text-3xl text-white"
                                         style="background:linear-gradient(135deg,
                                         {{ $cat->primary_color ?? '#6366f1' }},
                                         {{ $cat->secondary_color ?? '#8b5cf6' }});">

                                        <i class="fa-solid fa-graduation-cap"></i>

                                    </div>

                                @endif

                            </div>

                            <span class="text-xs font-bold text-slate-700 group-hover:text-violet-700 text-center leading-tight line-clamp-2">
                                {{ $cat->name }}
                            </span>

                        </a>

                    </div>

                @endforeach

            </div>

        </div>

    </div>
</section>
@endif

{{-- ═══════════════ FEATURED / TOP PICKS ═══════════════ --}}
@if($featuredProducts->isNotEmpty())
<section id="featured" class="bg-white py-12 overflow-hidden">
    <div class="max-w-[1850px] mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-center justify-between mb-6">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-amber-600">
                    {{ __('app.home_featured_eyebrow') }}
                </span>

                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">
                    {{ __('app.home_featured_title') }}
                </h2>

                @if(!empty($homeSections['featured_subtitle']))
                    <p class="text-sm text-slate-500 mt-1">
                        {{ $homeSections['featured_subtitle'] }}
                    </p>
                @endif
            </div>

            <div class="hidden md:flex gap-3">
                <button class="featured-prev w-11 h-11 rounded-full bg-white shadow hover:bg-violet-600 hover:text-white transition">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>

                <button class="featured-next w-11 h-11 rounded-full bg-white shadow hover:bg-violet-600 hover:text-white transition">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <div class="swiper featuredSwiper">

            <div class="swiper-wrapper">

                @foreach($featuredProducts as $product)

                    <div class="swiper-slide !w-[260px]">

                        @include('components.product-card', ['product' => $product])

                    </div>

                @endforeach

            </div>

            <div class="swiper-pagination mt-8"></div>

        </div>

    </div>
</section>
@endif



{{-- ═══════════════ BEST DEALS — TABS LINK TO FILTERED CATALOG ═══════════════ --}}
@if($products->isNotEmpty())
<section class="py-12 bg-white">
    <div class="max-w-[1850px] mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-6">
            <span class="text-xs font-bold uppercase tracking-wider text-rose-600">{{ __('app.home_deals_eyebrow') }}</span>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">
                {{ __('app.home_deals_title') }}
            </h2>

            @if($mainCategories->isNotEmpty())
                <div class="flex flex-wrap justify-center gap-2 mt-5">

                    <button type="button"
                            class="filter-btn px-4 py-2 rounded-full text-xs font-bold bg-violet-600 text-white"
                            data-college="">
                        {{ __('app.shared_all_products') }}
                    </button>

                    @foreach($mainCategories->take(6) as $c)
                        <button type="button"
                                class="filter-btn px-4 py-2 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200 hover:border-violet-500 hover:text-violet-700 transition"
                                data-college="{{ $c->slug }}">
                            {{ $c->name }}
                        </button>
                    @endforeach

                </div>
            @endif
        </div>

        <div id="products-grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">

            @foreach($products as $product)
                @php
                    $productCategory = $product->category;
                    $productCollegeSlug = $productCategory?->parent?->slug ?? $productCategory?->slug;
                    $productCategorySlugs = collect([$productCollegeSlug, $productCategory?->slug])->filter()->unique()->implode(' ');
                @endphp

                <div class="product-item"
                     data-colleges="{{ $productCategorySlugs }}">

                    @include('components.product-card',['product'=>$product])

                </div>

            @endforeach

        </div>

        <div class="flex justify-center mt-8">
            <a href="{{ route('products.index') }}"
               class="inline-flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white font-bold px-8 py-3 rounded-full shadow-lg shadow-violet-500/30 transition">
                {{ __('app.home_deals_browse_all') }}
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

    </div>
</section>

<script>
(function(){
    const buttons = document.querySelectorAll('.filter-btn');
    const items = document.querySelectorAll('#products-grid .product-item');
    const activeCls = ['bg-violet-600','text-white'];
    const idleCls   = ['bg-slate-100','text-slate-700','border','border-slate-200','hover:border-violet-500','hover:text-violet-700','transition'];

    function setActive(btn){
        buttons.forEach(b => {
            b.classList.remove(...activeCls);
            b.classList.add(...idleCls);
        });
        btn.classList.remove(...idleCls);
        btn.classList.add(...activeCls);
    }

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            const slug = btn.dataset.college || '';
            setActive(btn);
            items.forEach(item => {
                const itemSlugs = (item.dataset.colleges || item.dataset.college || '').split(/\s+/).filter(Boolean);
                item.style.display = (!slug || itemSlugs.includes(slug)) ? '' : 'none';
            });
        });
    });
})();
</script>


@endif

{{-- ═══════════════ COLLEGE TILES (BIG) ═══════════════ --}}
@if($mainCategories->isNotEmpty())
<section class="py-12 bg-slate-50 overflow-hidden">
    <div class="max-w-[1850px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-violet-600">{{ __('app.home_college_tiles_eyebrow') }}</span>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">{{ __('app.home_college_tiles_title') }}</h2>
            </div>
            <div class="hidden md:flex gap-3">
                <button class="college-prev w-11 h-11 rounded-full bg-white shadow hover:bg-violet-600 hover:text-white transition"><i class="fa-solid fa-chevron-left"></i></button>
                <button class="college-next w-11 h-11 rounded-full bg-white shadow hover:bg-violet-600 hover:text-white transition"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
        </div>

        <div class="swiper collegeSwiper">
            <div class="swiper-wrapper">
                @foreach($mainCategories as $cat)
                    <div class="swiper-slide !w-[320px]">
                        <a href="{{ route('category.show',$cat->slug) }}" class="group relative block aspect-[4/3] rounded-3xl overflow-hidden shadow-md hover:shadow-2xl transition-all hover:-translate-y-1" style="background:linear-gradient(135deg,{{ $cat->primary_color ?? '#6366f1' }},{{ $cat->secondary_color ?? '#8b5cf6' }});">
                            @if($cat->image)
                                <img src="{{ asset('storage/'.$cat->image) }}" alt="{{ $cat->name }}" class="absolute inset-0 w-full h-full object-cover opacity-40 group-hover:opacity-60 group-hover:scale-110 transition-all duration-500" loading="lazy">
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                            <div class="absolute inset-0 p-5 flex flex-col justify-between text-white">
                                <div class="inline-flex w-11 h-11 rounded-xl bg-white/20 backdrop-blur items-center justify-center text-lg"><i class="fa-solid fa-graduation-cap"></i></div>
                                <div>
                                    <h3 class="text-lg sm:text-xl font-black leading-tight">{{ $cat->name }}</h3>
                                    @if($cat->children_count)
                                        <p class="text-xs text-white/80 mt-1">{{ $cat->children_count }} {{ __('app.shared_departments') }}</p>
                                    @endif
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold mt-3 bg-white/20 backdrop-blur px-3 py-1.5 rounded-full">{{ __('app.home_college_tiles_shop_now') }} <i class="fa-solid fa-arrow-right text-[10px]"></i></span>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
            <div class="swiper-pagination mt-8"></div>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════ NEW ARRIVALS ═══════════════ --}}
@if($products->isNotEmpty())
<section class="py-12 bg-white">
    <div class="max-w-[1850px] mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-end justify-between mb-6">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-600">{{ __('app.home_new_eyebrow') }}</span>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">{{ __('app.home_new_title') }}</h2>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('products.index',['sort'=>'newest']) }}" class="text-violet-700 font-bold text-sm hover:underline hidden sm:inline-flex items-center gap-1">
                    {{ __('app.shared_view_all') }} <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>

                <button class="new-prev hidden md:flex w-10 h-10 items-center justify-center rounded-full border bg-white hover:bg-violet-600 hover:text-white transition">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>

                <button class="new-next hidden md:flex w-10 h-10 items-center justify-center rounded-full border bg-white hover:bg-violet-600 hover:text-white transition">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <div class="swiper newProductsSwiper">
            <div class="swiper-wrapper">

                @foreach($products->take(10) as $product)
                    <div class="swiper-slide">
                        @include('components.product-card',['product'=>$product])
                    </div>
                @endforeach

            </div>
        </div>

    </div>
</section>


@endif

{{-- ═══════════════ WHY US ═══════════════ --}}
<section class="py-14 bg-gradient-to-br from-slate-900 to-violet-950 text-white">
    <div class="max-w-[1850px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <span class="text-xs font-bold uppercase tracking-wider text-amber-300">{{ __('app.home_why_eyebrow') }}</span>
            <h2 class="text-2xl sm:text-4xl font-black mt-1">{{ __('app.home_why_title') }}</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @php
                $why = [
                    ['fa-flask-vial', __('app.home_why_1_title'), __('app.home_why_1_desc')],
                    ['fa-tags', __('app.home_why_2_title'), __('app.home_why_2_desc')],
                    ['fa-truck', __('app.home_why_3_title'), __('app.home_why_3_desc')],
                    ['fa-shield-halved', __('app.home_why_4_title'), __('app.home_why_4_desc')],
                    ['fa-people-group', __('app.home_why_5_title'), __('app.home_why_5_desc')],
                    ['fa-headset', __('app.home_why_6_title'), __('app.home_why_6_desc')],
                ];
            @endphp
            @foreach($why as [$ic, $t, $d])
            <div class="bg-white/5 backdrop-blur border border-white/10 rounded-2xl p-6 hover:bg-white/10 transition">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 text-white flex items-center justify-center text-xl mb-4 shadow-lg">
                    <i class="fa-solid {{ $ic }}"></i>
                </div>
                <h3 class="font-black text-lg mb-2">{{ $t }}</h3>
                <p class="text-sm text-violet-100/80 leading-relaxed">{{ $d }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════ NEWSLETTER ═══════════════ --}}
<section class="py-14 bg-white">
    <div class="max-w-3xl mx-auto px-4 text-center">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-violet-100 text-violet-700 text-2xl mb-4">
            <i class="fa-regular fa-envelope"></i>
        </div>
        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mb-3">{{ __('app.home_newsletter_title') }}</h2>
        <p class="text-slate-500 mb-6">{{ __('app.home_newsletter_subtitle') }}</p>
        <form action="{{ route('newsletter.subscribe') }}" method="post" class="flex flex-col sm:flex-row gap-3 max-w-lg mx-auto">
            @csrf
            <input type="email" name="email" required placeholder="{{ __('app.home_newsletter_placeholder') }}" class="flex-1 h-12 px-5 bg-slate-100 text-slate-900 rounded-xl text-sm outline-none focus:ring-2 focus:ring-violet-500 border border-transparent focus:bg-white focus:border-violet-300">
            <button type="submit" class="h-12 px-8 bg-violet-600 hover:bg-violet-700 text-white font-black rounded-xl transition shadow-lg shadow-violet-500/30">
                {{ __('app.shared_subscribe') }}
            </button>
        </form>
    </div>
</section>

@endsection
