@extends('admin.layouts.app')

@section('title', 'إدارة المخزون')

@section('content')
<div class="p-6 space-y-6" x-data="stockManager()">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">إدارة المخزون</h1>
            <p class="text-sm text-slate-500 mt-1">تعديل الكميات، تحديد حدود التنبيه، وتعديل جماعي</p>
        </div>
        <a href="{{ route('admin.stock.history') }}" class="px-4 py-2 rounded-xl bg-slate-800 text-white text-sm font-semibold hover:bg-slate-900">
            <i class="fa-solid fa-clock-rotate-left ml-2"></i> سجل التغييرات
        </a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border p-5">
            <p class="text-xs text-slate-500 font-semibold">إجمالي المنتجات</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border p-5">
            <p class="text-xs text-rose-500 font-semibold">نفذ المخزون</p>
            <p class="text-2xl font-bold text-rose-600 mt-1">{{ number_format($stats['out']) }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border p-5">
            <p class="text-xs text-amber-600 font-semibold">منخفض</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($stats['low']) }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border p-5">
            <p class="text-xs text-emerald-600 font-semibold">قيمة المخزون</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($stats['value'], 2) }} ج.م</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-2xl shadow-sm border p-4 flex flex-wrap items-center gap-3">
        <input type="text" name="q" value="{{ $search }}" placeholder="ابحث بالاسم أو SKU..." class="flex-1 min-w-[200px] rounded-xl border-slate-200 text-sm">
        <select name="filter" class="rounded-xl border-slate-200 text-sm">
            <option value="">كل المنتجات</option>
            <option value="low" @selected($filter==='low')>منخفض المخزون</option>
            <option value="out" @selected($filter==='out')>نفذ المخزون</option>
        </select>
        <button class="px-5 py-2 rounded-xl bg-violet-600 text-white text-sm font-semibold hover:bg-violet-700">فلترة</button>
    </form>

    {{-- Bulk save bar --}}
    <div x-show="dirty.length > 0" x-transition class="sticky top-2 z-10 bg-violet-600 text-white rounded-2xl shadow-xl p-4 flex items-center justify-between">
        <span class="font-semibold text-sm">لديك <span x-text="dirty.length"></span> تغييرات غير محفوظة</span>
        <div class="flex gap-2">
            <button @click="reset()" class="px-4 py-2 rounded-lg bg-white/20 text-white text-sm font-semibold hover:bg-white/30">إلغاء</button>
            <button @click="saveBulk()" :disabled="saving" class="px-5 py-2 rounded-lg bg-white text-violet-700 text-sm font-bold hover:bg-violet-50 disabled:opacity-50">
                <span x-show="!saving">حفظ الكل</span>
                <span x-show="saving">جارٍ الحفظ...</span>
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-right">المنتج</th>
                        <th class="px-4 py-3 text-right">SKU</th>
                        <th class="px-4 py-3 text-right">الفئة</th>
                        <th class="px-4 py-3 text-right">السعر</th>
                        <th class="px-4 py-3 text-right">المخزون</th>
                        <th class="px-4 py-3 text-right">حد التنبيه</th>
                        <th class="px-4 py-3 text-right">الحالة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $p)
                        @php
                            $status = $p->stock == 0 ? 'out' : (($p->low_stock_threshold && $p->stock <= $p->low_stock_threshold) ? 'low' : 'ok');
                            $statusMap = ['out' => ['bg-rose-100 text-rose-700', 'نفذ'], 'low' => ['bg-amber-100 text-amber-700', 'منخفض'], 'ok' => ['bg-emerald-100 text-emerald-700', 'متوفر']];
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $p->name }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $p->sku ?: '—' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $p->category->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ number_format($p->sale_price ?? $p->price, 2) }}</td>
                            <td class="px-4 py-3">
                                <input type="number" min="0" 
                                    :data-original="{{ $p->stock }}"
                                    value="{{ $p->stock }}"
                                    @input="markDirty({{ $p->id }}, $event.target.value, 'stock')"
                                    class="w-24 rounded-lg border-slate-200 text-sm">
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" min="0" 
                                    value="{{ $p->low_stock_threshold }}"
                                    @input="markDirty({{ $p->id }}, $event.target.value, 'threshold')"
                                    class="w-20 rounded-lg border-slate-200 text-sm">
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $statusMap[$status][0] }}">{{ $statusMap[$status][1] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-12 text-slate-400">لا توجد منتجات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t">{{ $products->links() }}</div>
    </div>
</div>

<script>
function stockManager() {
    return {
        dirty: [],
        saving: false,
        markDirty(id, value, field) {
            const idx = this.dirty.findIndex(d => d.id === id);
            if (idx >= 0) {
                this.dirty[idx][field] = value;
            } else {
                this.dirty.push({ id, [field]: value });
            }
        },
        reset() { location.reload(); },
        async saveBulk() {
            this.saving = true;
            const updates = this.dirty.filter(d => d.stock !== undefined).map(d => ({ id: d.id, stock: parseInt(d.stock) || 0 }));
            try {
                const res = await fetch('{{ route('admin.stock.bulk-update') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ updates, note: 'تحديث جماعي من اللوحة' })
                });
                const data = await res.json();
                if (data.ok) {
                    alert(`تم تحديث ${data.changed} منتج`);
                    location.reload();
                } else throw new Error('فشل');
            } catch (e) { alert('فشل الحفظ'); }
            finally { this.saving = false; }
        }
    };
}
</script>
@endsection
