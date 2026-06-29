@extends('account.layout')
@section('account_content')
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
    <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-violet-50/50 to-transparent">
        <h1 class="font-black text-slate-900 flex items-center gap-2">
            <i class="fa-solid fa-star text-amber-500"></i> {{ __('app.acc_reviews_title') }}
        </h1>
        <span class="text-xs font-bold text-slate-500">{{ __('app.acc_reviews_count', ['count' => $reviews->total()]) }}</span>
    </div>
</div>

<div class="space-y-3">
    @forelse($reviews as $r)
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-violet-200 transition">
        <div class="flex items-center justify-between">
            <span class="text-amber-500 text-lg tracking-tight">@for($i=0;$i<$r->rating;$i++)★@endfor<span class="text-slate-300">@for($i=0;$i<5-$r->rating;$i++)★@endfor</span></span>
            @php
                $sc = ['pending'=>'amber','approved'=>'emerald','rejected'=>'rose'][$r->status];
                $statusText = ['pending'=>__('app.acc_review_pending'),'approved'=>__('app.acc_review_approved'),'rejected'=>__('app.acc_review_rejected')][$r->status];
            @endphp
            <span class="px-3 py-1 text-xs bg-{{ $sc }}-50 text-{{ $sc }}-700 rounded-full font-bold">
                {{ $statusText }}
            </span>
        </div>
        @if($r->title)<h3 class="font-black text-slate-900 mt-2">{{ $r->title }}</h3>@endif
        <p class="text-sm text-slate-700 mt-1 leading-relaxed">{{ $r->body }}</p>
        <a href="{{ route('product.show', $r->product->slug) }}" class="inline-flex items-center gap-1 text-xs text-violet-600 font-bold mt-3 hover:underline">
            <i class="fa-solid fa-box text-[10px]"></i> {{ $r->product->name }}
        </a>
        @if($r->admin_reply)
        <div class="mt-3 p-3 bg-gradient-to-r from-violet-50 to-indigo-50 border border-violet-100 rounded-xl text-sm">
            <b class="text-violet-700">{{ __('app.acc_admin_reply') }}</b> {{ $r->admin_reply }}
        </div>
        @endif
    </div>
    @empty
    <div class="bg-white p-12 text-center rounded-2xl border border-slate-200">
        <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-500 grid place-items-center text-2xl mx-auto mb-3"><i class="fa-solid fa-star"></i></div>
        <p class="text-slate-500">{{ __('app.acc_no_reviews') }}</p>
    </div>
    @endforelse
</div>
<div class="mt-4">{{ $reviews->links() }}</div>
@endsection
