@extends('layouts.front')

@section('content')
@php
    // Selecting the most viewed post to be the featured cover post
    $featuredPost = ($popular ?? collect())->first();
    $hasFilters = request('q') || request('category');
@endphp

<div class="min-h-screen bg-[#F8FAFC] text-slate-800 font-sans selection:bg-indigo-500 selection:text-white pb-24">

    {{-- ============ MODERN HERO & SEARCH ============ --}}
    <section class="relative pt-16 pb-12 overflow-hidden">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-full overflow-hidden -z-10 pointer-events-none">
            <div class="absolute -top-40 right-20 w-96 h-96 bg-indigo-400/20 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob"></div>
            <div class="absolute top-0 left-20 w-96 h-96 bg-purple-400/20 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-2000"></div>
        </div>

        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 text-indigo-600 text-xs font-semibold tracking-wide uppercase ring-1 ring-inset ring-indigo-500/20 mb-6 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                    Digital Journal
                </span>
                <h1 class="text-5xl md:text-6xl font-extrabold text-slate-900 tracking-tight mb-6 leading-tight">
                    Ideas that shape the <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">Future</span>
                </h1>
                <p class="text-lg text-slate-600 leading-relaxed mb-8">
                    A modern space for articles, insights, and field experiences. Explore our latest carefully crafted releases to enrich your knowledge.
                </p>

                <form method="GET" class="relative max-w-xl mx-auto group">
                    <div class="absolute inset-y-0 start-0 flex items-center ps-5 pointer-events-none">
                        <i class="fas fa-search text-slate-400 group-focus-within:text-indigo-500 transition-colors duration-300"></i>
                    </div>
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search articles, authors, or topics..."
                           class="block w-full p-4 ps-12 text-slate-900 bg-white/80 backdrop-blur-md border border-slate-200 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white transition-all duration-300 outline-none text-base">
                    <button type="submit" class="absolute inset-y-2 end-2 px-6 bg-slate-900 hover:bg-indigo-600 text-white rounded-xl text-sm font-medium transition-colors duration-300 shadow-sm">
                        Search
                    </button>
                </form>
            </div>

        </div>
    </section>

    {{-- ============ IMMERSIVE FEATURED ARTICLE (MOST VIEWED) ============ --}}
    @if($featuredPost && !$hasFilters)
    <section class="max-w-7xl mx-auto px-6 lg:px-8 mb-20">
        <a href="{{ route('blog.show', $featuredPost->slug) }}" class="group block relative rounded-[2rem] overflow-hidden bg-white shadow-[0_20px_40px_-15px_rgba(0,0,0,0.03)] ring-1 ring-slate-100 transition-all duration-500 hover:-translate-y-2 hover:shadow-[0_30px_60px_-15px_rgba(0,0,0,0.08)]">
            <div class="grid lg:grid-cols-12 items-center">
                
                <div class="p-10 lg:p-14 flex flex-col justify-center relative z-10 lg:col-span-7 order-2 lg:order-1">
                    @if($featuredPost->category)
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-indigo-600 mb-6">
                            <i class="fas fa-fire text-amber-500"></i>
                            Trending
                            <span class="text-slate-300">|</span>
                            {{ $featuredPost->category->name }}
                        </span>
                    @endif
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-900 leading-[1.3] mb-6 group-hover:text-indigo-600 transition-colors duration-300">
                        {{ $featuredPost->title }}
                    </h2>
                    <p class="text-slate-600 text-base leading-relaxed line-clamp-3 mb-8">
                        {{ $featuredPost->excerpt }}
                    </p>
                    <div class="mt-auto flex items-center justify-between text-sm text-slate-500 font-medium">
                        <div class="flex items-center gap-4">
                            <span class="flex items-center gap-2"><i class="far fa-calendar"></i> {{ $featuredPost->published_at?->format('M d, Y') }}</span>
                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                            <span class="flex items-center gap-2 font-semibold text-indigo-600"><i class="far fa-eye"></i> {{ $featuredPost->views }} views</span>
                        </div>
                        <span class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300">
                            <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>
                </div>

                <div class="relative h-[280px] lg:h-[400px] lg:col-span-5 order-1 lg:order-2 overflow-hidden m-4 lg:m-6 rounded-2xl shadow-sm">
                    <img src="{{ $featuredPost->image_url }}" alt="{{ $featuredPost->title }}"
                         class="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/20 to-transparent"></div>
                </div>

            </div>
        </a>
    </section>
    @endif

    {{-- ============ LATEST ARTICLES GRID ============ --}}
    <section class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="flex items-center justify-between mb-10">
            <h3 class="text-2xl font-bold text-slate-900">
                {{ request('q') ? 'Search Results' : (request('category') ? 'Category Articles' : 'Latest Articles') }}
            </h3>
            @if(request('q'))
                <span class="px-4 py-1.5 rounded-full bg-slate-200/50 text-slate-600 text-sm font-medium">
                    For: "{{ request('q') }}"
                </span>
            @endif
        </div>

        @if($posts->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 px-4 bg-white rounded-[2rem] shadow-sm ring-1 ring-slate-100 text-center">
                <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-6 shadow-inner">
                    <i class="fas fa-search text-4xl text-slate-300"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">We couldn't find any articles</h3>
                <p class="text-slate-500 mb-8 max-w-sm">Try searching with different keywords or browse our available categories to find what you're looking for.</p>
                <a href="{{ route('blog.index') }}" class="px-6 py-3 bg-slate-900 text-white rounded-xl text-sm font-medium hover:bg-indigo-600 transition-colors duration-300 shadow-md">
                    Clear Search & Return
                </a>
            </div>
        @else
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($posts as $index => $post)
                <article class="group flex flex-col bg-white rounded-[1.5rem] overflow-hidden shadow-[0_4px_20px_-10px_rgba(0,0,0,0.05)] ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_15px_30px_-10px_rgba(0,0,0,0.1)]">
                    <a href="{{ route('blog.show', $post->slug) }}" class="block relative aspect-[16/10] overflow-hidden">
                        <img src="{{ $post->image_url }}" alt="{{ $post->title }}"
                             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                        @if($post->category)
                            <div class="absolute top-4 start-4 bg-white/90 backdrop-blur-md px-3 py-1.5 rounded-lg text-[11px] font-bold text-slate-800 shadow-sm">
                                {{ $post->category->name }}
                            </div>
                        @endif
                    </a>
                    
                    <div class="p-6 lg:p-8 flex flex-col flex-1">
                        <h3 class="text-xl font-bold text-slate-900 leading-snug mb-3 group-hover:text-indigo-600 transition-colors duration-300">
                            <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                        </h3>
                        <p class="text-slate-600 leading-relaxed line-clamp-3 mb-6 text-sm flex-1">
                            {{ $post->excerpt }}
                        </p>
                        <div class="flex items-center justify-between pt-5 border-t border-slate-100 text-xs font-medium text-slate-500">
                            <span class="flex items-center gap-1.5"><i class="far fa-calendar text-slate-400"></i> {{ $post->published_at?->format('M d, Y') }}</span>
                            <span class="flex items-center gap-1.5 group-hover:text-indigo-600 transition-colors">
                                Read More 
                                <i class="fas fa-arrow-right text-[10px] transform group-hover:translate-x-1 transition-transform"></i>
                            </span>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>

            <div class="mt-16 flex justify-center">
                {{ $posts->links() }}
            </div>
        @endif
    </section>
</div>
@endsection