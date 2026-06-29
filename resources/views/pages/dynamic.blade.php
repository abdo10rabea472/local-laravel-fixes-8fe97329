@extends('layouts.front')

@section('content')
@php
    $updated = $page->updated_at ?? $page->created_at;
    $readMins = max(1, (int) ceil(str_word_count(strip_tags((string) $page->content)) / 200));
@endphp

<section class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-indigo-950 to-violet-900 text-white">
    <div class="absolute inset-0 opacity-30 pointer-events-none"
         style="background-image: radial-gradient(circle at 20% 20%, rgba(139,92,246,.35), transparent 40%), radial-gradient(circle at 80% 0%, rgba(59,130,246,.25), transparent 45%);"></div>
    <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-b from-transparent to-slate-50"></div>

    <div class="relative mx-auto px-4 sm:px-6 py-16 sm:py-20" style="max-width: 121rem;">
        <nav class="flex items-center gap-2 text-xs text-slate-300 mb-6">
            <a href="{{ url('/') }}" class="hover:text-white transition-colors">{{ __('messages.home') ?? 'Home' }}</a>
            <i class="fa-solid fa-angle-left text-[10px] opacity-60"></i>
            <span class="text-white/90 font-semibold truncate">{{ $page->title }}</span>
        </nav>

        <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur border border-white/15 text-[11px] uppercase tracking-widest font-bold px-3 py-1.5 rounded-full mb-5">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
            Page
        </div>

        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-[1.1] mb-5 max-w-3xl">
            {{ $page->title }}
        </h1>

        @if($page->seo_description)
            <p class="text-lg sm:text-xl text-slate-200/90 leading-relaxed max-w-2xl">{{ $page->seo_description }}</p>
        @endif

        <div class="mt-8 flex flex-wrap items-center gap-3 text-xs">
            @if($updated)
                <span class="inline-flex items-center gap-2 bg-white/10 backdrop-blur border border-white/15 rounded-full px-3 py-1.5">
                    <i class="fa-regular fa-calendar text-slate-300"></i>
                    {{ $updated->translatedFormat('d M Y') }}
                </span>
            @endif
            <span class="inline-flex items-center gap-2 bg-white/10 backdrop-blur border border-white/15 rounded-full px-3 py-1.5">
                <i class="fa-regular fa-clock text-slate-300"></i>
                {{ $readMins }} min read
            </span>
        </div>
    </div>
</section>

<main class="bg-slate-50 pb-20 -mt-10 relative">
    <div class="mx-auto px-4 sm:px-6" style="max-width: 121rem;">
        <article class="bg-white rounded-3xl shadow-xl shadow-slate-900/5 border border-slate-100 overflow-hidden">
            <div class="h-1.5 bg-gradient-to-r from-violet-500 via-indigo-500 to-blue-500"></div>
            <div class="p-6 sm:p-10 lg:p-14">
                @if(trim((string) $page->content) !== '')
                    <div class="prose prose-slate prose-lg max-w-none break-words [overflow-wrap:anywhere]
                                prose-headings:font-black prose-headings:tracking-tight
                                prose-h2:text-3xl prose-h2:mt-10 prose-h2:mb-4
                                prose-h3:text-2xl prose-h3:mt-8
                                prose-p:break-words
                                prose-a:text-violet-600 prose-a:no-underline prose-a:break-all hover:prose-a:text-violet-700 hover:prose-a:underline
                                prose-strong:text-slate-900
                                prose-blockquote:border-violet-500 prose-blockquote:bg-violet-50/40 prose-blockquote:rounded-r-xl prose-blockquote:not-italic prose-blockquote:py-1
                                prose-img:rounded-2xl prose-img:shadow-md
                                prose-code:bg-slate-100 prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded prose-code:text-violet-700 prose-code:before:content-none prose-code:after:content-none
                                prose-pre:whitespace-pre-wrap prose-pre:break-words
                                prose-table:rounded-xl prose-table:overflow-hidden">

                        {!! $page->content !!}
                    </div>
                @else
                    <div class="text-center py-16">
                        <div class="inline-flex items-center justify-center h-16 w-16 rounded-2xl bg-slate-100 text-slate-400 mb-4">
                            <i class="fa-regular fa-file-lines text-2xl"></i>
                        </div>
                        <p class="text-slate-500 italic">This page has no content yet.</p>
                    </div>
                @endif
            </div>

            <div class="border-t border-slate-100 px-6 sm:px-10 lg:px-14 py-5 bg-slate-50/60 flex flex-wrap items-center justify-between gap-3">
                <div class="text-xs text-slate-500">
                    @if($updated)
                        <i class="fa-regular fa-pen-to-square me-1"></i>
                        Last updated {{ $updated->diffForHumans() }}
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-500 me-1">Share:</span>
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}&text={{ urlencode($page->title) }}" target="_blank" rel="noopener"
                       class="h-8 w-8 inline-flex items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:text-violet-600 hover:border-violet-300 transition">
                        <i class="fa-brands fa-x-twitter text-xs"></i>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}" target="_blank" rel="noopener"
                       class="h-8 w-8 inline-flex items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:text-violet-600 hover:border-violet-300 transition">
                        <i class="fa-brands fa-facebook-f text-xs"></i>
                    </a>
                    <a href="https://wa.me/?text={{ urlencode($page->title . ' ' . request()->fullUrl()) }}" target="_blank" rel="noopener"
                       class="h-8 w-8 inline-flex items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:text-emerald-600 hover:border-emerald-300 transition">
                        <i class="fa-brands fa-whatsapp text-xs"></i>
                    </a>
                </div>
            </div>
        </article>

        <div class="mt-8 text-center">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-600 hover:text-violet-600 transition">
                <i class="fa-solid fa-arrow-right-long"></i>
                {{ __('messages.back_home') ?? 'Back to home' }}
            </a>
        </div>
    </div>
</main>
@endsection
