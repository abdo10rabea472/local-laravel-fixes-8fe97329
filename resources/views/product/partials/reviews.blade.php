@php
    $approved = $product->reviews()->approved()->latest()->limit(20)->get();
    $avg = $product->reviews()->approved()->avg('rating');
    $count = $approved->count();
    $userCanReview = auth()->check() && \App\Models\Order::where('user_id', auth()->id())
        ->whereIn('status', ['paid','shipped','delivered'])
        ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
        ->exists();
    $userReview = auth()->check() ? \App\Models\Review::where('product_id', $product->id)->where('user_id', auth()->id())->first() : null;
@endphp

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="bg-white rounded-3xl border border-slate-200 p-6 lg:p-8">
        <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-black text-slate-900">المراجعات والتقييمات</h2>
                @if($count > 0)
                <div class="flex items-center gap-3 mt-2">
                    <span class="text-amber-500 text-2xl">@for($i=0;$i<round($avg);$i++)★@endfor<span class="text-slate-300">@for($i=0;$i<5-round($avg);$i++)★@endfor</span></span>
                    <span class="text-slate-600 font-bold">{{ number_format($avg, 1) }} / 5</span>
                    <span class="text-slate-400 text-sm">({{ $count }} مراجعة)</span>
                </div>
                @else
                <p class="text-sm text-slate-500 mt-1">لا توجد مراجعات بعد. كن أول من يقيّم!</p>
                @endif
            </div>
        </div>

        @auth
            @if($userCanReview)
            <form method="POST" action="{{ route('account.reviews.store') }}" class="bg-slate-50 p-5 rounded-2xl mb-6">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <h3 class="font-bold mb-3">{{ $userReview ? 'تعديل مراجعتك' : 'أضف مراجعتك' }}</h3>
                <div class="mb-3" x-data="{ rating: {{ $userReview->rating ?? 5 }} }">
                    <label class="text-sm text-slate-600 block mb-1">التقييم</label>
                    <div class="flex gap-1 text-3xl cursor-pointer">
                        <template x-for="i in 5" :key="i">
                            <span @click="rating=i" :class="i <= rating ? 'text-amber-500' : 'text-slate-300'">★</span>
                        </template>
                    </div>
                    <input type="hidden" name="rating" :value="rating">
                </div>
                <input type="text" name="title" value="{{ $userReview->title ?? '' }}" placeholder="عنوان المراجعة (اختياري)" class="w-full h-11 px-3 border border-slate-200 rounded-xl text-sm mb-3">
                <textarea name="body" rows="4" required class="w-full p-3 border border-slate-200 rounded-xl text-sm mb-3" placeholder="شاركنا رأيك في المنتج...">{{ $userReview->body ?? '' }}</textarea>
                <button class="px-6 py-2.5 bg-violet-600 hover:bg-violet-700 text-white font-bold rounded-xl">{{ $userReview ? 'تحديث المراجعة' : 'إرسال المراجعة' }}</button>
            </form>
            @else
            <div class="bg-slate-50 p-4 rounded-xl text-sm text-slate-600 mb-6">لا يمكنك مراجعة هذا المنتج إلا بعد شرائه.</div>
            @endif
        @else
            <div class="bg-slate-50 p-4 rounded-xl text-sm text-slate-600 mb-6">
                <a href="{{ route('login') }}" class="text-violet-600 font-bold">سجل دخولك</a> لكتابة مراجعة.
            </div>
        @endauth

        <div class="space-y-4">
            @foreach($approved as $r)
            <div class="border border-slate-100 rounded-2xl p-4">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <div>
                        <div class="font-bold text-slate-800">{{ $r->user?->name ?? $r->reviewer_name ?? 'عميل' }}</div>
                        <span class="text-amber-500">@for($i=0;$i<$r->rating;$i++)★@endfor<span class="text-slate-300">@for($i=0;$i<5-$r->rating;$i++)★@endfor</span></span>
                    </div>
                    <span class="text-xs text-slate-400">{{ $r->created_at->diffForHumans() }}</span>
                </div>
                @if($r->title)<h4 class="font-bold mb-1">{{ $r->title }}</h4>@endif
                <p class="text-sm text-slate-700">{{ $r->body }}</p>
                @if($r->admin_reply)
                <div class="mt-3 p-3 bg-violet-50 rounded-xl text-sm">
                    <b class="text-violet-700"><i class="fa-solid fa-reply ml-1"></i> رد الإدارة:</b> {{ $r->admin_reply }}
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</section>
