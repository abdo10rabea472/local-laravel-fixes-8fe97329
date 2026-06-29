@extends('layouts.front')

@push('styles')
<style>
    .faq-hero {
        background:
            radial-gradient(1200px 500px at 10% -20%, rgba(167, 139, 250, .35), transparent 60%),
            radial-gradient(900px 500px at 100% 0%, rgba(99, 102, 241, .4), transparent 55%),
            linear-gradient(135deg, #4c1d95 0%, #5b21b6 45%, #3730a3 100%);
    }
    .faq-card {
        transition: box-shadow .25s ease, transform .25s ease, border-color .25s ease;
    }
    .faq-card:hover { border-color: rgb(139 92 246 / .35); box-shadow: 0 18px 40px -24px rgb(124 58 237 / .35); }
    .faq-card.is-open { border-color: rgb(139 92 246 / .55); box-shadow: 0 20px 50px -25px rgb(124 58 237 / .4); }
    .faq-card.is-open .faq-icon { background: linear-gradient(135deg, #7c3aed, #4f46e5); color: #fff; }
    .faq-card.is-open .faq-chevron { transform: rotate(180deg); color: #7c3aed; }
    .faq-answer {
        display: grid;
        grid-template-rows: 0fr;
        transition: grid-template-rows .35s ease;
    }
    .faq-answer > div { overflow: hidden; }
    .faq-card.is-open .faq-answer { grid-template-rows: 1fr; }
    .chip { transition: all .2s ease; }
    .chip.active {
        background: linear-gradient(135deg, #7c3aed, #4f46e5);
        color: #fff;
        border-color: transparent;
        box-shadow: 0 10px 25px -12px rgb(124 58 237 / .55);
    }
    [dir="rtl"] .faq-search-icon { right: auto; left: 1.5rem; }
</style>
@endpush

@section('content')
<section class="faq-hero text-white relative overflow-hidden">
    <div class="absolute inset-0 opacity-[0.06] pointer-events-none"
         style="background-image: radial-gradient(circle at 1px 1px, #fff 1px, transparent 0); background-size: 22px 22px;"></div>
    <div class="relative max-w-6xl mx-auto px-4 sm:px-6 py-20 sm:py-28 text-center">
        <span class="inline-flex items-center gap-2 bg-white/10 ring-1 ring-white/20 backdrop-blur px-5 py-2 rounded-full text-sm font-semibold mb-6">
            <i class="fa-solid fa-circle-question text-violet-200"></i>
            {{ __('app.faq_badge') ?: 'Help Center' }}
        </span>
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight mb-5 bg-gradient-to-br from-white via-white to-violet-200 bg-clip-text text-transparent">
            {{ $page?->title ?: 'Frequently Asked Questions' }}
        </h1>
        <p class="text-lg text-white/80 max-w-2xl mx-auto leading-relaxed">
            {{ $page?->seo_description ?: 'Find quick answers about ordering lab equipment, shipping, payments, returns, and more.' }}
        </p>

        <div class="mt-10 flex flex-wrap justify-center gap-3 sm:gap-6 text-sm">
            <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 ring-1 ring-white/15">
                <i class="fa-solid fa-headset text-violet-200"></i> 24/7 Support
            </div>
            <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 ring-1 ring-white/15">
                <i class="fa-solid fa-truck-fast text-indigo-300"></i> Fast Shipping
            </div>
            <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 ring-1 ring-white/15">
                <i class="fa-solid fa-shield-halved text-indigo-200"></i> Secure Payments
            </div>
        </div>
    </div>
    <svg class="block w-full text-slate-50" viewBox="0 0 1440 60" preserveAspectRatio="none" aria-hidden="true">
        <path fill="currentColor" d="M0 30 Q 360 60 720 30 T 1440 30 V60 H0 Z"/>
    </svg>
</section>

@if($page && trim((string) $page->content) !== '' && !str_starts_with(ltrim($page->content), '['))
<main class="max-w-4xl mx-auto px-4 sm:px-6 py-14">
    <div class="prose prose-slate prose-lg max-w-none bg-white rounded-3xl shadow-sm border border-slate-100 p-8 sm:p-10">
        {!! $page->content !!}
    </div>
</main>
@else
<main class="max-w-5xl mx-auto px-4 sm:px-6 -mt-6 pb-20">
    {{-- Search --}}
    <div class="relative mb-8">
        <input type="text" id="faqSearch" placeholder="Search questions, keywords…"
            class="w-full bg-white border border-slate-200 focus:border-violet-500 focus:ring-4 focus:ring-violet-500/15 rounded-2xl py-4 ps-14 pe-6 text-base outline-none shadow-lg shadow-slate-900/5 transition">
        <i class="fa-solid fa-magnifying-glass absolute start-6 top-1/2 -translate-y-1/2 text-slate-400 faq-search-icon"></i>
    </div>

    {{-- Category chips --}}
    @php
        $grouped = collect($faqs)->groupBy(fn($f) => $f['category'] ?? 'General');
        $categories = $grouped->keys();
    @endphp
    @if($categories->count() > 1)
    <div class="flex flex-wrap gap-2 mb-8" id="faqChips">
        <button type="button" class="chip active px-4 py-2 rounded-full text-sm font-semibold border border-slate-200 bg-white text-slate-700" data-cat="all">
            <i class="fa-solid fa-layer-group me-1.5"></i> All
        </button>
        @foreach($categories as $cat)
        <button type="button" class="chip px-4 py-2 rounded-full text-sm font-semibold border border-slate-200 bg-white text-slate-700" data-cat="{{ $cat }}">
            {{ $cat }}
        </button>
        @endforeach
    </div>
    @endif

    {{-- FAQ list --}}
    <div class="space-y-4" id="faqContainer">
        @foreach($faqs as $i => $faq)
        <div class="faq-card faq-item bg-white rounded-2xl border border-slate-200/80 overflow-hidden"
             data-cat="{{ $faq['category'] ?? 'General' }}">
            <button type="button" class="faq-toggle w-full text-start px-5 sm:px-6 py-5 flex items-center gap-4">
                <span class="faq-icon flex-none w-10 h-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center text-sm font-bold transition-colors">
                    {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}
                </span>
                <span class="flex-1 font-bold text-slate-900 text-base sm:text-lg leading-snug">{{ $faq['q'] }}</span>
                <i class="fa-solid fa-chevron-down faq-chevron text-slate-400 transition-transform flex-none"></i>
            </button>
            <div class="faq-answer">
                <div>
                    <div class="px-5 sm:px-6 pb-6 ps-[4.5rem] text-slate-600 leading-relaxed border-t border-slate-100 pt-4">
                        {{ $faq['a'] }}
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div id="faqEmpty" class="hidden text-center py-16">
        <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-4">
            <i class="fa-solid fa-magnifying-glass text-slate-400 text-xl"></i>
        </div>
        <p class="font-bold text-slate-700">No matching questions</p>
        <p class="text-sm text-slate-500 mt-1">Try a different keyword or browse another category.</p>
    </div>

    {{-- Still need help CTA --}}
    <div class="mt-14 rounded-3xl overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white p-8 sm:p-10 grid sm:grid-cols-[1fr_auto] gap-6 items-center">
        <div>
            <h3 class="text-2xl sm:text-3xl font-black tracking-tight mb-2">Still have a question?</h3>
            <p class="text-slate-300">Our team usually replies within a few hours during business days.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('contact') }}"
               class="inline-flex items-center gap-2 bg-white text-slate-900 hover:bg-violet-50 font-bold px-6 py-3 rounded-xl transition">
                <i class="fa-solid fa-envelope"></i> Contact us
            </a>
            @php $wa = \App\Models\SiteSetting::get('contact_whatsapp') ?: \App\Models\SiteSetting::get('contact_phone'); @endphp
            @if($wa)
            <a href="https://wa.me/{{ preg_replace('/\D+/', '', $wa) }}" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-400 text-white font-bold px-6 py-3 rounded-xl transition">
                <i class="fa-brands fa-whatsapp"></i> WhatsApp
            </a>
            @endif
        </div>
    </div>
</main>
@endif
@endsection

@push('scripts')
<script>
(function () {
    const cards = document.querySelectorAll('.faq-card');
    const empty = document.getElementById('faqEmpty');
    const chips = document.querySelectorAll('#faqChips .chip');
    const search = document.getElementById('faqSearch');
    let activeCat = 'all';

    cards.forEach(card => {
        card.querySelector('.faq-toggle').addEventListener('click', () => {
            const isOpen = card.classList.contains('is-open');
            cards.forEach(c => c.classList.remove('is-open'));
            if (!isOpen) card.classList.add('is-open');
        });
    });

    function apply() {
        const term = (search?.value || '').toLowerCase().trim();
        let visible = 0;
        cards.forEach(card => {
            const matchCat = activeCat === 'all' || card.dataset.cat === activeCat;
            const matchTerm = !term || card.textContent.toLowerCase().includes(term);
            const show = matchCat && matchTerm;
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        if (empty) empty.classList.toggle('hidden', visible !== 0);
    }

    chips.forEach(chip => {
        chip.addEventListener('click', () => {
            chips.forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            activeCat = chip.dataset.cat;
            apply();
        });
    });

    search?.addEventListener('input', apply);
})();
</script>
@endpush
