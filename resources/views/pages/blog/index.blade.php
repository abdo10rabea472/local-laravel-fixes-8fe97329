@extends('layouts.front')

@section('content')
<section class="bg-gradient-to-br from-violet-600 to-indigo-800 text-white py-16">
    <div class="max-w-6xl mx-auto px-4 text-center">
        <h1 class="text-4xl font-bold mb-3"><i class="fas fa-newspaper"></i> المدونة العلمية</h1>
        <p class="text-violet-100">مقالات وتجارب ونصائح للطلاب والباحثين</p>
    </div>
</section>

<section class="py-12 bg-slate-50">
    <div class="max-w-6xl mx-auto px-4 grid lg:grid-cols-4 gap-6">
        <aside class="lg:col-span-1 space-y-4">
            <form method="GET" class="bg-white rounded-xl p-4 shadow-sm">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="بحث في المقالات..."
                       class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500">
                <button class="mt-2 w-full bg-violet-600 text-white py-2 rounded-lg hover:bg-violet-700">بحث</button>
            </form>

            @if($categories->count())
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <h3 class="font-bold text-slate-800 mb-3">التصنيفات</h3>
                <ul class="space-y-2">
                    <li><a href="{{ route('blog.index') }}" class="text-slate-600 hover:text-violet-600 {{ !request('category') ? 'font-semibold text-violet-700' : '' }}">جميع المقالات</a></li>
                    @foreach($categories as $cat)
                    <li><a href="{{ route('blog.index', ['category' => $cat->slug]) }}" class="text-slate-600 hover:text-violet-600 {{ request('category')===$cat->slug ? 'font-semibold text-violet-700' : '' }}">{{ $cat->name }}</a></li>
                    @endforeach
                </ul>
            </div>
            @endif
        </aside>

        <div class="lg:col-span-3">
            @if($posts->isEmpty())
                <div class="bg-white rounded-2xl p-12 text-center shadow-sm">
                    <i class="fas fa-newspaper text-6xl text-slate-300 mb-4"></i>
                    <p class="text-slate-600">لا توجد مقالات منشورة حاليًا.</p>
                </div>
            @else
                <div class="grid md:grid-cols-2 gap-6">
                    @foreach($posts as $post)
                    <article class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition">
                        <a href="{{ route('blog.show', $post->slug) }}" class="block aspect-video bg-slate-100">
                            @if($post->image)
                                <img src="{{ asset('storage/'.$post->image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-violet-100 to-indigo-100"><i class="fas fa-newspaper text-5xl text-violet-300"></i></div>
                            @endif
                        </a>
                        <div class="p-5">
                            @if($post->category)<span class="text-xs bg-violet-100 text-violet-700 px-2 py-1 rounded">{{ $post->category->name }}</span>@endif
                            <h2 class="text-lg font-bold text-slate-800 mt-2 mb-2 line-clamp-2"><a href="{{ route('blog.show', $post->slug) }}" class="hover:text-violet-600">{{ $post->title }}</a></h2>
                            <p class="text-sm text-slate-600 line-clamp-3">{{ $post->excerpt }}</p>
                            <div class="mt-3 flex justify-between text-xs text-slate-400">
                                <span><i class="far fa-clock"></i> {{ $post->published_at?->diffForHumans() }}</span>
                                <span><i class="far fa-eye"></i> {{ $post->views }}</span>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>
                <div class="mt-8">{{ $posts->links() }}</div>
            @endif
        </div>
    </div>
</section>
@endsection
