@extends('admin.layouts.app')

@section('title', 'مرتجع #' . $return->rma_number)

@section('content')
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">{{ $return->rma_number }}</h1>
            <p class="text-sm text-slate-500 mt-1">
                الطلب: <a href="{{ route('admin.orders.show', $return->order_id) }}" class="text-violet-600 font-semibold hover:underline">{{ $return->order?->order_number }}</a>
            </p>
        </div>
        <a href="{{ route('admin.returns.index') }}" class="text-violet-600 hover:underline text-sm font-semibold">
            <i class="fa-solid fa-arrow-right ml-1"></i> العودة
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Items + customer info --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border p-6">
                <h2 class="font-bold text-slate-800 mb-4">منتجات الإرجاع</h2>
                <table class="w-full text-sm">
                    <thead class="text-xs text-slate-500 uppercase border-b">
                        <tr>
                            <th class="py-2 text-right">المنتج</th>
                            <th class="py-2 text-right">الكمية</th>
                            <th class="py-2 text-right">السعر</th>
                            <th class="py-2 text-right">الإجمالي</th>
                            <th class="py-2 text-right">إعادة للمخزون</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($return->items as $item)
                            <tr>
                                <td class="py-3 font-medium text-slate-800">{{ $item->product?->name ?? '(محذوف)' }}</td>
                                <td class="py-3 text-slate-600">{{ $item->quantity }}</td>
                                <td class="py-3 text-slate-600">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="py-3 font-semibold text-slate-800">{{ number_format($item->line_total, 2) }}</td>
                                <td class="py-3">
                                    @if($item->restock)
                                        <span class="text-emerald-600 text-xs font-bold">✓ نعم</span>
                                    @else
                                        <span class="text-slate-400 text-xs">لا</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t-2 border-slate-200">
                        <tr>
                            <td colspan="3" class="py-3 text-right font-bold">إجمالي الاسترداد</td>
                            <td colspan="2" class="py-3 font-bold text-emerald-600 text-lg">{{ number_format($return->refund_amount, 2) }} ج.م</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border p-6">
                <h2 class="font-bold text-slate-800 mb-3">سبب الإرجاع</h2>
                <p class="text-slate-700 font-semibold mb-2">{{ $return->reasonLabel() }}</p>
                @if($return->customer_note)
                    <div class="bg-slate-50 rounded-xl p-4 mt-3">
                        <p class="text-xs text-slate-500 mb-1 font-semibold">ملاحظة العميل:</p>
                        <p class="text-sm text-slate-700">{{ $return->customer_note }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Status panel --}}
        <div class="space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border p-6">
                <h2 class="font-bold text-slate-800 mb-4">الحالة الحالية</h2>
                <span class="inline-block px-4 py-2 rounded-full text-sm font-bold bg-{{ $return->statusColor() }}-100 text-{{ $return->statusColor() }}-700">
                    {{ $return->statusLabel() }}
                </span>

                <form method="POST" action="{{ route('admin.returns.status', $return) }}" class="mt-6 space-y-4">
                    @csrf @method('PATCH')
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">تغيير إلى</label>
                        <select name="status" class="w-full rounded-xl border-slate-200 text-sm">
                            @foreach(\App\Models\ReturnRequest::STATUSES as $s)
                                <option value="{{ $s }}" @selected($return->status === $s)>{{ (new \App\Models\ReturnRequest(['status'=>$s]))->statusLabel() }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-amber-600 mt-1">عند تحديد "تم الاستلام" ستتم إعادة الكميات للمخزون تلقائياً.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">مبلغ الاسترداد</label>
                        <input type="number" step="0.01" min="0" name="refund_amount" value="{{ $return->refund_amount }}" class="w-full rounded-xl border-slate-200 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">ملاحظة إدارية</label>
                        <textarea name="admin_note" rows="3" class="w-full rounded-xl border-slate-200 text-sm">{{ $return->admin_note }}</textarea>
                    </div>
                    <button class="w-full px-4 py-2 rounded-xl bg-violet-600 text-white font-semibold hover:bg-violet-700">حفظ التغييرات</button>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border p-6 text-sm">
                <h3 class="font-bold text-slate-800 mb-3">الجدول الزمني</h3>
                <ul class="space-y-2 text-slate-600">
                    <li>تم الإنشاء: <span class="font-semibold">{{ $return->created_at->format('Y-m-d H:i') }}</span></li>
                    @if($return->approved_at)<li>تم القبول: <span class="font-semibold">{{ $return->approved_at->format('Y-m-d H:i') }}</span></li>@endif
                    @if($return->received_at)<li>تم الاستلام: <span class="font-semibold">{{ $return->received_at->format('Y-m-d H:i') }}</span></li>@endif
                    @if($return->refunded_at)<li>تم الاسترداد: <span class="font-semibold">{{ $return->refunded_at->format('Y-m-d H:i') }}</span></li>@endif
                </ul>
            </div>

            {{-- Aramex pickup --}}
            <div class="bg-white rounded-2xl shadow-sm border p-6">
                <h3 class="font-bold text-slate-800 mb-3 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-orange-500 text-white flex items-center justify-center text-xs font-bold">A</span>
                    استلام Aramex
                </h3>
                @if($return->pickup_guid)
                    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 text-xs space-y-1">
                        <p class="font-bold text-emerald-700">✓ تمت جدولة الاستلام</p>
                        <p>رقم: <span class="font-mono font-bold">{{ $return->pickup_reference }}</span></p>
                        <p>الوقت: {{ $return->pickup_scheduled_at?->format('Y-m-d H:i') }}</p>
                    </div>
                @else
                    <p class="text-xs text-slate-500 mb-3">جدولة استلام الشحنة من العميل عبر Aramex.</p>
                    <form method="POST" action="{{ route('admin.returns.aramex-pickup', $return) }}" onsubmit="return confirm('جدولة استلام Aramex الآن؟')">
                        @csrf
                        <button class="w-full px-4 py-2 rounded-xl bg-orange-600 text-white font-semibold hover:bg-orange-700 text-sm">
                            <i class="fa-solid fa-truck-pickup ml-2"></i> جدولة استلام
                        </button>
                    </form>
                @endif
                @if(session('error'))
                    <p class="mt-3 text-xs text-rose-600">{{ session('error') }}</p>
                @endif
            </div>

            <form method="POST" action="{{ route('admin.returns.destroy', $return) }}" onsubmit="return confirm('حذف نهائي؟')">
                @csrf @method('DELETE')
                <button class="w-full px-4 py-2 rounded-xl bg-rose-50 text-rose-700 font-semibold hover:bg-rose-100">
                    <i class="fa-solid fa-trash ml-2"></i> حذف هذا الطلب
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
