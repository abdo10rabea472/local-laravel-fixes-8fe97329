@extends('layouts.front')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/FAQS-PAGE.css') }}">
@endpush

@section('content')
<section class="hero-bg text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-16 sm:py-20 text-center">
        <span class="inline-flex items-center gap-2 bg-white/20 backdrop-blur px-5 py-2 rounded-full text-sm font-medium mb-5">
            <i class="fa-solid fa-circle-question"></i> Got Questions?
        </span>
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight mb-4">{{ $page?->title ?: 'Frequently Asked Questions' }}</h1>
        <p class="text-lg text-white/90 max-w-2xl mx-auto">{{ $page?->seo_description ?: 'Find quick answers about ordering lab equipment, shipping, returns, and more.' }}</p>
    </div>
</section>

@if($page && trim((string) $page->content) !== '' && !str_starts_with(ltrim($page->content), '['))
<main class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
    <div class="prose prose-slate prose-lg max-w-none bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        {!! $page->content !!}
    </div>
</main>
@else
    </div>
</section>

<main class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
    <div class="relative mb-10">
        <input type="text" id="faqSearch" placeholder="Search questions..."
            class="w-full bg-white border border-slate-200 focus:border-teal-500 rounded-2xl py-4 px-6 text-base outline-none shadow-sm">
        <i class="fa-solid fa-magnifying-glass absolute right-6 top-1/2 -translate-y-1/2 text-slate-400"></i>
    </div>

    <div class="space-y-4" id="faqContainer">
        @foreach($faqs as $faq)
        <div class="faq-item policy-card bg-white rounded-2xl shadow-sm overflow-hidden border border-slate-100">
            <button type="button" class="faq-toggle w-full text-left px-6 py-5 flex items-center justify-between font-bold text-slate-900">
                <span>{{ $faq['q'] }}</span>
                <i class="fa-solid fa-chevron-down text-slate-400 transition-transform"></i>
            </button>
            <div class="faq-answer px-6 pb-5 text-slate-600 leading-relaxed hidden">{{ $faq['a'] }}</div>
        </div>
        @endforeach
    </div>
</main>
@endif
@endsection

@push('scripts')
<script>
document.querySelectorAll('.faq-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
        const answer = btn.nextElementSibling;
        const icon = btn.querySelector('i');
        const open = !answer.classList.contains('hidden');
        document.querySelectorAll('.faq-answer').forEach(a => a.classList.add('hidden'));
        document.querySelectorAll('.faq-toggle i').forEach(i => i.style.transform = '');
        if (!open) { answer.classList.remove('hidden'); icon.style.transform = 'rotate(180deg)'; }
    });
});
document.getElementById('faqSearch')?.addEventListener('input', e => {
    const term = e.target.value.toLowerCase();
    document.querySelectorAll('.faq-item').forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(term) ? '' : 'none';
    });
});
</script>
@endpush
