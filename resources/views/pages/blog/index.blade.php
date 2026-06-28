@extends('layouts.front')

@section('content')
@php
    $featuredPost = $featured ?? null;
    $hasFilters = request('q') || request('category');
@endphp

<div class="bg-stone-50 text-stone-900 [font-feature-settings:'ss01','ss02']">

    {{-- ============ EDITORIAL MASTHEAD ============ --}}
    <section class="relative border-b border-stone-200 bg-stone-50">
        <div class="w-full max-w-[1850px] mx-auto px-6 lg:px-16 pt-14 pb-10">
            <div class="flex items-center justify-between text-[11px] uppercase tracking-[0.25em] text-stone-500 mb-10">
                <span>{{ now()->format('l, d F Y') }}</span>
                <span class="hidden sm:inline">عدد المقالات · {{ $posts->total() }}</span>
                <span>المجلة · العدد الرقمي</span>
            </div>

            <div class="grid lg:grid-cols-12 gap-10 items-end">
                <div class="lg:col-span-8">
                    <span class="inline-block text-[11px] tracking-[0.35em] uppercase text-amber-700 font-bold mb-5">— Editorial Journal</span>
                    <h1 class="font-serif text-5xl md:text-7xl lg:text-[6rem] leading-[0.95] tracking-tight text-stone-900">
                        قصص، أفكار،
                        <em class="italic font-light text-amber-700">ورؤى</em>
                        تستحق القراءة.
                    </h1>
                </div>

                <div class="lg:col-span-4 lg:border-r lg:border-stone-300 lg:pr-8">
                    <p class="text-base leading-loose text-stone-600 mb-6">
                        مساحة تحريرية للباحثين والمهتمين بالمعرفة. مقالات معمّقة، تجارب من الميدان، ومقاربات نقدية لما يحدث الآن.
                    </p>
                    <form method="GET" class="flex items-center border-b-2 border-stone-900 pb-2 group">
                        <i class="fas fa-search text-stone-400 ml-2 group-focus-within:text-stone-900 transition"></i>
                        <input type="search" name="q" value="{{ request('q') }}" placeholder="ابحث في الأرشيف…"
                               class="flex-1 bg-transparent px-2 py-1 focus:outline-none placeholder-stone-400 text-stone-900">
                        <button class="text-xs uppercase tracking-widest font-bold text-stone-900 hover:text-amber-700 transition">بحث</button>
                    </form>
                </div>
            </div>

            @if($categories->count())
            <div class="mt-12 pt-6 border-t border-stone-200 flex flex-wrap items-center gap-x-6 gap-y-3 text-sm">
                <span class="text-[11px] uppercase tracking-[0.3em] text-stone-400 font-bold">الأقسام</span>
                <a href="{{ route('blog.index') }}"
                   class="relative pb-1 transition {{ !request('category') ? 'text-amber-700 font-bold after:absolute after:bottom-0 after:right-0 after:w-full after:h-px after:bg-amber-700' : 'text-stone-600 hover:text-stone-900' }}">
                    الكل
                </a>
                @foreach($categories as $cat)
                    <a href="{{ route('blog.index', ['category' => $cat->slug]) }}"
                       class="relative pb-1 transition {{ request('category')===$cat->slug ? 'text-amber-700 font-bold after:absolute after:bottom-0 after:right-0 after:w-full after:h-px after:bg-amber-700' : 'text-stone-600 hover:text-stone-900' }}">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>
            @endif
        </div>
    </section>

    {{-- ============ HERO / FEATURED ============ --}}
    @if($featuredPost && !$hasFilters)
    <section class="bg-stone-900 text-stone-50">
        <div class="w-full max-w-[1850px] mx-auto px-6 lg:px-16 py-16 lg:py-24">
            <a href="{{ route('blog.show', $featuredPost->slug) }}" class="grid lg:grid-cols-12 gap-10 lg:gap-16 items-center group">
                <div class="lg:col-span-7 relative overflow-hidden">
                    <div class="aspect-[4/3] lg:aspect-[5/4] overflow-hidden bg-stone-800">
                        <img src="{{ $featuredPost->image_url }}" alt="{{ $featuredPost->title }}"
                             class="w-full h-full object-cover grayscale-[20%] group-hover:grayscale-0 group-hover:scale-105 transition-all duration-[1200ms]">
                    </div>
                    <div class="absolute top-5 right-5 bg-amber-400 text-stone-900 px-4 py-2 text-[11px] uppercase tracking-[0.25em] font-black">
                        ★ مقال الغلاف
                    </div>
                </div>
                <div class="lg:col-span-5">
                    @if($featuredPost->category)
                        <span class="inline-block text-[11px] uppercase tracking-[0.35em] text-amber-400 font-bold mb-5">
                            {{ $featuredPost->category->name }}
                        </span>
                    @endif
                    <h2 class="font-serif text-4xl md:text-5xl lg:text-6xl leading-[1.05] mb-6 group-hover:text-amber-300 transition">
                        {{ $featuredPost->title }}
                    </h2>
                    <p class="text-lg leading-loose text-stone-300 mb-8 line-clamp-4 max-w-xl">
                        {{ $featuredPost->excerpt }}
                    </p>
                    <div class="flex items-center gap-6 text-xs uppercase tracking-widest text-stone-400 pb-6 mb-6 border-b border-stone-700">
                        <span>{{ $featuredPost->published_at?->format('d F Y') }}</span>
                        <span>·</span>
                        <span>{{ $featuredPost->views }} مشاهدة</span>
                    </div>
                    <span class="inline-flex items-center gap-3 text-sm uppercase tracking-[0.25em] font-bold text-amber-400 group-hover:gap-5 transition-all">
                        اقرأ المقال كاملاً
                        <span class="w-10 h-px bg-amber-400"></span>
                        <i class="fas fa-arrow-left"></i>
                    </span>
                </div>
            </a>
        </div>
    </section>
    @endif

    {{-- ============ LATEST ARTICLES ============ --}}
    <section class="bg-stone-50 py-20 lg:py-28">
        <div class="w-full max-w-[1850px] mx-auto px-6 lg:px-16">

            <div class="flex items-end justify-between border-b border-stone-300 pb-6 mb-14">
                <div>
                    <span class="block text-[11px] uppercase tracking-[0.35em] text-amber-700 font-bold mb-3">— Latest</span>
                    <h2 class="font-serif text-4xl md:text-5xl text-stone-900">
                        {{ request('q') ? 'نتائج البحث' : (request('category') ? 'في هذا القسم' : 'أحدث الإصدارات') }}
                    </h2>
                    @if(request('q'))
                        <p class="text-stone-500 mt-3">عن: <em class="text-stone-900 font-semibold">"{{ request('q') }}"</em></p>
                    @endif
                </div>
                <span class="hidden md:block text-stone-400 font-serif text-3xl italic">{{ str_pad($posts->total(), 2, '0', STR_PAD_LEFT) }}</span>
            </div>

            @if($posts->isEmpty())
                <div class="py-24 text-center max-w-md mx-auto">
                    <div class="font-serif text-7xl text-stone-300 mb-6">∅</div>
                    <h3 class="font-serif text-3xl text-stone-900 mb-3">لا يوجد ما يطابق بحثك</h3>
                    <p class="text-stone-500 mb-8">جرّب كلمات أخرى أو تصفّح كامل الأرشيف.</p>
                    <a href="{{ route('blog.index') }}"
                       class="inline-flex items-center gap-3 text-sm uppercase tracking-[0.25em] font-bold text-stone-900 border-b-2 border-stone-900 pb-1 hover:text-amber-700 hover:border-amber-700 transition">
                        عرض جميع المقالات <i class="fas fa-arrow-left"></i>
                    </a>
                </div>
            @else
                <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-x-10 gap-y-16">
                    @foreach($posts as $index => $post)
                    <article class="group flex flex-col">
                        <a href="{{ route('blog.show', $post->slug) }}" class="block relative overflow-hidden mb-6 bg-stone-200">
                            <div class="aspect-[4/3] overflow-hidden">
                                <img src="{{ $post->image_url }}" alt="{{ $post->title }}"
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-[900ms]">
                            </div>
                            <span class="absolute top-4 right-4 bg-stone-50 text-stone-900 px-3 py-1 text-[10px] uppercase tracking-[0.25em] font-bold">
                                №{{ str_pad(($posts->firstItem() ?? 1) + $index, 2, '0', STR_PAD_LEFT) }}
                            </span>
                        </a>

                        @if($post->category)
                            <a href="{{ route('blog.index', ['category' => $post->category->slug]) }}"
                               class="text-[11px] uppercase tracking-[0.3em] text-amber-700 font-bold mb-3 hover:underline">
                                {{ $post->category->name }}
                            </a>
                        @endif

                        <h3 class="font-serif text-2xl lg:text-[1.75rem] leading-snug text-stone-900 mb-3 group-hover:text-amber-700 transition">
                            <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                        </h3>

                        <p class="text-stone-600 leading-loose line-clamp-3 mb-6 flex-1">{{ $post->excerpt }}</p>

                        <div class="flex items-center justify-between pt-5 border-t border-stone-200 text-xs text-stone-500">
                            <span class="uppercase tracking-widest">{{ $post->published_at?->format('d M Y') }}</span>
                            <span class="inline-flex items-center gap-3">
                                <span><i class="far fa-eye"></i> {{ $post->views }}</span>
                                <span class="text-amber-700 group-hover:translate-x-[-4px] transition-transform inline-block"><i class="fas fa-arrow-left"></i></span>
                            </span>
                        </div>
                    </article>
                    @endforeach
                </div>

                <div class="mt-20 pt-10 border-t border-stone-200 flex justify-center">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </section>

    {{-- ============ POPULAR STRIP ============ --}}
    @if(($popular ?? collect())->count())
    <section class="bg-stone-900 text-stone-50 py-20">
        <div class="w-full max-w-[1850px] mx-auto px-6 lg:px-16">
            <div class="flex items-end justify-between border-b border-stone-700 pb-6 mb-12">
                <div>
                    <span class="block text-[11px] uppercase tracking-[0.35em] text-amber-400 font-bold mb-3">— Most Read</span>
                    <h2 class="font-serif text-4xl md:text-5xl">الأكثر قراءة</h2>
                </div>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                @foreach($popular->take(4) as $i => $p)
                <a href="{{ route('blog.show', $p->slug) }}" class="group flex gap-5 items-start">
                    <span class="font-serif text-5xl text-amber-400 leading-none shrink-0">{{ str_pad($i+1, 2, '0', STR_PAD_LEFT) }}</span>
                    <div class="flex-1 min-w-0 border-r border-stone-700 pr-5">
                        <h4 class="font-serif text-xl leading-snug mb-2 group-hover:text-amber-300 transition line-clamp-3">{{ $p->title }}</h4>
                        <span class="text-[11px] uppercase tracking-widest text-stone-400"><i class="far fa-eye"></i> {{ $p->views }} مشاهدة</span>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ============ NEWSLETTER ============ --}}
    <section class="bg-amber-50 border-y border-amber-200">
        <div class="w-full max-w-[1850px] mx-auto px-6 lg:px-16 py-20 grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <span class="block text-[11px] uppercase tracking-[0.35em] text-amber-700 font-bold mb-4">— Newsletter</span>
                <h2 class="font-serif text-4xl md:text-5xl text-stone-900 leading-tight mb-4">
                    أحدث المقالات،
                    <em class="italic font-light text-amber-700">في بريدك.</em>
                </h2>
                <p class="text-stone-600 leading-loose max-w-lg">رسالة واحدة كل أسبوع. مقالات منتقاة، وقراءات مقترحة. بدون إزعاج.</p>
            </div>
            <form action="{{ route('newsletter.subscribe') ?? '#' }}" method="POST" class="flex items-center border-b-2 border-stone-900 pb-3">
                @csrf
                <input type="email" name="email" required placeholder="بريدك الإلكتروني"
                       class="flex-1 bg-transparent text-lg py-2 focus:outline-none placeholder-stone-400 text-stone-900">
                <button class="text-sm uppercase tracking-[0.25em] font-bold text-stone-900 hover:text-amber-700 transition px-4">
                    اشترك ←
                </button>
            </form>
        </div>
    </section>

</div>
@endsection
