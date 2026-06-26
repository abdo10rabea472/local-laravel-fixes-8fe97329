@extends('admin.layouts.app')

@section('title', 'تقارير المخزون')

@section('content')
<div class="p-6 space-y-6">
    <h1 class="text-2xl font-bold text-slate-800">تقارير المخزون</h1>

    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
        @foreach([
            ['إجمالي المنتجات', $stats['total'], 'box', 'sky'],
            ['وحدات في المخزون', number_format($stats['units']), 'boxes-stacked', 'indigo'],
            ['قيمة المخزون', number_format($stats['value'],0) . ' ج.م', 'sack-dollar', 'emerald'],
            ['منخفض المخزون', $stats['low'], 'triangle-exclamation', 'amber'],
            ['نفذ المخزون', $stats['out'], 'circle-xmark', 'rose'],
            ['حركات (30 يوم)', $stats['movements_30d'], 'arrows-rotate', 'violet'],
        ] as [$lbl,$val,$icon,$c])
        <div class="bg-white border rounded-2xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500">{{ $lbl }}</p>
                    <h4 class="text-xl font-black text-{{ $c }}-600 mt-1">{{ $val }}</h4>
                </div>
                <i class="fa-solid fa-{{ $icon }} text-{{ $c }}-300 text-2xl"></i>
            </div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white border rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b flex items-center justify-between">
                <h3 class="font-bold text-slate-800"><i class="fa-solid fa-triangle-exclamation text-amber-500"></i> منخفض المخزون</h3>
                <a href="{{ route('admin.stock.index', ['filter'=>'low']) }}" class="text-violet-600 text-xs font-bold">إدارة →</a>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr><th class="px-4 py-2 text-right">المنتج</th><th class="px-4 py-2 text-right">الكود</th><th class="px-4 py-2 text-right">المخزون</th><th class="px-4 py-2 text-right">الحد</th></tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($low as $p)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-slate-700">{{ $p->name }}</td>
                            <td class="px-4 py-3 text-slate-500 text-xs">{{ $p->sku ?: '—' }}</td>
                            <td class="px-4 py-3 font-bold text-amber-600">{{ $p->stock }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $p->low_stock_threshold }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-8 text-slate-400">لا توجد منتجات منخفضة المخزون 🎉</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t">{{ $low->links() }}</div>
        </div>

        <div class="bg-white border rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b flex items-center justify-between">
                <h3 class="font-bold text-slate-800"><i class="fa-solid fa-circle-xmark text-rose-500"></i> نفذ من المخزون</h3>
                <a href="{{ route('admin.stock.index', ['filter'=>'out']) }}" class="text-violet-600 text-xs font-bold">إدارة →</a>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr><th class="px-4 py-2 text-right">المنتج</th><th class="px-4 py-2 text-right">الكود</th><th class="px-4 py-2 text-right">التصنيف</th></tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($out as $p)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-slate-700">{{ $p->name }}</td>
                            <td class="px-4 py-3 text-slate-500 text-xs">{{ $p->sku ?: '—' }}</td>
                            <td class="px-4 py-3 text-slate-500 text-xs">{{ $p->category?->name }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center py-8 text-slate-400">لا توجد منتجات نافدة 🎉</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t">{{ $out->links() }}</div>
        </div>
    </div>

    <div class="bg-white border rounded-2xl p-6">
        <h3 class="font-bold text-slate-800 mb-4"><i class="fa-solid fa-fire text-orange-500"></i> الأكثر حركة (30 يوم)</h3>
        <table class="w-full text-sm">
            <thead class="text-xs uppercase text-slate-500 border-b">
                <tr><th class="py-2 text-right">المنتج</th><th class="py-2 text-right">الكود</th><th class="py-2 text-right">المخزون الحالي</th><th class="py-2 text-right">إجمالي الحركة</th></tr>
            </thead>
            <tbody class="divide-y">
                @forelse($topMovers as $m)
                    <tr>
                        <td class="py-3 font-semibold text-slate-700">{{ $m->product?->name ?? '—' }}</td>
                        <td class="py-3 text-slate-500 text-xs">{{ $m->product?->sku ?: '—' }}</td>
                        <td class="py-3">{{ $m->product?->stock ?? 0 }}</td>
                        <td class="py-3 font-bold text-violet-600">{{ (int) $m->movement }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center py-8 text-slate-400">لا توجد حركات</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
