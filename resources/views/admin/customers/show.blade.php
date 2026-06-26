@extends('admin.layouts.app')
@section('title', 'العميل ' . $customer->name)
@section('content')
<div class="p-6 max-w-6xl mx-auto" x-data="customerShow({{ $customer->id }})">
    @if(session('success'))<div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl">{{ session('success') }}</div>@endif

    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-black text-slate-900">{{ $customer->name }}</h1>
            <p class="text-sm text-slate-500">{{ $customer->email }} {{ $customer->phone ? '· ' . $customer->phone : '' }}</p>
        </div>
        <div class="flex gap-2">
            <button @click="toggleActive()" :disabled="busy"
                class="px-4 py-2 rounded-xl text-sm font-bold text-white {{ $customer->is_active ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-600 hover:bg-emerald-700' }}">
                <i class="fa-solid {{ $customer->is_active ? 'fa-ban' : 'fa-check' }} ml-1"></i>
                {{ $customer->is_active ? 'حظر' : 'تفعيل' }}
            </button>
            <a href="{{ route('admin.customers.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-sm font-bold">رجوع</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Stats -->
            <div class="grid grid-cols-3 gap-3">
                <div class="bg-white p-4 rounded-2xl border border-slate-200 text-center">
                    <p class="text-xs text-slate-500">الطلبات</p>
                    <p class="text-2xl font-black text-violet-600">{{ $customer->orders->count() }}</p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 text-center">
                    <p class="text-xs text-slate-500">إجمالي الإنفاق</p>
                    <p class="text-2xl font-black text-emerald-600">{{ number_format($totalSpent, 0) }} EGP</p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 text-center">
                    <p class="text-xs text-slate-500">المراجعات</p>
                    <p class="text-2xl font-black text-amber-600">{{ $customer->reviews->count() }}</p>
                </div>
            </div>

            <!-- Orders -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="p-4 border-b border-slate-100 font-bold">الطلبات الأخيرة</div>
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-xs text-slate-600"><tr>
                        <th class="p-3 text-right">رقم الطلب</th><th class="p-3">العناصر</th><th class="p-3">الإجمالي</th><th class="p-3">الحالة</th><th class="p-3">التاريخ</th><th class="p-3"></th>
                    </tr></thead>
                    <tbody>
                        @forelse($customer->orders as $o)
                        <tr class="border-t border-slate-100">
                            <td class="p-3 font-mono font-bold text-violet-700">{{ $o->order_number }}</td>
                            <td class="p-3 text-center">{{ $o->items_count }}</td>
                            <td class="p-3 text-center">{{ number_format($o->total, 2) }}</td>
                            <td class="p-3 text-center text-xs">{{ $o->statusLabel() }}</td>
                            <td class="p-3 text-center text-xs">{{ $o->created_at->format('Y-m-d') }}</td>
                            <td class="p-3 text-center"><a href="{{ route('admin.orders.show', $o) }}" class="text-violet-600 text-xs font-bold">عرض</a></td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="p-6 text-center text-slate-400 text-sm">لا توجد طلبات.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Reviews -->
            @if($customer->reviews->count() > 0)
            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <h3 class="font-bold mb-3">مراجعات العميل</h3>
                @foreach($customer->reviews as $r)
                <div class="border-b border-slate-100 py-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-amber-500">@for($i=0;$i<$r->rating;$i++)★@endfor</span>
                        <span class="text-xs text-slate-400">{{ $r->created_at->format('Y-m-d') }}</span>
                    </div>
                    <p class="text-slate-700 mt-1">{{ $r->body }}</p>
                    <a href="{{ route('product.show', $r->product->slug) }}" class="text-xs text-violet-600">{{ $r->product->name }}</a>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <div class="space-y-6">
            <!-- Edit info -->
            <form method="POST" action="{{ route('admin.customers.update', $customer) }}" class="bg-white rounded-2xl border border-slate-200 p-4 space-y-3">
                @csrf @method('PUT')
                <h3 class="font-bold">بيانات العميل</h3>
                <div>
                    <label class="text-xs text-slate-600">الهاتف</label>
                    <input type="text" name="phone" value="{{ $customer->phone }}" class="w-full h-10 px-3 border border-slate-200 rounded-xl text-sm">
                </div>
                <div>
                    <label class="text-xs text-slate-600">المجموعة</label>
                    <select name="customer_group_id" class="w-full h-10 px-3 border border-slate-200 rounded-xl text-sm">
                        <option value="">— بدون —</option>
                        @foreach($groups as $g)
                            <option value="{{ $g->id }}" @selected($customer->customer_group_id==$g->id)>{{ $g->name }} ({{ $g->discount_percent }}%)</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-slate-600">ملاحظات داخلية</label>
                    <textarea name="admin_notes" rows="4" class="w-full p-3 border border-slate-200 rounded-xl text-sm">{{ $customer->admin_notes }}</textarea>
                </div>
                <button class="w-full h-10 bg-violet-600 hover:bg-violet-700 text-white rounded-xl font-bold text-sm">حفظ</button>
            </form>

            <!-- Send email -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4 space-y-3">
                <h3 class="font-bold">إرسال بريد للعميل</h3>
                <input type="text" x-model="subject" placeholder="الموضوع" class="w-full h-10 px-3 border border-slate-200 rounded-xl text-sm">
                <textarea x-model="body" rows="5" placeholder="نص الرسالة..." class="w-full p-3 border border-slate-200 rounded-xl text-sm"></textarea>
                <button @click="sendEmail()" :disabled="busy" class="w-full h-10 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white rounded-xl font-bold text-sm">
                    <span x-show="!busy">إرسال</span><span x-show="busy">جاري الإرسال...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function customerShow(id){return{
    id, busy:false, subject:'', body:'',
    async req(url, method, body){
        this.busy=true;
        try{
            const r = await fetch(url,{method,headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:body?JSON.stringify(body):null});
            const j = await r.json();
            this.toast(j.message||'تم', j.ok);
            if(j.ok && url.includes('toggle-active')) setTimeout(()=>location.reload(), 600);
        } catch(e){ this.toast('خطأ في الاتصال', false); }
        finally { this.busy=false; }
    },
    toast(msg, ok){
        const t = document.createElement('div');
        t.className = `fixed top-4 right-4 z-50 px-5 py-3 rounded-xl text-white font-bold shadow-lg ${ok?'bg-emerald-600':'bg-rose-600'}`;
        t.textContent = msg; document.body.appendChild(t); setTimeout(()=>t.remove(), 2500);
    },
    toggleActive(){ this.req(`/admin/customers/${this.id}/toggle-active`, 'PATCH'); },
    sendEmail(){
        if(!this.subject || !this.body){ this.toast('املأ الموضوع والنص', false); return; }
        this.req(`/admin/customers/${this.id}/send-email`, 'POST', { subject:this.subject, body:this.body }).then(()=>{ this.subject=''; this.body=''; });
    }
}}
</script>
@endsection
