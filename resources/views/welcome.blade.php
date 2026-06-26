@extends('layouts.front')

@section('content')

{{-- ═══════════════ HERO BANNER ═══════════════ --}}
<section class="bg-gradient-to-b from-blue-600 to-blue-700 pt-4 pb-8">
    <div class="max-w-[1400px] mx-auto px-3 sm:px-4">

        {{-- Installments countdown strip --}}
        <div class="bg-white rounded-2xl overflow-hidden shadow-lg mb-4">
            <div class="flex items-stretch flex-wrap">
                <div class="bg-gradient-to-l from-amber-400 to-amber-500 text-slate-900 px-4 sm:px-6 py-3 flex items-center gap-2 font-black text-sm sm:text-base">
                    <i class="fa-solid fa-bolt"></i>
                    <span>عرض حصري</span>
                </div>
                <div class="flex-1 flex items-center justify-center gap-3 sm:gap-6 px-4 py-3 flex-wrap">
                    <span class="font-black text-slate-900 text-sm sm:text-base">قسط مشترياتك بدون فوائد حتى 18 شهر</span>
                    <div class="flex items-center gap-1.5" id="hero-countdown">
                        @foreach(['أيام'=>'D','ساعات'=>'H','دقائق'=>'M','ثواني'=>'S'] as $label=>$key)
                        <div class="text-center">
                            <div class="w-9 h-9 sm:w-10 sm:h-10 bg-blue-600 text-white rounded-lg flex items-center justify-center font-black text-sm" data-cd="{{ $key }}">00</div>
                            <div class="text-[9px] text-slate-500 font-bold mt-0.5">{{ $label }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Main hero banner --}}
        <div class="relative rounded-3xl overflow-hidden shadow-2xl shadow-blue-900/30 bg-gradient-to-l from-emerald-600 via-emerald-700 to-emerald-800 min-h-[260px] sm:min-h-[340px]">
            @if($hero['background'])
                <img src="{{ $hero['background'] }}" alt="" class="absolute inset-0 w-full h-full object-cover opacity-30 mix-blend-overlay">
            @endif
            <div class="absolute -left-32 top-1/2 -translate-y-1/2 w-96 h-96 bg-yellow-400/20 rounded-full blur-3xl"></div>
            <div class="relative grid grid-cols-1 md:grid-cols-2 gap-6 p-6 sm:p-10 items-center min-h-[260px] sm:min-h-[340px]">
                <div class="text-white">
                    <span class="inline-block bg-yellow-400 text-emerald-900 text-xs font-black px-3 py-1 rounded-full mb-3">{{ $hero['badge'] ?? 'كاش باك حتى 36 شهر' }}</span>
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black leading-tight mb-3">
                        {{ $hero['title'] }}
                    </h1>
                    <p class="text-emerald-50 text-base sm:text-lg max-w-md mb-6">{{ $hero['subtitle'] }}</p>
                    <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 bg-white text-emerald-700 font-black px-7 py-3.5 rounded-xl hover:bg-yellow-300 hover:text-emerald-900 transition shadow-xl">
                        <i class="fa-solid fa-bag-shopping"></i> تسوّق الآن
                    </a>
                </div>
                <div class="hidden md:flex items-center justify-end">
                    <div class="relative">
                        <div class="absolute inset-0 bg-white/10 backdrop-blur rounded-full blur-2xl scale-110"></div>
                        <div class="relative w-44 h-44 lg:w-56 lg:h-56 rounded-full bg-white/15 backdrop-blur border-4 border-white/30 flex items-center justify-center text-white">
                            <div class="text-center">
                                <div class="text-6xl lg:text-8xl font-black leading-none">30<span class="text-3xl lg:text-4xl">%</span></div>
                                <div class="text-sm font-bold mt-1">خصم فوري</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════ CATEGORY CIRCLES ═══════════════ --}}
@if($mainCategories->isNotEmpty())
<section class="bg-white py-6 sm:py-8 border-b border-slate-100">
    <div class="max-w-[1400px] mx-auto px-3 sm:px-4">
        <div class="flex gap-3 sm:gap-5 overflow-x-auto pb-2 scrollbar-thin snap-x">
            @foreach($mainCategories as $i => $cat)
                <a href="{{ route('category.show', $cat->slug) }}" class="snap-start flex-shrink-0 flex flex-col items-center gap-2 group w-20 sm:w-24">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-gradient-to-br from-blue-50 to-slate-100 border-2 border-slate-200 group-hover:border-blue-500 group-hover:scale-105 transition-all p-1 flex items-center justify-center overflow-hidden">
                        @if($cat->image)
                            <img src="{{ asset('storage/'.$cat->image) }}" alt="{{ $cat->name }}" class="w-full h-full object-cover rounded-full" loading="lazy">
                        @else
                            <i class="fa-solid fa-tag text-2xl text-blue-500"></i>
                        @endif
                    </div>
                    <span class="text-xs font-bold text-slate-700 group-hover:text-blue-600 text-center line-clamp-1">{{ $cat->name }}</span>
                </a>
            @endforeach
            <a href="{{ route('products.index') }}" class="snap-start flex-shrink-0 flex flex-col items-center gap-2 group w-20 sm:w-24">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-gradient-to-br from-rose-500 to-rose-600 text-white flex items-center justify-center group-hover:scale-105 transition shadow-lg shadow-rose-500/30">
                    <i class="fa-solid fa-percent text-2xl"></i>
                </div>
                <span class="text-xs font-bold text-rose-600 text-center">عروض الخصومات</span>
            </a>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════ BEST SELLERS ═══════════════ --}}
@if($featuredProducts->isNotEmpty())
<section class="bg-slate-50 py-10">
    <div class="max-w-[1400px] mx-auto px-3 sm:px-4">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900">{{ $homeSections['featured_title'] ?? 'الأكثر مبيعاً' }}</h2>
            <a href="{{ route('products.index') }}" class="text-blue-600 font-bold text-sm hover:underline flex items-center gap-1">
                عرض الكل <i class="fa-solid fa-chevron-left text-xs"></i>
            </a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
            @foreach($featuredProducts->take(5) as $product)
                @include('components.product-card', ['product' => $product])
            @endforeach
        </div>
        <div class="flex justify-center mt-8">
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold px-8 py-3 rounded-full shadow-lg shadow-blue-500/20 transition">
                <i class="fa-solid fa-arrow-left"></i> استكشف المزيد
            </a>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════ PAY LATER BANNER ═══════════════ --}}
<section class="py-8 bg-white">
    <div class="max-w-[1400px] mx-auto px-3 sm:px-4">
        <div class="bg-gradient-to-l from-blue-50 to-indigo-50 rounded-3xl border border-blue-100 p-6 sm:p-10 grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
            <div class="text-center md:text-right">
                <h3 class="text-2xl sm:text-3xl font-black text-slate-900 mb-3">اشتر الآن وادفع لاحقاً</h3>
                <p class="text-slate-600 mb-5">قسّط طلبك بسهولة عبر شركاء الدفع المعتمدين</p>
                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-3 rounded-xl transition">
                    اعرف المزيد <i class="fa-solid fa-arrow-left"></i>
                </a>
            </div>
            <div class="flex flex-wrap items-center justify-center gap-3">
                @foreach(['halan','TRU','QNB','saib','VOD','contact'] as $p)
                <div class="bg-white rounded-xl px-4 py-3 shadow-md font-black text-blue-700 text-sm min-w-[80px] text-center border border-slate-100">{{ $p }}</div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════ 3 PROMO TILES ═══════════════ --}}
<section class="py-6 bg-white">
    <div class="max-w-[1400px] mx-auto px-3 sm:px-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('products.index') }}" class="relative overflow-hidden rounded-2xl bg-gradient-to-l from-blue-500 to-blue-700 text-white p-6 min-h-[140px] flex flex-col justify-between group hover:shadow-xl transition">
            <div>
                <p class="text-xs font-bold opacity-90 mb-1">توصيل سريع</p>
                <h4 class="text-xl font-black leading-tight">اطلب واستلم خلال 30 دقيقة</h4>
            </div>
            <i class="fa-solid fa-truck-fast text-4xl opacity-30 self-end"></i>
        </a>
        <a href="{{ route('products.index') }}" class="relative overflow-hidden rounded-2xl bg-gradient-to-l from-amber-400 to-yellow-500 text-slate-900 p-6 min-h-[140px] flex flex-col justify-between group hover:shadow-xl transition">
            <div>
                <p class="text-xs font-bold opacity-80 mb-1">عرض خاص</p>
                <h4 class="text-xl font-black leading-tight">تركيب وتوصيل مجاني على التكييفات</h4>
            </div>
            <i class="fa-solid fa-snowflake text-4xl opacity-30 self-end"></i>
        </a>
        <a href="{{ route('products.index') }}" class="relative overflow-hidden rounded-2xl bg-gradient-to-l from-emerald-500 to-emerald-700 text-white p-6 min-h-[140px] flex flex-col justify-between group hover:shadow-xl transition">
            <div>
                <p class="text-xs font-bold opacity-90 mb-1">صديق البيئة</p>
                <h4 class="text-xl font-black leading-tight">طاقة وتوفير كل يوم</h4>
            </div>
            <i class="fa-solid fa-leaf text-4xl opacity-30 self-end"></i>
        </a>
    </div>
</section>

{{-- ═══════════════ FEATURED CATEGORIES TILES ═══════════════ --}}
@if($mainCategories->isNotEmpty())
<section class="py-10 bg-white">
    <div class="max-w-[1400px] mx-auto px-3 sm:px-4">
        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 text-center mb-6">متاح الآن في {{ config('app.name', 'متجرنا') }}</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
            @foreach($mainCategories->take(6) as $cat)
                <a href="{{ route('category.show', $cat->slug) }}" class="group relative aspect-square rounded-2xl bg-blue-600 hover:bg-blue-700 transition shadow-md overflow-hidden flex items-end p-3">
                    @if($cat->image)
                        <img src="{{ asset('storage/'.$cat->image) }}" alt="{{ $cat->name }}" class="absolute inset-0 w-full h-full object-cover opacity-50 group-hover:opacity-70 transition" loading="lazy">
                    @endif
                    <div class="relative w-full bg-white/95 backdrop-blur rounded-xl py-2 text-center">
                        <span class="text-xs sm:text-sm font-black text-slate-900 line-clamp-1 px-1">{{ $cat->name }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════ BEST DEALS WITH TABS ═══════════════ --}}
@if($products->isNotEmpty())
<section class="py-10 bg-slate-50">
    <div class="max-w-[1400px] mx-auto px-3 sm:px-4">
        <div class="text-center mb-6">
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900">أفضل العروض</h2>
            @if($mainCategories->isNotEmpty())
            <div class="flex flex-wrap justify-center gap-2 mt-4">
                <button class="deals-tab active px-4 py-1.5 rounded-full text-xs font-bold bg-blue-600 text-white">الكل</button>
                @foreach($mainCategories->take(4) as $c)
                <button class="deals-tab px-4 py-1.5 rounded-full text-xs font-bold bg-white text-slate-700 border border-slate-200 hover:border-blue-500">{{ $c->name }}</button>
                @endforeach
            </div>
            @endif
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
            @foreach($products->take(5) as $product)
                @include('components.product-card', ['product' => $product])
            @endforeach
        </div>
        <div class="flex justify-center mt-8">
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold px-8 py-3 rounded-full shadow-lg shadow-blue-500/20 transition">
                <i class="fa-solid fa-arrow-left"></i> استكشف العروض
            </a>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════ BRAND BANNER ═══════════════ --}}
<section class="py-8 bg-white">
    <div class="max-w-[1400px] mx-auto px-3 sm:px-4">
        <div class="relative rounded-3xl overflow-hidden bg-gradient-to-l from-blue-700 via-blue-600 to-blue-800 text-white p-8 sm:p-12 grid grid-cols-1 md:grid-cols-2 gap-6 items-center shadow-xl">
            <div class="absolute -right-20 -top-20 w-72 h-72 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute -left-20 -bottom-20 w-80 h-80 bg-amber-400/10 rounded-full blur-3xl"></div>
            <div class="relative">
                <span class="inline-block bg-white/20 backdrop-blur text-xs font-bold px-3 py-1 rounded-full mb-3">العلامة المميزة</span>
                <h3 class="text-3xl sm:text-5xl font-black mb-3 leading-tight">المتجر الرسمي<br><span class="text-amber-300">لأفضل الماركات</span></h3>
                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 bg-white text-blue-700 font-black px-6 py-3 rounded-xl hover:bg-amber-300 hover:text-blue-900 transition shadow-lg">
                    اشترِ الآن <i class="fa-solid fa-arrow-left"></i>
                </a>
            </div>
            <div class="relative hidden md:flex items-center justify-center gap-4 flex-wrap">
                @foreach(['fa-mobile-screen','fa-tv','fa-headphones','fa-laptop'] as $ic)
                <div class="w-20 h-20 rounded-2xl bg-white/10 backdrop-blur border border-white/20 flex items-center justify-center text-3xl">
                    <i class="fa-solid {{ $ic }}"></i>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════ SELECTED FOR YOU ═══════════════ --}}
@if($products->isNotEmpty())
<section class="py-10 bg-white">
    <div class="max-w-[1400px] mx-auto px-3 sm:px-4">
        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 text-center mb-6">اخترنا لك</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
            @foreach($products->take(10) as $product)
                @include('components.product-card', ['product' => $product])
            @endforeach
        </div>
        <div class="flex justify-center mt-8">
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold px-8 py-3 rounded-full shadow-lg shadow-blue-500/20 transition">
                <i class="fa-solid fa-arrow-left"></i> استكشف تخصيصاتنا
            </a>
        </div>
        @if($products->hasPages())
        <div class="mt-8 flex justify-center">{{ $products->withQueryString()->links() }}</div>
        @endif
    </div>
</section>
@endif

{{-- ═══════════════ NEWSLETTER ═══════════════ --}}
<section class="py-12 bg-gradient-to-l from-blue-600 to-blue-700 text-white">
    <div class="max-w-3xl mx-auto px-4 text-center">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white/20 backdrop-blur text-2xl mb-4">
            <i class="fa-regular fa-envelope"></i>
        </div>
        <h2 class="text-2xl sm:text-3xl font-black mb-3">اشترك في النشرة البريدية</h2>
        <p class="text-blue-100 mb-6">احصل على آخر العروض والمنتجات الجديدة مباشرة في بريدك</p>
        <form action="#" method="post" class="flex flex-col sm:flex-row gap-3 max-w-lg mx-auto">
            @csrf
            <input type="email" required placeholder="بريدك الإلكتروني" class="flex-1 h-12 px-5 bg-white text-slate-900 rounded-xl text-sm outline-none focus:ring-2 focus:ring-amber-400">
            <button type="submit" class="h-12 px-8 bg-amber-400 hover:bg-amber-300 text-blue-900 font-black rounded-xl transition">
                اشترك الآن
            </button>
        </form>
    </div>
</section>

<script>
// Hero countdown — 36 days from now (placeholder offer end)
(function(){
    const end = new Date(); end.setDate(end.getDate()+18); end.setHours(end.getHours()+5);
    function tick(){
        const diff = Math.max(0, end - new Date());
        const d = Math.floor(diff/86400000),
              h = Math.floor(diff/3600000)%24,
              m = Math.floor(diff/60000)%60,
              s = Math.floor(diff/1000)%60;
        const map = {D:d, H:h, M:m, S:s};
        document.querySelectorAll('[data-cd]').forEach(el => {
            el.textContent = String(map[el.dataset.cd]).padStart(2,'0');
        });
    }
    tick(); setInterval(tick, 1000);
})();

// Deals tabs visual toggle
document.querySelectorAll('.deals-tab').forEach(b => b.addEventListener('click', e => {
    document.querySelectorAll('.deals-tab').forEach(x => {
        x.classList.remove('bg-blue-600','text-white');
        x.classList.add('bg-white','text-slate-700','border','border-slate-200');
    });
    e.currentTarget.classList.add('bg-blue-600','text-white');
    e.currentTarget.classList.remove('bg-white','text-slate-700','border','border-slate-200');
}));
</script>
@endsection
