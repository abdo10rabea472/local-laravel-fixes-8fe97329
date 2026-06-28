@extends('layouts.front')

@section('content')
{{-- ============ HERO ============ --}}
<section class="relative overflow-hidden bg-gradient-to-br from-slate-950 via-violet-950 to-indigo-950 text-white">
    <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 15% 20%, #a78bfa 0, transparent 35%), radial-gradient(circle at 85% 70%, #6366f1 0, transparent 40%), radial-gradient(circle at 50% 100%, #ec4899 0, transparent 45%);"></div>
    <div class="absolute inset-0 opacity-[0.07]" style="background-image: linear-gradient(rgba(255,255,255,.4) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.4) 1px, transparent 1px); background-size: 56px 56px;"></div>

    <div class="relative w-full px-6 lg:px-16 py-24 lg:py-32">
        <div class="max-w-5xl mx-auto text-center">
            <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur text-sm font-medium border border-white/20 mb-6">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <i class="fas fa-book-open"></i> مدونة المعرفة
            </span>
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-black mb-6 tracking-tight leading-[1.1]">
                أحدث المقالات
                <span class="bg-gradient-to-r from-violet-300 via-fuchsia-300 to-indigo-300 bg-clip-text text-transparent">والتجارب</span>
            </h1>
            <p class="text-lg md:text-xl text-violet-100/90 max-w-2xl mx-auto leading-relaxed">
                اقرأ، تعلم، وشارك أفكارك في مجتمع يهتم بالمعرفة والإبداع.
            </p>

            <form method="GET" class="mt-10 max-w-2xl mx-auto flex bg-white/10 backdrop-blur-xl rounded-2xl p-2 border border-white/20 shadow-2xl shadow-violet-900/40">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="ابحث عن مقال، كلمة، موضوع..."
                       class="flex-1 bg-transparent px-5 py-3 placeholder-violet-200/70 text-white focus:outline-none text-base">
                <button class="px-6 md:px-8 py-3 bg-white text-violet-700 font-bold rounded-xl hover:bg-violet-50 transition-all hover:shadow-lg">
                    <i class="fas fa-search"></i> <span class="hidden sm:inline">بحث</span>
                </button>
            </form>

            @if($categories->count())
            <div class="mt-8 flex flex-wrap items-center justify-center gap-2">
                <a href="{{ route('blog.index') }}" class="px-4 py-1.5 rounded-full text-xs font-bold transition border {{ !request('category') ? 'bg-white text-violet-700 border-white' : 'bg-white/5 text-white/90 border-white/15 hover:bg-white/10' }}">جميع التصنيفات</a>
                @foreach($categories->take(8) as $cat)
                <a href="{{ route('blog.index', ['category' => $cat->slug]) }}" class="px-4 py-1.5 rounded-full text-xs font-bold transition border {{ request('category')===$cat->slug ? 'bg-white text-violet-700 border-white' : 'bg-white/5 text-white/90 border-white/15 hover:bg-white/10' }}">{{ $cat->name }}</a>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <div class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-b from-transparent to-slate-50"></div>
</section>

{{-- ============ FEATURED ============ --}}
@if($featured && !request('q') && !request('category'))
<section class="bg-slate-50 pb-12 -mt-8 relative">
    <div class="w-full px-6 lg:px-16">
        <a href="{{ route('blog.show', $featured->slug) }}" class="group block relative overflow-hidden rounded-[2rem] bg-white shadow-xl shadow-violet-900/5 hover:shadow-2xl hover:shadow-violet-900/15 transition-all duration-500">
            <div class="grid lg:grid-cols-5 items-stretch">
                <div class="lg:col-span-3 relative aspect-[16/10] lg:aspect-auto bg-gradient-to-br from-slate-100 to-violet-50 overflow-hidden">
                    <img src="{{ $featured->image_url }}" alt="{{ $featured->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                    <span class="absolute top-5 right-5 inline-flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-amber-400 to-orange-500 text-white text-xs font-black rounded-full shadow-lg">
                        <i class="fas fa-star"></i> مقال مميز
                    </span>
                </div>
                <div class="lg:col-span-2 p-8 lg:p-12 flex flex-col justify-center">
                    @if($featured->category)<span class="inline-block w-fit px-3 py-1 bg-violet-100 text-violet-700 text-xs font-bold rounded-full mb-4 uppercase tracking-wide">{{ $featured->category->name }}</span>@endif
                    <h2 class="text-3xl lg:text-4xl font-black text-slate-900 leading-tight mb-4 group-hover:text-violet-700 transition">{{ $featured->title }}</h2>
                    <p class="text-slate-600 line-clamp-3 mb-6 leading-relaxed">{{ $featured->excerpt }}</p>
                    <div class="flex items-center gap-5 text-sm text-slate-500 pb-6 mb-6 border-b border-slate-100">
                        <span class="inline-flex items-center gap-1.5"><i class="far fa-calendar text-violet-500"></i> {{ $featured->published_at?->format('d M Y') }}</span>
                        <span class="inline-flex items-center gap-1.5"><i class="far fa-eye text-violet-500"></i> {{ $featured->views }}</span>
                    </div>
                    <span class="inline-flex items-center gap-2 text-violet-700 font-bold group-hover:gap-3 transition-all">
                        اقرأ المقال كاملاً <i class="fas fa-arrow-left text-sm"></i>
                    </span>
                </div>
            </div>
        </a>
    </div>
</section>
@endif

{{-- ============ GRID + SIDEBAR ============ --}}
<section class="py-16 bg-slate-50">
    <div class="w-full px-6 lg:px-16 grid lg:grid-cols-[1fr_320px] xl:grid-cols-[1fr_360px] gap-10">
        {{-- Main --}}
        <div>
            <div class="flex items-end justify-between mb-8 pb-5 border-b border-slate-200">
                <div>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">
                        {{ request('q') ? 'نتائج البحث' : (request('category') ? 'تصنيف المقالات' : 'أحدث المقالات') }}
                    </h2>
                    @if(request('q'))<p class="text-sm text-slate-500 mt-1">عن: <span class="font-semibold text-violet-700">"{{ request('q') }}"</span></p>@endif
                </div>
                <span class="text-sm font-semibold text-slate-500 bg-white px-3 py-1.5 rounded-full border border-slate-200">{{ $posts->total() }} مقال</span>
            </div>

            @if($posts->isEmpty())
                <div class="bg-white rounded-3xl p-16 text-center shadow-sm border border-slate-100">
                    <div class="w-20 h-20 mx-auto mb-5 rounded-full bg-violet-50 flex items-center justify-center">
                        <i class="fas fa-newspaper text-3xl text-violet-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">لا توجد مقالات</h3>
                    <p class="text-slate-500 mb-6">لم نعثر على نتائج تطابق بحثك. جرب كلمات أخرى.</p>
                    <a href="{{ route('blog.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-violet-600 text-white font-bold rounded-xl hover:bg-violet-700 transition">
                        <i class="fas fa-redo"></i> عرض جميع المقالات
                    </a>
                </div>
            @else
                <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-7">
                    @foreach($posts as $post)
                    <article class="group bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl hover:shadow-violet-900/10 transition-all duration-500 border border-slate-100 hover:border-violet-200 hover:-translate-y-1 flex flex-col">
                        <a href="{{ route('blog.show', $post->slug) }}" class="block aspect-[16/10] bg-gradient-to-br from-slate-100 to-violet-50 overflow-hidden relative">
                            <img src="{{ $post->image_url }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/40 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition"></div>
                            @if($post->category)
                                <span class="absolute top-4 right-4 px-3 py-1.5 bg-white/95 backdrop-blur text-violet-700 text-xs font-black rounded-full shadow-md uppercase tracking-wide">{{ $post->category->name }}</span>
                            @endif
                        </a>
                        <div class="p-6 flex flex-col flex-1">
                            <h3 class="text-lg font-extrabold text-slate-900 mb-3 line-clamp-2 leading-snug group-hover:text-violet-700 transition">
                                <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                            </h3>
                            <p class="text-sm text-slate-600 line-clamp-3 mb-5 flex-1 leading-relaxed">{{ $post->excerpt }}</p>
                            <div class="flex items-center justify-between pt-4 border-t border-slate-100 text-xs font-semibold text-slate-500">
                                <span class="inline-flex items-center gap-1.5"><i class="far fa-clock text-violet-500"></i> {{ $post->published_at?->diffForHumans() }}</span>
                                <span class="inline-flex items-center gap-1.5"><i class="far fa-eye text-violet-500"></i> {{ $post->views }}</span>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>

                <div class="mt-12 flex justify-center">{{ $posts->links() }}</div>
            @endif
        </div>

        {{-- Sidebar --}}
        <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
            @if($categories->count())
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                <h3 class="font-extrabold text-slate-900 mb-5 flex items-center gap-2 text-lg">
                    <span class="w-8 h-8 rounded-lg bg-violet-100 text-violet-600 flex items-center justify-center"><i class="fas fa-tags text-sm"></i></span>
                    التصنيفات
                </h3>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('blog.index') }}" class="flex items-center justify-between px-4 py-2.5 rounded-xl transition {{ !request('category') ? 'bg-violet-50 text-violet-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                            <span>جميع المقالات</span>
                            <i class="fas fa-chevron-left text-xs opacity-50"></i>
                        </a>
                    </li>
                    @foreach($categories as $cat)
                    <li>
                        <a href="{{ route('blog.index', ['category' => $cat->slug]) }}" class="flex items-center justify-between px-4 py-2.5 rounded-xl transition {{ request('category')===$cat->slug ? 'bg-violet-50 text-violet-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                            <span>{{ $cat->name }}</span>
                            <i class="fas fa-chevron-left text-xs opacity-50"></i>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if($popular->count())
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                <h3 class="font-extrabold text-slate-900 mb-5 flex items-center gap-2 text-lg">
                    <span class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center"><i class="fas fa-fire text-sm"></i></span>
                    الأكثر قراءة
                </h3>
                <ul class="space-y-4">
                    @foreach($popular as $i => $p)
                    <li>
                        <a href="{{ route('blog.show', $p->slug) }}" class="flex gap-3 group">
                            <div class="w-16 h-16 flex-shrink-0 rounded-xl overflow-hidden bg-slate-100 relative">
                                <img src="{{ $p->image_url }}" alt="{{ $p->title }}" class="w-full h-full object-cover">
                                <span class="absolute -top-1 -right-1 w-6 h-6 rounded-full bg-violet-600 text-white text-xs font-black flex items-center justify-center shadow">{{ $i+1 }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-slate-800 line-clamp-2 group-hover:text-violet-700 transition leading-snug">{{ $p->title }}</h4>
                                <span class="text-xs text-slate-400 mt-1 inline-flex items-center gap-1"><i class="far fa-eye"></i> {{ $p->views }} مشاهدة</span>
                            </div>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="relative overflow-hidden bg-gradient-to-br from-violet-600 via-violet-700 to-indigo-700 rounded-3xl p-7 text-white shadow-xl shadow-violet-900/20">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-12 -left-8 w-40 h-40 bg-pink-400/20 rounded-full blur-3xl"></div>
                <div class="relative">
                    <div class="w-12 h-12 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center mb-4">
                        <i class="fas fa-envelope-open-text text-xl"></i>
                    </div>
                    <h3 class="font-extrabold mb-2 text-xl">اشترك بالنشرة</h3>
                    <p class="text-sm text-violet-100 mb-5 leading-relaxed">احصل على أحدث المقالات في بريدك أسبوعياً.</p>
                    <form action="{{ route('newsletter.subscribe') ?? '#' }}" method="POST" class="space-y-2">
                        @csrf
                        <input type="email" name="email" required placeholder="بريدك الإلكتروني" class="w-full px-4 py-3 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-white text-sm">
                        <button class="w-full bg-white text-violet-700 font-black py-3 rounded-xl hover:bg-violet-50 transition shadow-lg">اشترك الآن</button>
                    </form>
                </div>
            </div>
        </aside>
    </div>
</section>
@endsection
