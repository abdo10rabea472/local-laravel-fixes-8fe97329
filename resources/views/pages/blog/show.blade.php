@extends('layouts.front')

@push('styles')
    @if($post->no_index ?? false)<meta name="robots" content="noindex,nofollow">@endif
@endpush

@section('content')
<section class="relative bg-gradient-to-br from-slate-900 via-violet-900 to-indigo-900 text-white pt-16 pb-32 overflow-hidden">
    <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 30%, #a78bfa 0, transparent 40%), radial-gradient(circle at 80% 70%, #6366f1 0, transparent 45%);"></div>
    <div class="relative max-w-4xl mx-auto px-4">
        <nav class="flex items-center gap-2 text-sm text-violet-200 mb-6">
            <a href="{{ route('home') }}" class="hover:text-white">الرئيسية</a>
            <i class="fas fa-chevron-left text-[10px]"></i>
            <a href="{{ route('blog.index') }}" class="hover:text-white">المدونة</a>
            @if($post->category)
                <i class="fas fa-chevron-left text-[10px]"></i>
                <a href="{{ route('blog.index', ['category'=>$post->category->slug]) }}" class="hover:text-white">{{ $post->category->name }}</a>
            @endif
        </nav>

        @if($post->category)
            <span class="inline-block px-3 py-1 bg-white/15 backdrop-blur border border-white/20 text-white text-xs font-bold rounded-full mb-4">{{ $post->category->name }}</span>
        @endif

        <h1 class="text-3xl md:text-5xl font-extrabold leading-tight tracking-tight mb-6">{{ $post->title }}</h1>

        <div class="flex flex-wrap items-center gap-5 text-sm text-violet-100">
            <div class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-full bg-violet-500 flex items-center justify-center font-bold">
                    {{ mb_substr($post->author->name ?? 'ف', 0, 1) }}
                </div>
                <span class="font-medium">{{ $post->author->name ?? 'فريق التحرير' }}</span>
            </div>
            <span class="inline-flex items-center gap-1.5"><i class="far fa-calendar"></i> {{ $post->published_at?->format('d M Y') }}</span>
            <span class="inline-flex items-center gap-1.5"><i class="far fa-eye"></i> {{ $post->views }} مشاهدة</span>
            <span class="inline-flex items-center gap-1.5"><i class="far fa-comments"></i> {{ $post->approvedComments->count() }} تعليق</span>
            <span class="inline-flex items-center gap-1.5"><i class="far fa-clock"></i> {{ max(1, (int) ceil(str_word_count(strip_tags($post->content)) / 200)) }} دقائق قراءة</span>
        </div>
    </div>
</section>

<div class="max-w-4xl mx-auto px-4 -mt-24 relative z-10">
    @if($post->image)
        <img src="{{ asset('storage/'.$post->image) }}" alt="{{ $post->title }}" class="w-full rounded-3xl shadow-2xl aspect-video object-cover">
    @endif
</div>

<article class="py-12 bg-white">
    <div class="max-w-3xl mx-auto px-4">
        @if($post->excerpt)
            <p class="text-xl text-slate-600 leading-relaxed mb-8 pb-8 border-b border-slate-200 font-medium italic">
                {{ $post->excerpt }}
            </p>
        @endif

        <div class="prose prose-lg prose-slate max-w-none leading-relaxed prose-headings:font-extrabold prose-headings:text-slate-900 prose-a:text-violet-600 prose-a:no-underline hover:prose-a:underline prose-img:rounded-xl prose-img:shadow-md prose-blockquote:border-r-4 prose-blockquote:border-violet-500 prose-blockquote:bg-violet-50 prose-blockquote:py-2 prose-blockquote:px-4 prose-blockquote:rounded-l-lg prose-blockquote:not-italic prose-strong:text-slate-900">
            {!! $post->content !!}
        </div>

        @if($post->meta_keywords)
        <div class="mt-10 pt-6 border-t border-slate-200 flex flex-wrap gap-2">
            <span class="text-sm text-slate-500 font-semibold">الوسوم:</span>
            @foreach(array_filter(array_map('trim', explode(',', $post->meta_keywords))) as $tag)
                <span class="px-3 py-1 bg-slate-100 hover:bg-violet-100 hover:text-violet-700 text-slate-700 text-xs rounded-full transition cursor-pointer">#{{ $tag }}</span>
            @endforeach
        </div>
        @endif

        {{-- Share --}}
        <div class="mt-8 flex items-center gap-3 flex-wrap">
            <span class="text-sm text-slate-500 font-semibold">شارك المقال:</span>
            @php $u = urlencode(route('blog.show', $post->slug)); $t = urlencode($post->title); @endphp
            <a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u={{ $u }}" class="w-9 h-9 rounded-full bg-[#1877F2] text-white flex items-center justify-center hover:opacity-90"><i class="fab fa-facebook-f"></i></a>
            <a target="_blank" href="https://twitter.com/intent/tweet?url={{ $u }}&text={{ $t }}" class="w-9 h-9 rounded-full bg-black text-white flex items-center justify-center hover:opacity-90"><i class="fab fa-x-twitter"></i></a>
            <a target="_blank" href="https://wa.me/?text={{ $t }}%20{{ $u }}" class="w-9 h-9 rounded-full bg-[#25D366] text-white flex items-center justify-center hover:opacity-90"><i class="fab fa-whatsapp"></i></a>
            <a target="_blank" href="https://www.linkedin.com/sharing/share-offsite/?url={{ $u }}" class="w-9 h-9 rounded-full bg-[#0A66C2] text-white flex items-center justify-center hover:opacity-90"><i class="fab fa-linkedin-in"></i></a>
            <button onclick="navigator.clipboard.writeText('{{ route('blog.show', $post->slug) }}'); this.innerHTML='<i class=&quot;fas fa-check&quot;></i>'" class="w-9 h-9 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center hover:bg-slate-300"><i class="fas fa-link"></i></button>
        </div>

        @if($post->author)
        <div class="mt-10 p-6 bg-gradient-to-br from-violet-50 to-indigo-50 rounded-2xl border border-violet-100 flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-violet-600 text-white flex items-center justify-center text-2xl font-bold flex-shrink-0">
                {{ mb_substr($post->author->name ?? 'ف', 0, 1) }}
            </div>
            <div>
                <p class="text-xs text-violet-600 font-semibold">كاتب المقال</p>
                <p class="text-lg font-bold text-slate-900">{{ $post->author->name ?? 'فريق التحرير' }}</p>
                <p class="text-sm text-slate-600">عضو في فريق التحرير، يكتب عن الأبحاث والمعرفة.</p>
            </div>
        </div>
        @endif
    </div>
</article>

{{-- Comments --}}
<section id="comments" class="py-12 bg-slate-50 border-t border-slate-100">
    <div class="max-w-3xl mx-auto px-4">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl font-extrabold text-slate-900 flex items-center gap-2">
                <i class="far fa-comments text-violet-600"></i>
                التعليقات
                <span class="text-base font-medium text-slate-500">({{ $post->approvedComments->count() }})</span>
            </h2>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Comment Form --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-8">
            <h3 class="text-lg font-bold text-slate-900 mb-4">اترك تعليقاً</h3>
            <form action="{{ route('blog.comments.store', $post->slug) }}" method="POST" class="space-y-4">
                @csrf
                @auth
                    <p class="text-sm text-slate-600">تعلق باسم: <span class="font-bold text-violet-700">{{ auth()->user()->name }}</span></p>
                @else
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">الاسم *</label>
                            <input type="text" name="name" required value="{{ old('name') }}" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-violet-500 focus:border-violet-500 outline-none">
                            @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">البريد الإلكتروني *</label>
                            <input type="email" name="email" required value="{{ old('email') }}" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-violet-500 focus:border-violet-500 outline-none">
                            @error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                @endauth
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">تعليقك *</label>
                    <textarea name="body" rows="4" required placeholder="شاركنا رأيك بأدب واحترام..." class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-violet-500 focus:border-violet-500 outline-none resize-y">{{ old('body') }}</textarea>
                    @error('body')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-violet-600 to-indigo-600 text-white font-bold rounded-xl hover:shadow-lg hover:-translate-y-0.5 transition-all">
                    <i class="fas fa-paper-plane"></i> نشر التعليق
                </button>
            </form>
        </div>

        {{-- Comments List --}}
        @if($post->approvedComments->count())
            <div class="space-y-4">
                @foreach($post->approvedComments as $c)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex gap-4">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 text-white flex items-center justify-center font-bold flex-shrink-0">
                        {{ mb_substr($c->name, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between flex-wrap gap-2 mb-1">
                            <h4 class="font-bold text-slate-900">{{ $c->name }}</h4>
                            <span class="text-xs text-slate-400"><i class="far fa-clock"></i> {{ $c->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-slate-700 leading-relaxed whitespace-pre-line">{{ $c->body }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-2xl border border-dashed border-slate-200 p-10 text-center">
                <i class="far fa-comment-dots text-5xl text-slate-300 mb-3"></i>
                <p class="text-slate-500">لا توجد تعليقات بعد. كن أول من يعلق!</p>
            </div>
        @endif
    </div>
</section>

@if($related->count())
<section class="py-14 bg-white border-t border-slate-100">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-2xl font-extrabold text-slate-900 mb-8 flex items-center gap-2">
            <i class="fas fa-bookmark text-violet-600"></i> مقالات ذات صلة
        </h2>
        <div class="grid md:grid-cols-3 gap-6">
            @foreach($related as $r)
            <a href="{{ route('blog.show', $r->slug) }}" class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all border border-slate-100">
                <div class="aspect-video bg-slate-100 overflow-hidden">
                    @if($r->image)
                        <img src="{{ asset('storage/'.$r->image) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-violet-100 to-indigo-100"><i class="fas fa-newspaper text-5xl text-violet-300"></i></div>
                    @endif
                </div>
                <div class="p-5">
                    <h3 class="font-bold text-slate-900 line-clamp-2 group-hover:text-violet-700 transition">{{ $r->title }}</h3>
                    <p class="text-xs text-slate-400 mt-2"><i class="far fa-calendar"></i> {{ $r->published_at?->format('Y-m-d') }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
