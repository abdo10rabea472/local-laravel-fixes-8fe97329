@extends('admin.layouts.app')
@section('title', 'المراجعات')
@section('content')
<div class="p-6 max-w-7xl mx-auto" x-data="reviewsPage()">
    @if(session('success'))<div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl">{{ session('success') }}</div>@endif

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900">المراجعات والتقييمات</h1>
            <p class="text-sm text-slate-500 mt-1">موافقة / رفض / رد على مراجعات العملاء.</p>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-3 mb-6">
        <div class="bg-white p-4 rounded-2xl border border-slate-200"><p class="text-xs text-slate-500">قيد المراجعة</p><p class="text-xl font-black text-amber-600">{{ $counts['pending'] }}</p></div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200"><p class="text-xs text-slate-500">معتمدة</p><p class="text-xl font-black text-emerald-600">{{ $counts['approved'] }}</p></div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200"><p class="text-xs text-slate-500">مرفوضة</p><p class="text-xl font-black text-rose-600">{{ $counts['rejected'] }}</p></div>
    </div>

    <form method="GET" class="flex gap-2 mb-4">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="بحث..." class="h-10 px-3 border border-slate-200 rounded-xl text-sm flex-1">
        <select name="status" class="h-10 px-3 border border-slate-200 rounded-xl text-sm">
            <option value="">كل الحالات</option>
            <option value="pending" @selected(request('status')==='pending')>قيد المراجعة</option>
            <option value="approved" @selected(request('status')==='approved')>معتمدة</option>
            <option value="rejected" @selected(request('status')==='rejected')>مرفوضة</option>
        </select>
        <button class="h-10 px-4 bg-slate-900 text-white rounded-xl text-sm font-bold">فلتر</button>
    </form>

    <div class="space-y-3">
        @forelse($reviews as $r)
        <div class="bg-white rounded-2xl border border-slate-200 p-4" data-id="{{ $r->id }}">
            <div class="flex items-start justify-between gap-4 mb-2">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-amber-500 text-lg">@for($i=0;$i<$r->rating;$i++)★@endfor<span class="text-slate-300">@for($i=0;$i<5-$r->rating;$i++)★@endfor</span></span>
                        @php $sc = ['pending'=>'amber','approved'=>'emerald','rejected'=>'rose'][$r->status]; @endphp
                        <span class="px-2 py-1 text-xs bg-{{ $sc }}-50 text-{{ $sc }}-700 rounded-full font-bold">{{ $r->status }}</span>
                    </div>
                    @if($r->title)<h3 class="font-bold mt-1">{{ $r->title }}</h3>@endif
                    <p class="text-sm text-slate-700 mt-1">{{ $r->body }}</p>
                    <div class="text-xs text-slate-500 mt-2">
                        بواسطة: <b>{{ $r->user?->name ?? $r->reviewer_name ?? 'ضيف' }}</b> ({{ $r->user?->email ?? $r->reviewer_email }}) ·
                        المنتج: <a href="{{ route('product.show', $r->product->slug) }}" target="_blank" class="text-violet-600">{{ $r->product->name }}</a> ·
                        {{ $r->created_at->format('Y-m-d H:i') }}
                    </div>
                    @if($r->admin_reply)
                    <div class="mt-3 p-3 bg-violet-50 rounded-xl text-sm">
                        <b class="text-violet-700">رد الإدارة:</b> {{ $r->admin_reply }}
                    </div>
                    @endif
                </div>
                <div class="flex flex-col gap-2 shrink-0">
                    @if($r->status !== 'approved')
                    <button @click="setStatus({{ $r->id }}, 'approved')" class="px-3 py-1 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold">اعتماد</button>
                    @endif
                    @if($r->status !== 'rejected')
                    <button @click="setStatus({{ $r->id }}, 'rejected')" class="px-3 py-1 text-xs bg-rose-600 hover:bg-rose-700 text-white rounded-lg font-bold">رفض</button>
                    @endif
                    <button @click="replyTo={{ $r->id }}; replyText=`{{ addslashes($r->admin_reply ?? '') }}`" class="px-3 py-1 text-xs bg-slate-900 text-white rounded-lg font-bold">رد</button>
                    <button @click="del({{ $r->id }})" class="px-3 py-1 text-xs bg-slate-100 text-slate-600 rounded-lg font-bold">حذف</button>
                </div>
            </div>

            <div x-show="replyTo==={{ $r->id }}" x-cloak class="mt-3 border-t border-slate-100 pt-3">
                <textarea x-model="replyText" rows="3" class="w-full p-3 border border-slate-200 rounded-xl text-sm" placeholder="اكتب الرد..."></textarea>
                <div class="flex gap-2 mt-2">
                    <button @click="sendReply({{ $r->id }})" class="px-4 py-2 bg-violet-600 text-white rounded-lg text-sm font-bold">حفظ الرد</button>
                    <button @click="replyTo=null" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-sm">إلغاء</button>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white p-10 text-center text-slate-400 rounded-2xl border border-slate-200">لا توجد مراجعات.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $reviews->links() }}</div>
</div>

<script>
function reviewsPage(){return{
    replyTo:null, replyText:'',
    async req(url, method, body){
        try{
            const r = await fetch(url,{method,headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:body?JSON.stringify(body):null});
            const j = await r.json();
            this.toast(j.message||'تم', j.ok);
            if(j.ok) setTimeout(()=>location.reload(), 500);
        } catch(e){ this.toast('خطأ', false); }
    },
    toast(msg, ok){
        const t=document.createElement('div');
        t.className=`fixed top-4 right-4 z-50 px-5 py-3 rounded-xl text-white font-bold shadow-lg ${ok?'bg-emerald-600':'bg-rose-600'}`;
        t.textContent=msg; document.body.appendChild(t); setTimeout(()=>t.remove(),2000);
    },
    setStatus(id, status){ this.req(`/admin/reviews/${id}/status`, 'PATCH', {status}); },
    sendReply(id){ if(!this.replyText.trim()){this.toast('اكتب الرد',false); return;} this.req(`/admin/reviews/${id}/reply`, 'POST', {admin_reply:this.replyText}); },
    del(id){ if(!confirm('حذف المراجعة؟')) return; this.req(`/admin/reviews/${id}`, 'DELETE'); }
}}
</script>
@endsection
