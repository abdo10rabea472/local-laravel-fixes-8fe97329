@extends('layouts.front')

@section('content')
<article class="py-12 bg-white">
    <div class="max-w-3xl mx-auto px-4">
        <nav class="text-sm text-slate-500 mb-4">
            <a href="{{ route('blog.index') }}" class="hover:text-violet-600">المدونة</a>
            @if($post->category) / <a href="{{ route('blog.index', ['category'=>$post->category->slug]) }}" class="hover:text-violet-600">{{ $post->category->name }}</a> @endif
        </nav>

        <h1 class="text-3xl md:text-4xl font-bold text-slate-800 mb-3">{{ $post->title }}</h1>
        <div class="flex items-center gap-4 text-sm text-slate-500 mb-6">
            <span><i class="far fa-calendar"></i> {{ $post->published_at?->format('Y-m-d') }}</span>
            <span><i class="far fa-eye"></i> {{ $post->views }} مشاهدة</span>
            @if($post->author)<span><i class="far fa-user"></i> {{ $post->author->name ?? 'فريق التحرير' }}</span>@endif
        </div>

        @if($post->image)
            <img src="{{ asset('storage/'.$post->image) }}" alt="{{ $post->title }}" class="w-full rounded-2xl mb-8">
        @endif

        <div class="prose prose-slate max-w-none text-slate-700 leading-relaxed text-lg">
            {!! nl2br(e($post->content)) !!}
        </div>
    </div>
</article>

@if($related->count())
<section class="py-12 bg-slate-50">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-2xl font-bold text-slate-800 mb-6">مقالات ذات صلة</h2>
        <div class="grid md:grid-cols-3 gap-6">
            @foreach($related as $r)
            <a href="{{ route('blog.show', $r->slug) }}" class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition">
                <div class="aspect-video bg-slate-100">
                    @if($r->image)<img src="{{ asset('storage/'.$r->image) }}" class="w-full h-full object-cover">@endif
                </div>
                <div class="p-4">
                    <h3 class="font-bold text-slate-800 line-clamp-2">{{ $r->title }}</h3>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
