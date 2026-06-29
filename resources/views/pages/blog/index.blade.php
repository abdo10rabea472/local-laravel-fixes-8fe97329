@extends('layouts.front')

@section('content')
<div class="min-h-screen bg-[#F8FAFC] text-slate-800 font-sans selection:bg-indigo-500 selection:text-white pb-24">

    {{-- ============ HERO ============ --}}
    <section class="relative pt-14 pb-10 overflow-hidden">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-full overflow-hidden -z-10 pointer-events-none">
            <div class="absolute -top-40 right-20 w-96 h-96 bg-indigo-400/20 rounded-full mix-blend-multiply filter blur-3xl opacity-70"></div>
            <div class="absolute top-0 left-20 w-96 h-96 bg-purple-400/20 rounded-full mix-blend-multiply filter blur-3xl opacity-70"></div>
        </div>
        <div class="max-w-7xl mx-auto px-6 lg:px-8 text-center">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 text-indigo-600 text-xs font-semibold tracking-wide uppercase ring-1 ring-inset ring-indigo-500/20 mb-5">
                <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span> {{ __('app.blog_eyebrow') }}
            </span>
            <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight mb-4 leading-tight">
                {{ __('app.blog_hero_title_1') }} <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">{{ __('app.blog_hero_title_2') }}</span>
            </h1>
            <p class="text-slate-600 max-w-2xl mx-auto">{{ __('app.blog_hero_subtitle') }}</p>
        </div>
    </section>

    {{-- ============ MAIN GRID ============ --}}
    <div class="max-w-7xl mx-auto px-6 lg:px-8 grid lg:grid-cols-3 gap-10">

        {{-- ============ ARTICLES LIST ============ --}}
        <main class="lg:col-span-2 space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-slate-900">
                    {{ request('q') ? __('app.blog_search_results') : (request('category') ? __('app.blog_category') : (request('tag') ? __('app.blog_tag').': '.request('tag') : __('app.blog_latest'))) }}
                </h2>
                @if(request('q') || request('category') || request('tag'))
                    <a href="{{ route('blog.index') }}" class="text-xs font-medium text-indigo-600 hover:underline">{{ __('app.shared_clear_filters') }}</a>
                @endif
            </div>

            @if($posts->isEmpty())
                <div class="p-12 bg-white rounded-2xl ring-1 ring-slate-100 text-center">
                    <i class="fas fa-search text-3xl text-slate-300 mb-3"></i>
                    <p class="text-slate-500">{{ __('app.blog_empty') }}</p>
                </div>
            @else
                @foreach($posts as $post)
                <article class="group flex gap-4 sm:gap-6 bg-white rounded-2xl p-4 sm:p-5 ring-1 ring-slate-100 shadow-[0_4px_20px_-10px_rgba(0,0,0,0.05)] transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_15px_30px_-10px_rgba(0,0,0,0.08)]">
                    <a href="{{ route('blog.show', $post->slug) }}" class="shrink-0 block w-28 h-28 sm:w-44 sm:h-32 rounded-xl overflow-hidden bg-slate-100">
                        <img src="{{ $post->image_url }}" alt="{{ $post->title }}"
                             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    </a>
                    <div class="flex-1 min-w-0 flex flex-col">
                        @if($post->category)
                            <a href="{{ route('blog.index', ['category' => $post->category->slug]) }}"
                               class="text-[11px] font-bold uppercase tracking-wider text-indigo-600 hover:text-indigo-800 mb-1.5">
                                {{ $post->category->name }}
                            </a>
                        @endif
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 leading-snug mb-1.5 line-clamp-2 group-hover:text-indigo-600 transition-colors">
                            <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                        </h3>
                        <p class="hidden sm:block text-sm text-slate-600 leading-relaxed line-clamp-2 mb-3">{{ $post->excerpt }}</p>
                        <div class="mt-auto flex items-center flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500 font-medium">
                            <span class="flex items-center gap-1.5"><i class="far fa-calendar"></i> {{ $post->published_at?->format('M d, Y') }}</span>
                            <span class="flex items-center gap-1.5"><i class="far fa-eye"></i> {{ $post->views }} {{ __('app.blog_views') }}</span>
                            <span class="flex items-center gap-1.5"><i class="far fa-comment"></i> {{ $post->comments_count ?? 0 }} {{ __('app.blog_comments') }}</span>
                        </div>
                    </div>
                </article>
                @endforeach

                <div class="pt-6">{{ $posts->links() }}</div>
            @endif
        </main>

        {{-- ============ SIDEBAR ============ --}}
        <aside class="lg:col-span-1 space-y-6 lg:sticky lg:top-24 self-start">

            {{-- Search --}}
            <div class="p-5 bg-white rounded-2xl ring-1 ring-slate-100 shadow-sm">
                <h4 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2"><i class="fas fa-search text-indigo-600"></i> {{ __('app.blog_search_title') }}</h4>
                <form method="GET" class="relative">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('app.blog_search_placeholder') }}"
                           class="w-full h-11 ps-4 pe-10 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                    <button class="absolute inset-y-0 end-0 px-3 text-slate-400 hover:text-indigo-600"><i class="fas fa-arrow-right"></i></button>
                </form>
            </div>

            {{-- Featured --}}
            @if($featured)
            <div class="p-5 bg-white rounded-2xl ring-1 ring-slate-100 shadow-sm">
                <h4 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2"><i class="fas fa-star text-amber-500"></i> {{ __('app.blog_featured') }}</h4>
                <a href="{{ route('blog.show', $featured->slug) }}" class="group block">
                    <div class="aspect-[16/10] rounded-xl overflow-hidden mb-3 bg-slate-100">
                        <img src="{{ $featured->image_url }}" alt="{{ $featured->title }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    </div>
                    @if($featured->category)
                        <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600">{{ $featured->category->name }}</span>
                    @endif
                    <h5 class="font-bold text-slate-900 leading-snug mt-1 mb-2 group-hover:text-indigo-600 transition-colors line-clamp-2">{{ $featured->title }}</h5>
                    <p class="text-xs text-slate-500 line-clamp-2">{{ $featured->excerpt }}</p>
                </a>
            </div>
            @endif

            {{-- Popular --}}
            @if($popular && $popular->count())
            <div class="p-5 bg-white rounded-2xl ring-1 ring-slate-100 shadow-sm">
                <h4 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2"><i class="fas fa-fire text-rose-500"></i> {{ __('app.blog_most_viewed') }}</h4>
                <ul class="space-y-4">
                    @foreach($popular as $i => $p)
                    <li>
                        <a href="{{ route('blog.show', $p->slug) }}" class="group flex gap-3 items-start">
                            <span class="shrink-0 w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-500 text-white text-xs font-bold flex items-center justify-center">{{ $i + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                <h6 class="text-sm font-semibold text-slate-800 leading-snug line-clamp-2 group-hover:text-indigo-600 transition-colors">{{ $p->title }}</h6>
                                <div class="flex items-center gap-3 mt-1 text-[11px] text-slate-400 font-medium">
                                    <span><i class="far fa-calendar"></i> {{ $p->published_at?->format('M d, Y') }}</span>
                                    <span><i class="far fa-eye"></i> {{ $p->views }}</span>
                                </div>
                            </div>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Categories --}}
            @if($categories->count())
            <div class="p-5 bg-white rounded-2xl ring-1 ring-slate-100 shadow-sm">
                <h4 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2"><i class="fas fa-folder text-indigo-600"></i> {{ __('app.blog_categories') }}</h4>
                <div class="space-y-1">
                    <a href="{{ route('blog.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ !request('category') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50' }}">
                        <span>{{ __('app.blog_all') }}</span>
                    </a>
                    @foreach($categories as $cat)
                    <a href="{{ route('blog.index', ['category' => $cat->slug]) }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request('category')===$cat->slug ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50' }}">
                        <span>{{ $cat->name }}</span>
                        <i class="fas fa-chevron-left text-[10px] opacity-50"></i>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Tags --}}
            @if($tags && $tags->count())
            <div class="p-5 bg-white rounded-2xl ring-1 ring-slate-100 shadow-sm">
                <h4 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2"><i class="fas fa-tags text-purple-600"></i> {{ __('app.blog_tags') }}</h4>
                <div class="flex flex-wrap gap-2">
                    @foreach($tags as $tag)
                    <a href="{{ route('blog.index', ['tag' => $tag]) }}"
                       class="px-3 py-1.5 rounded-full text-xs font-medium transition-colors {{ request('tag')===$tag ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-indigo-50 hover:text-indigo-700' }}">
                        #{{ $tag }}
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

        </aside>
    </div>
</div>
@endsection
