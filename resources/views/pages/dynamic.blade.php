@extends('layouts.front')

@section('content')
<section class="bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-900 text-white py-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 text-center">
        <h1 class="text-4xl sm:text-5xl font-black tracking-tight mb-3">{{ $page->title }}</h1>
        @if($page->seo_description)
            <p class="text-lg text-slate-200 max-w-2xl mx-auto">{{ $page->seo_description }}</p>
        @endif
    </div>
</section>

<main class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
    <div class="prose prose-slate prose-lg max-w-none bg-white rounded-3xl shadow-sm border border-slate-100 p-8 sm:p-10">
        @if(trim((string) $page->content) !== '')
            {!! $page->content !!}
        @else
            <p class="text-slate-500 italic">This page has no content yet.</p>
        @endif
    </div>
</main>
@endsection
