@php
    $bgUrl = $section->background_image
        ? asset('storage/' . $section->background_image)
        : null;
    $imageUrl = $section->image ? asset('storage/' . $section->image) : null;
    $primary = $primary ?? '#10b981';
    $secondary = $secondary ?? '#22d3ee';
    $text = is_array($section->content ?? null)
        ? ($section->content['body'] ?? null)
        : ($section->content ?? null);
@endphp
@if($section->title || $text || $imageUrl)
<section class="relative rounded-3xl overflow-hidden border border-slate-200/70 shadow-[0_10px_30px_-15px_rgba(15,23,42,0.1)]"
    @if($bgUrl)
        style="background-image: url('{{ $bgUrl }}'); background-size: cover; background-position: center;"
    @else
        style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);"
    @endif>
    @if($bgUrl)
        <div class="absolute inset-0 bg-slate-900/55"></div>
    @endif
    <div class="relative z-10 p-6 sm:p-8 grid {{ $imageUrl ? 'md:grid-cols-[1fr_auto] md:items-center' : '' }} gap-6">
        <div>
            @if($section->title)
                <h3 class="text-xl sm:text-2xl font-black mb-3 tracking-tight {{ $bgUrl ? 'text-white' : 'text-slate-900' }}">
                    {{ $section->title }}
                </h3>
            @endif
            @if($text)
                <div class="leading-relaxed {{ $bgUrl ? 'text-white/90' : 'text-slate-600' }}">{{ $text }}</div>
            @endif
        </div>
        @if($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $section->title ?? '' }}"
                class="rounded-2xl max-h-56 w-full md:w-72 object-cover shadow-lg">
        @endif
    </div>
</section>
@endif
