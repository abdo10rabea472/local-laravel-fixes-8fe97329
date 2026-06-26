@extends('admin.layouts.app')

@section('title', 'سجل المخزون')

@section('content')
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-800">سجل تغييرات المخزون</h1>
        <a href="{{ route('admin.stock.index') }}" class="text-violet-600 hover:underline text-sm font-semibold">
            <i class="fa-solid fa-arrow-right ml-1"></i> العودة للمخزون
        </a>
    </div>

    <form method="GET" class="bg-white rounded-2xl shadow-sm border p-4 flex flex-wrap items-center gap-3">
        <select name="product_id" class="rounded-xl border-slate-200 text-sm min-w-[200px]">
            <option value="">كل المنتجات</option>
            @foreach($products as $p)
                <option value="{{ $p->id }}" @selected($productId == $p->id)>{{ $p->name }}</option>
            @endforeach
        </select>
        <select name="type" class="rounded-xl border-slate-200 text-sm">
            <option value="">كل أنواع التغييرات</option>
            @foreach(['manual' => 'تعديل يدوي', 'order' => 'طلب جديد', 'order_cancel' => 'إلغاء طلب', 'return' => 'مرتجع', 'adjustment' => 'تسوية', 'bulk_update' => 'تحديث جماعي'] as $k => $v)
                <option value="{{ $k }}" @selected($type === $k)>{{ $v }}</option>
            @endforeach
        </select>
        <button class="px-5 py-2 rounded-xl bg-violet-600 text-white text-sm font-semibold hover:bg-violet-700">فلترة</button>
    </form>

    <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-right">التاريخ</th>
                        <th class="px-4 py-3 text-right">المنتج</th>
                        <th class="px-4 py-3 text-right">النوع</th>
                        <th class="px-4 py-3 text-right">التغيير</th>
                        <th class="px-4 py-3 text-right">قبل</th>
                        <th class="px-4 py-3 text-right">بعد</th>
                        <th class="px-4 py-3 text-right">ملاحظة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($movements as $m)
                        <tr>
                            <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $m->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3 font-medium text-slate-700">{{ $m->product?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-{{ $m->typeColor() }}-100 text-{{ $m->typeColor() }}-700">{{ $m->typeLabel() }}</span>
                            </td>
                            <td class="px-4 py-3 font-bold {{ $m->quantity_change > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $m->quantity_change > 0 ? '+' : '' }}{{ $m->quantity_change }}
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $m->stock_before }}</td>
                            <td class="px-4 py-3 text-slate-700 font-semibold">{{ $m->stock_after }}</td>
                            <td class="px-4 py-3 text-slate-500 text-xs">{{ $m->note ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-12 text-slate-400">لا توجد حركات مسجلة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t">{{ $movements->links() }}</div>
    </div>
</div>
@endsection
