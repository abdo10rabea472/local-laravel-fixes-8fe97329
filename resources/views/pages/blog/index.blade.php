@extends('layouts.front')

@section('content')
<section class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-violet-900 to-indigo-900 text-white">
    <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, #a78bfa 0, transparent 40%), radial-gradient(circle at 80% 60%, #6366f1 0, transparent 45%);"></div>
    <div class="relative max-w-6xl mx-auto px-4 py-20 text-center">
        <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur text-sm font-medium border border-white/15 mb-5">
            <i class="fas fa-book-open"></i> مدونة المعرفة
        </span>
        <h1 class="text-4xl md:text-5xl font-extrabold mb-4 tracking-tight">أحدث المقالات والتجارب</h1>
        <p class="text-lg text-violet-100 max-w-2xl mx-auto">اقرأ، تعلم، وشارك أفكارك في مجتمع يهتم بالمعرفة والإبداع.</p>

        <form method="GET" class="mt-8 max-w-xl mx-auto flex bg-white/10 backdrop-blur rounded-2xl p-2 border border-white/20">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="ابحث عن مقال، كلمة، موضوع..."
                   class="flex-1 bg-transparent px-4 py-2 placeholder-violet-200 text-white focus:outline-none">
            <button class="px-6 py-2 bg-white text-violet-700 font-bold rounded-xl hover:bg-violet-50 transition">
                <i class="fas fa-search"></i> بحث
            </button>
        </form>
    </div>
</section>

@if($featured && !request('q') && !request('category'))
<section class="py-12 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <a href="{{ route('blog.show', $featured->slug) }}" class="group grid md:grid-cols-2 gap-8 items-center bg-gradient-to-br from-slate-50 to-violet-50 rounded-3xl p-6 md:p-10 hover:shadow-xl transition-all">
            <div class="aspect-video rounded-2xl overflow-hidden bg-slate-200">
                @if($featured->image)
                    <img src="{{ asset('storage/'.$featured->image) }}" alt="{{ $featured->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                @else
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-violet-200 to-indigo-200"><i class="fas fa-newspaper text-6xl text-violet-400"></i></div>
                @endif
            </div>
            <div>
                <span class="inline-block px-3 py-1 bg-violet-600 text-white text-xs font-bold rounded-full mb-3">⭐ مقال مميز</span>
                @if($featured->category)<span class="inline-block px-3 py-1 bg-white text-violet-700 text-xs font-bold rounded-full mb-3 mr-2 border border-violet-200">{{ $featured->category->name }}</span>@endif
                <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900 leading-snug mb-3 group-hover:text-violet-700 transition">{{ $featured->title }}</h2>
                <p class="text-slate-600 line-clamp-3 mb-4">{{ $featured->excerpt }}</p>
                <div class="flex items-center gap-4 text-sm text-slate-500">
                    <span><i class="far fa-calendar"></i> {{ $featured->published_at?->format('Y-m-d') }}</span>
                    <span><i class="far fa-eye"></i> {{ $featured->views }} مشاهدة</span>
                </div>
            </div>
        </a>
    </div>
</section>
@endif

<section class="py-12 bg-slate-50">
    <div class="max-w-6xl mx-auto px-4 grid lg:grid-cols-4 gap-8">
        <div class="lg:col-span-3">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-extrabold text-slate-900">
                    {{ request('q') ? 'نتائج البحث' : (request('category') ? 'تصنيف المقالات' : 'أحدث المقالات') }}
                </h2>
                <span class="text-sm text-slate-500">{{ $posts->total() }} مقال</span>
            </div>

            @if($posts->isEmpty())
                <div class="bg-white rounded-2xl p-12 text-center shadow-sm border border-slate-100">
                    <i class="fas fa-newspaper text-6xl text-slate-300 mb-4"></i>
                    <p class="text-slate-600 text-lg">لا توجد مقالات تطابق بحثك.</p>
                </div>
            @else
                <div class="grid sm:grid-cols-2 gap-6">
                    @foreach($posts as $post)
                    <article class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-slate-100 flex flex-col">
                        <a href="{{ route('blog.show', $post->slug) }}" class="block aspect-video bg-slate-100 overflow-hidden relative">
                            @if($post->image)
                                <img src="{{ asset('storage/'.$post->image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-violet-100 to-indigo-100"><i class="fas fa-newspaper text-5xl text-violet-300"></i></div>
                            @endif
                            @if($post->category)
                                <span class="absolute top-3 right-3 px-3 py-1 bg-white/95 backdrop-blur text-violet-700 text-xs font-bold rounded-full shadow">{{ $post->category->name }}</span>
                            @endif
                        </a>
                        <div class="p-5 flex flex-col flex-1">
                            <h3 class="text-lg font-bold text-slate-900 mb-2 line-clamp-2 group-hover:text-violet-700 transition">
                                <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                            </h3>
                            <p class="text-sm text-slate-600 line-clamp-3 mb-4 flex-1">{{ $post->excerpt }}</p>
                            <div class="flex items-center justify-between pt-3 border-t border-slate-100 text-xs text-slate-500">
                                <span class="inline-flex items-center gap-1.5"><i class="far fa-clock text-violet-500"></i> {{ $post->published_at?->diffForHumans() }}</span>
                                <span class="inline-flex items-center gap-1.5"><i class="far fa-eye text-violet-500"></i> {{ $post->views }}</span>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>
                <div class="mt-10">{{ $posts->links() }}</div>
            @endif
        </div>

        <aside class="lg:col-span-1 space-y-6">
            @if($categories->count())
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2"><i class="fas fa-tags text-violet-600"></i> التصنيفات</h3>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('blog.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-violet-50 hover:text-violet-700 transition {{ !request('category') ? 'bg-violet-50 text-violet-700 font-semibold' : 'text-slate-600' }}">
                            <span>جميع المقالات</span>
                        </a>
                    </li>
                    @foreach($categories as $cat)
                    <li>
                        <a href="{{ route('blog.index', ['category' => $cat->slug]) }}" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-violet-50 hover:text-violet-700 transition {{ request('category')===$cat->slug ? 'bg-violet-50 text-violet-700 font-semibold' : 'text-slate-600' }}">
                            <span>{{ $cat->name }}</span>
                            <i class="fas fa-chevron-left text-xs"></i>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if($popular->count())
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2"><i class="fas fa-fire text-orange-500"></i> الأكثر قراءة</h3>
                <ul class="space-y-4">
                    @foreach($popular as $p)
                    <li>
                        <a href="{{ route('blog.show', $p->slug) }}" class="flex gap-3 group">
                            <div class="w-16 h-16 flex-shrink-0 rounded-lg overflow-hidden bg-slate-100">
                                @if($p->image)
                                    <img src="{{ asset('storage/'.$p->image) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-violet-50"><i class="fas fa-newspaper text-violet-300"></i></div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-semibold text-slate-800 line-clamp-2 group-hover:text-violet-700 transition">{{ $p->title }}</h4>
                                <span class="text-xs text-slate-400"><i class="far fa-eye"></i> {{ $p->views }}</span>
                            </div>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="bg-gradient-to-br from-violet-600 to-indigo-700 rounded-2xl p-6 text-white shadow-lg">
                <h3 class="font-bold mb-2 text-lg"><i class="fas fa-envelope-open-text"></i> اشترك بالنشرة</h3>
                <p class="text-sm text-violet-100 mb-4">احصل على أحدث المقالات في بريدك أسبوعياً.</p>
                <form action="{{ route('newsletter.subscribe') ?? '#' }}" method="POST" class="space-y-2">
                    @csrf
                    <input type="email" name="email" required placeholder="بريدك الإلكتروني" class="w-full px-4 py-2 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-white">
                    <button class="w-full bg-white text-violet-700 font-bold py-2 rounded-lg hover:bg-violet-50 transition">اشترك الآن</button>
                </form>
            </div>
        </aside>
    </div>
</section>
@endsection
