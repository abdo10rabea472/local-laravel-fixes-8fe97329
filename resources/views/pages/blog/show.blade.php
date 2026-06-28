@extends('layouts.front')

@push('styles')
    @if($post->no_index ?? false)<meta name="robots" content="noindex,nofollow">@endif
@endpush

@section('content')
@php
    $readingTime = max(1, (int) ceil(str_word_count(strip_tags($post->content)) / 200));
    $shareUrl = urlencode(route('blog.show', $post->slug));
    $shareTitle = urlencode($post->title);
@endphp

<div class="bg-stone-50 text-stone-900 [font-feature-settings:'ss01','ss02']">

    {{-- ============ BREADCRUMB ============ --}}
    <div class="border-b border-stone-200">
        <div class="w-full max-w-[1850px] mx-auto px-6 lg:px-16 py-5">
            <nav class="flex items-center gap-3 text-[11px] uppercase tracking-[0.25em] text-stone-500">
                <a href="{{ route('home') }}" class="hover:text-amber-700 transition">الرئيسية</a>
                <span>/</span>
                <a href="{{ route('blog.index') }}" class="hover:text-amber-700 transition">المجلة</a>
                @if($post->category)
                    <span>/</span>
                    <a href="{{ route('blog.index', ['category'=>$post->category->slug]) }}" class="hover:text-amber-700 transition">{{ $post->category->name }}</a>
                @endif
                <span>/</span>
                <span class="text-stone-900 font-bold truncate max-w-[260px]">{{ $post->title }}</span>
            </nav>
        </div>
    </div>

    {{-- ============ EDITORIAL HERO ============ --}}
    <header class="bg-stone-50">
        <div class="w-full max-w-[1850px] mx-auto px-6 lg:px-16 pt-16 lg:pt-24 pb-12">
            <div class="max-w-4xl mx-auto text-center">
                @if($post->category)
                    <a href="{{ route('blog.index', ['category'=>$post->category->slug]) }}"
                       class="inline-block text-[11px] uppercase tracking-[0.4em] text-amber-700 font-bold mb-6 hover:underline">
                        {{ $post->category->name }}
                    </a>
                @endif

                <h1 class="font-serif text-4xl md:text-6xl lg:text-7xl leading-[1.05] tracking-tight text-stone-900 mb-8">
                    {{ $post->title }}
                </h1>

                @if($post->excerpt)
                    <p class="font-serif italic text-xl md:text-2xl text-stone-600 leading-relaxed max-w-3xl mx-auto mb-10">
                        “{{ $post->excerpt }}”
                    </p>
                @endif

                <div class="flex items-center justify-center flex-wrap gap-x-6 gap-y-3 text-[11px] uppercase tracking-[0.25em] text-stone-500 pt-8 border-t border-stone-300 max-w-2xl mx-auto">
                    <span class="inline-flex items-center gap-2">
                        <span class="w-7 h-7 rounded-full bg-stone-900 text-stone-50 flex items-center justify-center font-serif text-sm normal-case">
                            {{ mb_substr($post->author->name ?? 'ف', 0, 1) }}
                        </span>
                        <span class="text-stone-900 font-bold">{{ $post->author->name ?? 'فريق التحرير' }}</span>
                    </span>
                    <span>·</span>
                    <span>{{ $post->published_at?->format('d F Y') }}</span>
                    <span>·</span>
                    <span>{{ $readingTime }} دقائق قراءة</span>
                    <span>·</span>
                    <span>{{ $post->views }} مشاهدة</span>
                </div>
            </div>
        </div>

        {{-- Full-bleed cover --}}
        <div class="w-full max-w-[1850px] mx-auto px-6 lg:px-16">
            <figure class="relative overflow-hidden">
                <img src="{{ $post->image_url }}" alt="{{ $post->title }}"
                     class="w-full aspect-[21/9] object-cover">
            </figure>
        </div>
    </header>

    {{-- ============ ARTICLE BODY + SIDEBAR ============ --}}
    <section class="py-16 lg:py-24">
        <div class="w-full max-w-[1850px] mx-auto px-6 lg:px-16 grid lg:grid-cols-[1fr_320px] xl:grid-cols-[1fr_360px] gap-16">

            {{-- ========== ARTICLE ========== --}}
            <article class="max-w-3xl mx-auto lg:mx-0 w-full">

                {{-- Share bar (sticky top on desktop) --}}
                <div class="flex items-center justify-between pb-6 mb-10 border-b border-stone-200">
                    <span class="text-[11px] uppercase tracking-[0.3em] text-stone-400 font-bold">شارك</span>
                    <div class="flex items-center gap-3">
                        <a target="_blank" href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareTitle }}"
                           class="w-10 h-10 border border-stone-300 rounded-full flex items-center justify-center text-stone-700 hover:bg-stone-900 hover:text-stone-50 hover:border-stone-900 transition"><i class="fab fa-x-twitter"></i></a>
                        <a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}"
                           class="w-10 h-10 border border-stone-300 rounded-full flex items-center justify-center text-stone-700 hover:bg-stone-900 hover:text-stone-50 hover:border-stone-900 transition"><i class="fab fa-facebook-f"></i></a>
                        <a target="_blank" href="https://wa.me/?text={{ $shareTitle }}%20{{ $shareUrl }}"
                           class="w-10 h-10 border border-stone-300 rounded-full flex items-center justify-center text-stone-700 hover:bg-stone-900 hover:text-stone-50 hover:border-stone-900 transition"><i class="fab fa-whatsapp"></i></a>
                        <a target="_blank" href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}"
                           class="w-10 h-10 border border-stone-300 rounded-full flex items-center justify-center text-stone-700 hover:bg-stone-900 hover:text-stone-50 hover:border-stone-900 transition"><i class="fab fa-linkedin-in"></i></a>
                        <button onclick="navigator.clipboard.writeText('{{ route('blog.show', $post->slug) }}'); this.innerHTML='<i class=&quot;fas fa-check&quot;></i>'"
                                class="w-10 h-10 border border-stone-300 rounded-full flex items-center justify-center text-stone-700 hover:bg-amber-500 hover:text-stone-900 hover:border-amber-500 transition"><i class="fas fa-link"></i></button>
                    </div>
                </div>

                {{-- First-letter drop cap via prose --}}
                <div class="prose prose-stone prose-lg lg:prose-xl max-w-none
                            prose-headings:font-serif prose-headings:font-bold prose-headings:text-stone-900 prose-headings:tracking-tight
                            prose-h2:text-4xl prose-h2:mt-16 prose-h2:mb-6 prose-h2:pb-3 prose-h2:border-b prose-h2:border-stone-200
                            prose-h3:text-2xl prose-h3:mt-12
                            prose-p:leading-loose prose-p:text-stone-700
                            prose-a:text-amber-700 prose-a:font-semibold prose-a:no-underline hover:prose-a:underline prose-a:decoration-amber-700
                            prose-strong:text-stone-900
                            prose-img:rounded-none prose-img:shadow-none prose-img:my-10
                            prose-blockquote:not-italic prose-blockquote:font-serif prose-blockquote:text-2xl prose-blockquote:text-stone-900
                            prose-blockquote:border-r-4 prose-blockquote:border-amber-500 prose-blockquote:bg-transparent prose-blockquote:pr-6 prose-blockquote:py-2
                            prose-ul:my-6 prose-li:marker:text-amber-700
                            first-letter:font-serif first-letter:text-7xl first-letter:font-bold first-letter:float-right first-letter:ml-3 first-letter:mt-1 first-letter:leading-none first-letter:text-amber-700">
                    {!! $post->content !!}
                </div>

                {{-- Tags --}}
                @if($post->meta_keywords)
                <div class="mt-16 pt-8 border-t border-stone-200">
                    <span class="block text-[11px] uppercase tracking-[0.3em] text-stone-400 font-bold mb-4">الوسوم</span>
                    <div class="flex flex-wrap gap-2">
                        @foreach(array_filter(array_map('trim', explode(',', $post->meta_keywords))) as $tag)
                            <span class="px-4 py-1.5 border border-stone-300 text-stone-700 text-xs rounded-full hover:bg-stone-900 hover:text-stone-50 hover:border-stone-900 transition cursor-pointer">#{{ $tag }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Author --}}
                @if($post->author)
                <div class="mt-16 p-8 lg:p-10 bg-stone-900 text-stone-50 flex flex-col sm:flex-row items-start gap-6">
                    <div class="w-20 h-20 rounded-full bg-amber-500 text-stone-900 flex items-center justify-center font-serif text-3xl font-bold shrink-0">
                        {{ mb_substr($post->author->name ?? 'ف', 0, 1) }}
                    </div>
                    <div class="flex-1">
                        <span class="block text-[11px] uppercase tracking-[0.3em] text-amber-400 font-bold mb-2">كاتب المقال</span>
                        <h3 class="font-serif text-2xl mb-2">{{ $post->author->name ?? 'فريق التحرير' }}</h3>
                        <p class="text-stone-300 leading-loose">عضو في فريق التحرير، يكتب عن الأبحاث والمعرفة ويهتم بمتابعة المستجدات في هذا المجال.</p>
                    </div>
                </div>
                @endif

                {{-- Comments --}}
                <div id="comments" class="mt-20 pt-12 border-t border-stone-300">
                    <div class="flex items-end justify-between mb-10">
                        <div>
                            <span class="block text-[11px] uppercase tracking-[0.3em] text-amber-700 font-bold mb-2">— Discussion</span>
                            <h2 class="font-serif text-4xl text-stone-900">
                                التعليقات
                                <span class="text-stone-400 font-light">({{ $post->approvedComments->count() }})</span>
                            </h2>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="mb-8 p-4 bg-emerald-50 border-r-4 border-emerald-500 text-emerald-800 flex items-center gap-2">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                        </div>
                    @endif

                    <div class="bg-white border border-stone-200 p-8 mb-10">
                        <h3 class="font-serif text-2xl text-stone-900 mb-6">اترك تعليقاً</h3>
                        <form action="{{ route('blog.comments.store', $post->slug) }}" method="POST" class="space-y-5">
                            @csrf
                            @auth
                                <p class="text-sm text-stone-600">تعلق باسم: <span class="font-bold text-stone-900">{{ auth()->user()->name }}</span></p>
                            @else
                                <div class="grid md:grid-cols-2 gap-5">
                                    <div>
                                        <label class="block text-[11px] uppercase tracking-[0.25em] font-bold text-stone-500 mb-2">الاسم *</label>
                                        <input type="text" name="name" required value="{{ old('name') }}"
                                               class="w-full px-0 py-2 border-0 border-b-2 border-stone-300 bg-transparent focus:ring-0 focus:border-stone-900 outline-none transition">
                                        @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label class="block text-[11px] uppercase tracking-[0.25em] font-bold text-stone-500 mb-2">البريد *</label>
                                        <input type="email" name="email" required value="{{ old('email') }}"
                                               class="w-full px-0 py-2 border-0 border-b-2 border-stone-300 bg-transparent focus:ring-0 focus:border-stone-900 outline-none transition">
                                        @error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                            @endauth
                            <div>
                                <label class="block text-[11px] uppercase tracking-[0.25em] font-bold text-stone-500 mb-2">تعليقك *</label>
                                <textarea name="body" rows="4" required placeholder="شاركنا رأيك…"
                                          class="w-full px-0 py-2 border-0 border-b-2 border-stone-300 bg-transparent focus:ring-0 focus:border-stone-900 outline-none resize-y transition">{{ old('body') }}</textarea>
                                @error('body')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <button type="submit"
                                    class="inline-flex items-center gap-3 px-8 py-3 bg-stone-900 text-stone-50 text-sm uppercase tracking-[0.25em] font-bold hover:bg-amber-600 transition">
                                نشر التعليق <i class="fas fa-arrow-left"></i>
                            </button>
                        </form>
                    </div>

                    @if($post->approvedComments->count())
                        <div class="space-y-6">
                            @foreach($post->approvedComments as $c)
                            <div class="bg-white border border-stone-200 p-6 flex gap-5">
                                <div class="w-12 h-12 rounded-full bg-stone-900 text-stone-50 flex items-center justify-center font-serif text-lg font-bold shrink-0">
                                    {{ mb_substr($c->name, 0, 1) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between flex-wrap gap-2 mb-2">
                                        <h4 class="font-bold text-stone-900">{{ $c->name }}</h4>
                                        <span class="text-[11px] uppercase tracking-widest text-stone-400">{{ $c->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-stone-700 leading-loose whitespace-pre-line">{{ $c->body }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="border-2 border-dashed border-stone-200 p-12 text-center">
                            <div class="font-serif text-5xl text-stone-300 mb-3">“ ”</div>
                            <p class="text-stone-500">لا توجد تعليقات بعد. كن أول من يعلق.</p>
                        </div>
                    @endif
                </div>
            </article>

            {{-- ========== SIDEBAR ========== --}}
            <aside class="lg:sticky lg:top-24 lg:self-start space-y-10">

                {{-- Related --}}
                @if(($related ?? collect())->count())
                <div>
                    <div class="border-b border-stone-300 pb-4 mb-6">
                        <span class="block text-[11px] uppercase tracking-[0.3em] text-amber-700 font-bold mb-1">— Related</span>
                        <h3 class="font-serif text-2xl text-stone-900">مقالات ذات صلة</h3>
                    </div>
                    <ul class="space-y-6">
                        @foreach($related->take(4) as $i => $r)
                        <li>
                            <a href="{{ route('blog.show', $r->slug) }}" class="group flex gap-4 items-start">
                                <span class="font-serif text-2xl text-amber-700 leading-none shrink-0 w-8">{{ str_pad($i+1, 2, '0', STR_PAD_LEFT) }}</span>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-serif text-base leading-snug text-stone-900 group-hover:text-amber-700 transition line-clamp-3 mb-2">{{ $r->title }}</h4>
                                    <span class="text-[11px] uppercase tracking-widest text-stone-400">{{ $r->published_at?->format('d M Y') }}</span>
                                </div>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Newsletter mini --}}
                <div class="bg-stone-900 text-stone-50 p-8">
                    <span class="block text-[11px] uppercase tracking-[0.3em] text-amber-400 font-bold mb-3">— Newsletter</span>
                    <h3 class="font-serif text-2xl mb-3 leading-tight">لا تفوّت<br><em class="italic font-light text-amber-400">المقال القادم.</em></h3>
                    <p class="text-sm text-stone-400 leading-relaxed mb-5">رسالة واحدة كل أسبوع. أفكار منتقاة.</p>
                    <form action="{{ route('newsletter.subscribe') ?? '#' }}" method="POST" class="space-y-3">
                        @csrf
                        <input type="email" name="email" required placeholder="بريدك"
                               class="w-full bg-transparent border-0 border-b border-stone-600 text-stone-50 px-0 py-2 focus:ring-0 focus:border-amber-400 outline-none placeholder-stone-500 transition">
                        <button class="w-full text-right text-sm uppercase tracking-[0.25em] font-bold text-amber-400 hover:text-amber-300 transition pt-2">
                            اشترك ←
                        </button>
                    </form>
                </div>

                {{-- Back to top --}}
                <a href="#" class="block text-center text-[11px] uppercase tracking-[0.3em] font-bold text-stone-500 hover:text-amber-700 transition py-4 border-t border-stone-200">
                    ↑ العودة للأعلى
                </a>
            </aside>

        </div>
    </section>

</div>
@endsection
