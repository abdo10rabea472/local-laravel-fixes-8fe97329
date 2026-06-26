@php
    $body = is_array($section->content ?? null) ? ($section->content['body'] ?? null) : null;
    $primary = $primary ?? '#10b981';
@endphp
@if($section->title || $body)
<section class="relative rounded-3xl overflow-hidden bg-white border border-slate-200/70 shadow-[0_10px_30px_-15px_rgba(15,23,42,0.1)]">
    <span class="absolute left-0 top-0 h-full w-1.5" style="background: linear-gradient(180deg, {{ $primary }}, {{ $secondary ?? $primary }});"></span>
    <div class="relative z-10 p-6 sm:p-8 pl-8 sm:pl-10">
        @if($section->title)
            <h3 class="text-xl sm:text-2xl font-black text-slate-900 mb-3 tracking-tight">{{ $section->title }}</h3>
        @endif
        @if($body)
            <div class="text-slate-600 leading-relaxed">{!! nl2br(e($body)) !!}</div>
        @endif
    </div>
</section>
@endif
