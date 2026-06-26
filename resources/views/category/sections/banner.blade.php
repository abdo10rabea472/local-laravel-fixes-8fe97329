@php
    $bgUrl = $section->background_image
        ? asset('storage/' . $section->background_image)
        : ($college?->banner_url ?? null);
    $body = is_array($section->content ?? null) ? ($section->content['body'] ?? null) : null;
    $primary = $primary ?? '#10b981';
    $secondary = $secondary ?? '#22d3ee';
@endphp
<section class="relative rounded-3xl overflow-hidden min-h-[260px] flex items-center shadow-[0_20px_50px_-20px_rgba(15,23,42,0.15)]"
    @if($bgUrl)
        style="background-image: url('{{ $bgUrl }}'); background-size: cover; background-position: center;"
    @else
        style="background: linear-gradient(135deg, {{ $primary }}, {{ $secondary }});"
    @endif>
    @if($bgUrl)
        <div class="absolute inset-0 bg-gradient-to-r from-slate-900/70 via-slate-900/50 to-transparent"></div>
    @endif
    <div class="relative z-10 p-8 sm:p-12 w-full max-w-3xl">
        @if($section->title)
            <h3 class="text-2xl sm:text-4xl font-black text-white mb-3 tracking-tight">{{ $section->title }}</h3>
        @endif
        @if($body)
            <div class="text-white/90 text-base leading-relaxed">{!! nl2br(e($body)) !!}</div>
        @endif
    </div>
</section>
