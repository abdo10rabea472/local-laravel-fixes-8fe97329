<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $seo = $seo ?? [];
        $seoTitle = $seo['seo_title'] ?? 'UNI-LAB MARKET';
        $seoDescription = $seo['seo_description'] ?? '';
        $seoKeywords = $seo['seo_keywords'] ?? '';
        $canonicalUrl = $seo['canonical_url'] ?? url()->current();
        $ogTitle = $seo['og_title'] ?? $seoTitle;
        $ogDescription = $seo['og_description'] ?? $seoDescription;
        $ogImage = $seo['og_image'] ?? site_setting_url('default_og_image', asset('imges/photo_٢٠٢٦-٠٢-٢٥_٠٨-٤٧-٣٧-removebg-preview.png'));
        $schemaMarkup = $seo['schema_markup'] ?? null;
    @endphp

    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    @if($seoKeywords)
        <meta name="keywords" content="{{ $seoKeywords }}">
    @endif
    <link rel="canonical" href="{{ $canonicalUrl }}">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    @if($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ogTitle }}">
    <meta name="twitter:description" content="{{ $ogDescription }}">
    @if($ogImage)
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif

    @if($schemaMarkup)
        <script type="application/ld+json">{!! $schemaMarkup !!}</script>
    @endif

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ss.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

    @stack('styles')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @include('components.front-header')
    @include('components.welcome-popup')
    @include('components.free-shipping-popup')


    <main>
        @yield('content')
    </main>

    @include('components.front-footer')

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script>
        @php
            $c = current_currency();
            $defaultCurrency = app(\App\Services\CurrencyService::class)->default();
            $appCurrency = $c ? [
                'code' => $c->code,
                'symbol' => $c->symbol,
                'position' => $c->symbol_position,
                'decimals' => (int) $c->decimals,
                'rate' => (float) $c->exchange_rate,
                'defaultCode' => $defaultCurrency?->code ?? $c->code,
            ] : ['code' => 'EGP', 'symbol' => 'EGP', 'position' => 'after', 'decimals' => 2, 'rate' => 1, 'defaultCode' => 'EGP'];
        @endphp
        window.APP_CURRENCY = @json($appCurrency);
        window.convertMoney = function(amount){
            const c = window.APP_CURRENCY || { rate: 1 };
            return Number(amount || 0) * Number(c.rate || 1);
        };
        window.formatMoney = function(amount){
            const c = window.APP_CURRENCY;
            const n = window.convertMoney(amount).toLocaleString(undefined, { minimumFractionDigits: c.decimals, maximumFractionDigits: c.decimals });
            return c.position === 'before' ? `${c.symbol}${n}` : `${n} ${c.symbol}`;
        };
    </script>


    <script>

        (function(){
            const el = document.querySelector(".collegeIconsSwiper");
            if(!el) return;
            const count = el.querySelectorAll(".swiper-slide").length;
            new Swiper(el,{
                slidesPerView:"auto",
                spaceBetween:16,
                loop: false, rewind: true, watchOverflow: true,
                speed:700,
                grabCursor:true,
                autoplay:{ delay:2500, disableOnInteraction:false, pauseOnMouseEnter:true },
                navigation:{ nextEl:".college-icons-next", prevEl:".college-icons-prev" },
                breakpoints:{
                    320:{spaceBetween:12},
                    768:{spaceBetween:16},
                    1024:{spaceBetween:20},
                }
            });
        })();
    </script>






        <script>
            (function(){
                const el = document.querySelector(".featuredSwiper");
                if(!el) return;
                const count = el.querySelectorAll(".swiper-slide").length;
                new Swiper(el, {
                    slidesPerView: "auto",
                    spaceBetween: 20,
                    loop: false, rewind: true, watchOverflow: true,
                    grabCursor: true,
                    speed: 700,
                    autoplay: { delay: 2500, disableOnInteraction: false, pauseOnMouseEnter: true },
                    navigation: { nextEl: ".featured-next", prevEl: ".featured-prev" },
                    pagination: { el: ".featuredSwiper .swiper-pagination", clickable: true },
                    breakpoints: {
                        320: { spaceBetween: 12 },
                        768: { spaceBetween: 20 },
                        1024: { spaceBetween: 24 }
                    }
                });
            })();
        </script>



    <script>
        document.querySelectorAll('.filter-btn').forEach(btn => {

            btn.addEventListener('click', function () {

                document.querySelectorAll('.filter-btn').forEach(b => {
                    b.classList.remove('bg-violet-600','text-white');
                    b.classList.add('bg-slate-100','text-slate-700','border','border-slate-200');
                });

                this.classList.remove('bg-slate-100','text-slate-700','border','border-slate-200');
                this.classList.add('bg-violet-600','text-white');

                const college = this.dataset.college;

                document.querySelectorAll('.product-item').forEach(item => {

                    if (college === '' || (item.dataset.colleges || '').split(/\s+/).includes(college)) {
                        item.classList.remove('hidden');
                    } else {
                        item.classList.add('hidden');
                    }

                });

            });

        });
    </script>


    <script>
        (function(){
            const el = document.querySelector(".collegeSwiper");
            if(!el) return;
            const count = el.querySelectorAll(".swiper-slide").length;
            new Swiper(el,{
                slidesPerView:"auto",
                spaceBetween:20,
                loop: false, rewind: true, watchOverflow: true,
                loopAdditionalSlides:20,
                grabCursor:true,
                watchOverflow:false,
                speed:800,
                autoplay:{ delay:2500, disableOnInteraction:false, pauseOnMouseEnter:true },
                navigation:{ nextEl:".college-next", prevEl:".college-prev" },
                pagination:{ el:".swiper-pagination", clickable:true },
                breakpoints:{
                    320:{spaceBetween:12},
                    768:{spaceBetween:20},
                    1024:{spaceBetween:24}
                }
            });
        })();
    </script>

    <script>
        (function(){
            const el = document.querySelector(".newProductsSwiper");
            if(!el) return;
            const count = el.querySelectorAll(".swiper-slide").length;
            new Swiper(el,{
                slidesPerView:2,
                spaceBetween:16,
                loop: false, rewind: true, watchOverflow: true,
                speed:700,
                grabCursor:true,
                autoplay:{ delay:2500, disableOnInteraction:false, pauseOnMouseEnter:true },
                navigation:{ nextEl:".new-next", prevEl:".new-prev" },
                breakpoints:{
                    640:{slidesPerView:2},
                    768:{slidesPerView:3},
                    1024:{slidesPerView:4},
                    1280:{slidesPerView:5}
                }
            });
        })();
    </script>

    <script src="{{ asset('js/ajax.js') }}?v={{ @filemtime(public_path('js/ajax.js')) ?: time() }}"></script>
    <script src="{{ asset('js/main.js') }}?v={{ @filemtime(public_path('js/main.js')) ?: time() }}"></script>
    <script defer src="{{ asset('js/swiper.js') }}?v={{ @filemtime(public_path('js/swiper.js')) ?: time() }}"></script>
    {{-- Instant page navigation: prefetches links on hover/touchstart --}}
    <script src="https://instant.page/5.2.0" type="module" integrity="sha384-jnZyxPjiipYXnSU0ygqeac2q7CVYMbh84q0uHVRRxEtvFPiQYbXWUorga2aqZJ0z"></script>

    @stack('scripts')
</body>
</html>
