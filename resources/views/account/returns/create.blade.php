@extends('account.layout')

@section('account-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-800">طلب إرجاع للطلب #{{ $order->order_number }}</h1>
        <a href="{{ route('account.orders.show', $order) }}" class="text-violet-600 text-sm font-semibold">← العودة</a>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700">
            @foreach($errors->all() as $e) <p>{{ $e }}</p> @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('account.returns.store', $order) }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-2xl border p-6">
            <h2 class="font-bold text-slate-800 mb-4">اختر المنتجات والكميات</h2>
            <table class="w-full text-sm">
                <thead class="text-xs text-slate-500 uppercase border-b">
                    <tr>
                        <th class="py-2 text-right">تضمين</th>
                        <th class="py-2 text-right">المنتج</th>
                        <th class="py-2 text-right">السعر</th>
                        <th class="py-2 text-right">الكمية المتاحة</th>
                        <th class="py-2 text-right">كمية الإرجاع</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($order->items as $i => $item)
                        <tr>
                            <td class="py-3">
                                <input type="hidden" name="items[{{ $i }}][order_item_id]" value="{{ $item->id }}">
                                <input type="checkbox" name="items[{{ $i }}][include]" value="1" class="w-4 h-4" onchange="this.closest('tr').querySelector('.qty').disabled = !this.checked">
                            </td>
                            <td class="py-3 font-medium text-slate-800">{{ $item->product_name }}</td>
                            <td class="py-3 text-slate-600">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-3 text-slate-600">{{ $item->quantity }}</td>
                            <td class="py-3">
                                <input type="number" name="items[{{ $i }}][quantity]" min="1" max="{{ $item->quantity }}" value="{{ $item->quantity }}" disabled class="qty w-20 rounded-lg border-slate-200 text-sm disabled:bg-slate-100">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="text-xs text-amber-600 mt-3">⚠️ أرسل فقط المنتجات التي تود إرجاعها — التي لم تختر "تضمين" لن تُحسب.</p>
        </div>

        <div class="bg-white rounded-2xl border p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">سبب الإرجاع *</label>
                <select name="reason" required class="w-full rounded-xl border-slate-200 text-sm">
                    <option value="defective">منتج معيب</option>
                    <option value="wrong_item">منتج خاطئ</option>
                    <option value="not_as_described">مخالف للوصف</option>
                    <option value="damaged">تالف عند الاستلام</option>
                    <option value="no_longer_wanted">لم أعد أرغب به</option>
                    <option value="other">أخرى</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">ملاحظات إضافية</label>
                <textarea name="customer_note" rows="4" class="w-full rounded-xl border-slate-200 text-sm" placeholder="اشرح تفاصيل المشكلة..."></textarea>
            </div>
        </div>

        <button onclick="return confirmSubmit(event)" class="w-full px-6 py-3 rounded-xl bg-violet-600 text-white font-bold hover:bg-violet-700">
            إرسال طلب الإرجاع
        </button>
    </form>
</div>

<script>
function confirmSubmit(e) {
    const form = e.target.closest('form');
    // Remove unchecked rows so backend validation gets a clean list
    const rows = form.querySelectorAll('tbody tr');
    rows.forEach((r, idx) => {
        const cb = r.querySelector('input[type=checkbox]');
        if (!cb.checked) {
            r.querySelectorAll('input').forEach(i => i.disabled = true);
        }
    });
    const anyChecked = Array.from(form.querySelectorAll('input[type=checkbox]')).some(c => c.checked);
    if (!anyChecked) { alert('اختر منتج واحد على الأقل'); e.preventDefault(); return false; }
    return confirm('تأكيد إرسال طلب الإرجاع؟');
}
</script>
@endsection
