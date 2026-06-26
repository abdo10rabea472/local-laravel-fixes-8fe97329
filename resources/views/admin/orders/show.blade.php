@extends('admin.layouts.app')
@section('title', 'الطلب ' . $order->order_number)

@section('content')
<div class="p-6 max-w-6xl mx-auto" x-data="orderShow({{ $order->id }}, '{{ $order->status }}')">
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-black text-slate-900">طلب <span class="font-mono text-violet-700">{{ $order->order_number }}</span></h1>
            <p class="text-sm text-slate-500 mt-1">{{ $order->created_at->format('Y-m-d H:i') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-sm font-bold"><i class="fa-solid fa-print ml-1"></i> طباعة الفاتورة</a>
            <button @click="resendEmail()" :disabled="busy" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white rounded-xl text-sm font-bold"><i class="fa-solid fa-envelope ml-1"></i> إرسال إيميل للعميل</button>
            <a href="{{ route('admin.orders.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-sm font-bold">رجوع</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Items -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="p-4 border-b border-slate-100 font-bold">العناصر</div>
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-xs text-slate-600"><tr>
                        <th class="p-3 text-right">المنتج</th><th class="p-3">السعر</th><th class="p-3">الكمية</th><th class="p-3">الإجمالي</th>
                    </tr></thead>
                    <tbody>
                        @foreach($order->items as $it)
                        <tr class="border-t border-slate-100">
                            <td class="p-3">
                                @if($it->product)
                                    <a href="{{ route('product.show', $it->product->slug) }}" target="_blank" class="text-violet-700 hover:underline">{{ $it->product_name }}</a>
                                @else
                                    {{ $it->product_name }}
                                @endif
                            </td>
                            <td class="p-3 text-center">{{ number_format($it->unit_price, 2) }}</td>
                            <td class="p-3 text-center">{{ $it->quantity }}</td>
                            <td class="p-3 text-center font-bold">{{ number_format($it->line_total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50 text-sm">
                        <tr><td colspan="3" class="p-2 text-left">الإجمالي الفرعي</td><td class="p-2 text-center">{{ number_format($order->subtotal, 2) }}</td></tr>
                        @if($order->discount_amount > 0)
                        <tr><td colspan="3" class="p-2 text-left">الخصم {{ $order->coupon_code ? '(' . $order->coupon_code . ')' : '' }}</td><td class="p-2 text-center text-rose-600">-{{ number_format($order->discount_amount, 2) }}</td></tr>
                        @endif
                        <tr><td colspan="3" class="p-2 text-left">الشحن</td><td class="p-2 text-center">{{ number_format($order->shipping_cost, 2) }}</td></tr>
                        <tr><td colspan="3" class="p-2 text-left font-bold">الإجمالي النهائي</td><td class="p-2 text-center font-black text-violet-700 text-lg">{{ number_format($order->total, 2) }} {{ $order->currency }}</td></tr>
                    </tfoot>
                </table>
            </div>

            <!-- Status history -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <h3 class="font-bold mb-3">سجل الحالة</h3>
                <ul class="space-y-2 text-sm">
                    @foreach($order->history as $h)
                        <li class="flex justify-between border-b border-slate-100 pb-2">
                            <span><b class="text-slate-700">{{ $h->from_status ?? '—' }}</b> → <b class="text-violet-700">{{ $h->to_status }}</b> @if($h->note)<span class="text-slate-500">— {{ $h->note }}</span>@endif</span>
                            <span class="text-xs text-slate-400">{{ $h->created_at->format('Y-m-d H:i') }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Customer -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4 text-sm">
                <h3 class="font-bold mb-3">العميل</h3>
                <p><b>الاسم:</b> {{ $order->customer_name ?: '—' }}</p>
                <p><b>الإيميل:</b> {{ $order->email }}</p>
                <p><b>الهاتف:</b> {{ $order->phone ?: '—' }}</p>
                @if($order->shipping_country || $order->shipping_address)
                <hr class="my-3">
                <h4 class="font-bold mb-2">عنوان الشحن</h4>
                <p>{{ $order->shipping_country }} {{ $order->shipping_region ? '/' . $order->shipping_region : '' }}</p>
                <p>{{ $order->shipping_address }}</p>
                <p>{{ $order->shipping_city }} {{ $order->shipping_postcode }}</p>
                @endif
                @if($order->notes)
                <hr class="my-3"><p class="text-slate-600"><b>ملاحظات:</b> {{ $order->notes }}</p>
                @endif
            </div>

            <!-- Status update -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <h3 class="font-bold mb-3">تحديث الحالة</h3>
                <select x-model="newStatus" class="w-full h-10 px-3 border border-slate-200 rounded-xl text-sm mb-2">
                    @foreach(['pending'=>'قيد الانتظار','paid'=>'مدفوع','shipped'=>'تم الشحن','delivered'=>'تم التوصيل','cancelled'=>'ملغي','refunded'=>'مسترد'] as $k=>$v)
                        <option value="{{ $k }}">{{ $v }}</option>
                    @endforeach
                </select>
                <input type="text" x-model="note" placeholder="ملاحظة (اختياري)" class="w-full h-10 px-3 border border-slate-200 rounded-xl text-sm mb-2">
                <label class="flex items-center gap-2 text-xs text-slate-600 mb-3">
                    <input type="checkbox" x-model="notify"> إرسال إيميل تلقائي للعميل
                </label>
                <button @click="updateStatus()" :disabled="busy" class="w-full h-10 bg-violet-600 hover:bg-violet-700 disabled:opacity-50 text-white rounded-xl font-bold text-sm">
                    <span x-show="!busy">حفظ</span><span x-show="busy">جاري الحفظ...</span>
                </button>
            </div>

            <!-- Shipment (carrier API) -->
            @if($order->carrier)
            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <h3 class="font-bold mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-truck-fast text-violet-600"></i> الشحنة
                    @if($order->shipping_status)
                        <span class="text-[10px] px-2 py-1 rounded-full bg-violet-100 text-violet-700">{{ $order->shipping_status }}</span>
                    @endif
                </h3>

                @if($order->shipping_error)
                    <div class="mb-3 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                        <p class="font-bold mb-1"><i class="fa-solid fa-triangle-exclamation"></i> فشل إنشاء الشحنة</p>
                        <p class="break-words">{{ $order->shipping_error }}</p>
                        <p class="mt-1 text-[10px] opacity-70">عدد المحاولات: {{ $order->shipping_attempts }}</p>
                    </div>
                @endif

                <div class="text-xs space-y-1 mb-3">
                    <p><b>الشركة:</b> {{ $order->carrier->name }}</p>
                    @if($order->shipment_number)
                        <p><b>رقم الشحنة:</b> <span class="font-mono">{{ $order->shipment_number }}</span></p>
                    @endif
                    @if($order->tracking_number)
                        <p><b>رقم التتبع:</b> <span class="font-mono">{{ $order->tracking_number }}</span></p>
                    @endif
                    @if($order->shipment_created_at)
                        <p><b>أنشئت في:</b> {{ $order->shipment_created_at->format('Y-m-d H:i') }}</p>
                    @endif
                </div>

                @if($order->barcode)
                    <div class="mb-3 p-2 bg-white rounded-lg border border-slate-200 text-center">
                        <p class="text-[10px] text-slate-500 mb-1">الباركود</p>
                        <img src="https://barcode.tec-it.com/barcode.ashx?data={{ urlencode($order->barcode) }}&code=Code128&dpi=96"
                             alt="Barcode" class="mx-auto h-12">
                        <p class="text-[10px] font-mono mt-1">{{ $order->barcode }}</p>
                    </div>
                @endif

                @if($order->pickup_address)
                    <div class="mb-3 p-2 rounded-lg bg-slate-50 text-xs">
                        <p class="font-bold mb-1">عنوان الاستلام</p>
                        @foreach((array) $order->pickup_address as $k => $v)
                            @if(!is_array($v))<p><span class="text-slate-500">{{ $k }}:</span> {{ $v }}</p>@endif
                        @endforeach
                        @if($order->pickup_datetime)
                            <p class="mt-1"><b>موعد الاستلام:</b> {{ $order->pickup_datetime->format('Y-m-d H:i') }}</p>
                        @endif
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-2">
                    @if($order->label_url)
                        <a href="{{ $order->label_url }}" target="_blank" rel="noopener"
                           class="h-9 inline-flex items-center justify-center bg-violet-600 hover:bg-violet-700 text-white rounded-xl font-bold text-xs">
                            <i class="fa-solid fa-print ml-1"></i> طباعة البوليصة
                        </a>
                        <a href="{{ $order->label_url }}" download
                           class="h-9 inline-flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs">
                            <i class="fa-solid fa-download ml-1"></i> تحميل
                        </a>
                    @endif
                    @if($order->tracking_url)
                        <a href="{{ $order->tracking_url }}" target="_blank" rel="noopener"
                           class="col-span-2 h-9 inline-flex items-center justify-center bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-xl font-bold text-xs">
                            <i class="fa-solid fa-location-arrow ml-1"></i> صفحة التتبع لدى الشركة
                        </a>
                    @endif
                    @if($order->shipment_number)
                        <form method="POST" action="{{ route('admin.orders.shipment.sync', $order) }}" class="col-span-2">@csrf
                            <button type="submit" class="w-full h-9 bg-sky-50 hover:bg-sky-100 text-sky-700 rounded-xl font-bold text-xs">
                                <i class="fa-solid fa-arrows-rotate ml-1"></i> مزامنة حالة الشحنة
                            </button>
                        </form>
                    @endif
                    @if(!$order->shipment_number && $order->shipping_carrier_id)
                        <form method="POST" action="{{ route('admin.orders.shipment.retry', $order) }}" class="col-span-2">@csrf
                            <button type="submit" class="w-full h-9 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-xs">
                                <i class="fa-solid fa-rotate ml-1"></i> إعادة محاولة إنشاء الشحنة
                            </button>
                        </form>
                    @endif
                </div>

                @if(!empty($order->tracking_history) && is_array($order->tracking_history))
                    <div class="mt-3 border-t pt-3 max-h-40 overflow-y-auto">
                        <p class="text-[10px] font-bold text-slate-500 mb-2">سجل التتبع</p>
                        @foreach(array_reverse($order->tracking_history) as $ev)
                            <div class="text-[11px] text-slate-600 border-r-2 border-violet-200 pr-2 py-1 mb-1">
                                <div class="font-semibold">{{ $ev['status'] ?? '' }} <span class="text-slate-400">{{ $ev['at'] ?? '' }}</span></div>
                                <div class="text-slate-500">{{ $ev['description'] ?? '' }} {{ !empty($ev['location']) ? '— '.$ev['location'] : '' }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            @endif

            <!-- Manual shipping override -->
            @php $carriers = \App\Models\ShippingCarrier::active()->orderBy('sort_order')->get(['id','name','default_cost']); @endphp
            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <h3 class="font-bold mb-3">تعديل يدوي للشحن</h3>
                <label class="block text-xs text-slate-500 mb-1">شركة الشحن</label>
                <select x-model="carrierId" @change="onCarrierChange()" class="w-full h-10 px-3 border border-slate-200 rounded-xl text-sm mb-2">
                    <option value="">— اختر —</option>
                    @foreach($carriers as $c)
                        <option value="{{ $c->id }}" data-cost="{{ $c->default_cost }}">{{ $c->name }}</option>
                    @endforeach
                </select>
                <label class="block text-xs text-slate-500 mb-1">رقم التتبع</label>
                <input type="text" x-model="tracking" placeholder="رقم التتبع" class="w-full h-10 px-3 border border-slate-200 rounded-xl text-sm mb-2">
                <label class="block text-xs text-slate-500 mb-1">التكلفة الفعلية</label>
                <input type="number" step="0.01" x-model="actualCost" placeholder="0.00" class="w-full h-10 px-3 border border-slate-200 rounded-xl text-sm mb-2">
                <label class="block text-xs text-slate-500 mb-1">ملاحظات الشحن</label>
                <textarea x-model="shipNotes" rows="2" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm mb-2"></textarea>
                <button @click="updateShipping()" :disabled="busy" class="w-full h-10 bg-slate-900 hover:bg-slate-800 disabled:opacity-50 text-white rounded-xl font-bold text-sm">حفظ بيانات الشحن</button>
            </div>

        </div>
    </div>
</div>


<script>
function orderShow(id, currentStatus){
    return {
        id, busy:false,
        newStatus: currentStatus, note:'', notify:true,
        carrier: @json($order->shipping_carrier),
        carrierId: @json($order->shipping_carrier_id),
        tracking: @json($order->tracking_number),
        actualCost: @json($order->actual_shipping_cost),
        shipNotes: @json($order->shipped_notes),
        async req(url, method, body){
            this.busy = true;
            try{
                const r = await fetch(url, {
                    method,
                    headers: { 'Content-Type':'application/json', 'Accept':'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: body ? JSON.stringify(body) : null
                });
                const j = await r.json();
                if(j.ok){ this.toast(j.message || 'تم', true); setTimeout(()=>location.reload(), 600); }
                else this.toast(j.message || 'حدث خطأ', false);
            } catch(e){ this.toast('خطأ في الاتصال', false); }
            finally { this.busy = false; }
        },
        toast(msg, ok){
            const t = document.createElement('div');
            t.className = `fixed top-4 right-4 z-50 px-5 py-3 rounded-xl text-white font-bold shadow-lg ${ok?'bg-emerald-600':'bg-rose-600'}`;
            t.textContent = msg; document.body.appendChild(t);
            setTimeout(()=>t.remove(), 2500);
        },
        onCarrierChange(){
            const opt = event.target.selectedOptions[0];
            if(opt && opt.dataset.cost && !this.actualCost) this.actualCost = opt.dataset.cost;
        },
        updateStatus(){ this.req(`/admin/orders/${this.id}/status`, 'PATCH', { status: this.newStatus, note: this.note, notify: this.notify }); },
        updateShipping(){ this.req(`/admin/orders/${this.id}/shipping`, 'PATCH', {
            shipping_carrier_id: this.carrierId || null,
            shipping_carrier: this.carrier,
            tracking_number: this.tracking,
            actual_shipping_cost: this.actualCost || null,
            shipped_notes: this.shipNotes,
        }); },
        resendEmail(){ this.req(`/admin/orders/${this.id}/resend-email`, 'POST', {}); }
    }
}
</script>
@endsection
