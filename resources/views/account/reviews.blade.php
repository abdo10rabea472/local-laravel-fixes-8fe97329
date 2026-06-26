@extends('account.layout')
@section('account_content')
<h1 class="text-2xl font-black text-slate-900 mb-6">مراجعاتي</h1>
<div class="space-y-3">
    @forelse($reviews as $r)
    <div class="bg-white p-4 rounded-2xl border border-slate-200">
        <div class="flex items-center justify-between">
            <span class="text-amber-500 text-lg">@for($i=0;$i<$r->rating;$i++)★@endfor<span class="text-slate-300">@for($i=0;$i<5-$r->rating;$i++)★@endfor</span></span>
            @php $sc = ['pending'=>'amber','approved'=>'emerald','rejected'=>'rose'][$r->status]; @endphp
            <span class="px-2 py-1 text-xs bg-{{ $sc }}-50 text-{{ $sc }}-700 rounded-full font-bold">
                {{ ['pending'=>'قيد المراجعة','approved'=>'معتمدة','rejected'=>'مرفوضة'][$r->status] }}
            </span>
        </div>
        @if($r->title)<h3 class="font-bold mt-1">{{ $r->title }}</h3>@endif
        <p class="text-sm text-slate-700 mt-1">{{ $r->body }}</p>
        <a href="{{ route('product.show', $r->product->slug) }}" class="text-xs text-violet-600 mt-2 inline-block">{{ $r->product->name }} ←</a>
        @if($r->admin_reply)
        <div class="mt-3 p-3 bg-violet-50 rounded-xl text-sm">
            <b class="text-violet-700">رد الإدارة:</b> {{ $r->admin_reply }}
        </div>
        @endif
    </div>
    @empty
    <div class="bg-white p-10 text-center text-slate-400 rounded-2xl">لا توجد مراجعات بعد.</div>
    @endforelse
</div>
<div class="mt-4">{{ $reviews->links() }}</div>
@endsection
