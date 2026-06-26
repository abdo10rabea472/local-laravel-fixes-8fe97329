@extends('admin.layouts.app')

@section('title', 'تقارير الكوبونات')

@section('content')
<div class="p-6 space-y-6">
    <h1 class="text-2xl font-bold text-slate-800">تقارير الكوبونات</h1>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white border rounded-2xl p-5">
            <p class="text-xs font-bold text-slate-500">إجمالي مرات الاستخدام</p>
            <h3 class="text-2xl font-black text-violet-600 mt-2">{{ (int) $totals->redemptions }}</h3>
        </div>
        <div class="bg-white border rounded-2xl p-5">
            <p class="text-xs font-bold text-slate-500">إجمالي الخصومات المُمنوحة</p>
            <h3 class="text-2xl font-black text-rose-600 mt-2">{{ number_format((float) $totals->discount, 2) }} <span class="text-xs">ج.م</span></h3>
        </div>
        <div class="bg-white border rounded-2xl p-5">
            <p class="text-xs font-bold text-slate-500">إيرادات الطلبات المرتبطة</p>
            <h3 class="text-2xl font-black text-emerald-600 mt-2">{{ number_format((float) $totals->revenue, 2) }} <span class="text-xs">ج.م</span></h3>
        </div>
    </div>

    <div class="bg-white border rounded-2xl overflow-hidden">
        <div class="px-5 py-4 border-b">
            <h3 class="font-bold text-slate-800">جميع الكوبونات</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-2 text-right">الكود</th>
                    <th class="px-4 py-2 text-right">القيمة</th>
                    <th class="px-4 py-2 text-right">الاستخدامات</th>
                    <th class="px-4 py-2 text-right">إجمالي الخصم</th>
                    <th class="px-4 py-2 text-right">إيرادات الطلبات</th>
                    <th class="px-4 py-2 text-right">الحالة</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($coupons as $c)
                    <tr>
                        <td class="px-4 py-3 font-mono font-bold text-slate-800">{{ $c->code }}</td>
                        <td class="px-4 py-3">
                            @if($c->type === 'percent')
                                {{ rtrim(rtrim(number_format($c->value, 2), '0'), '.') }}%
                            @else
                                {{ number_format($c->value, 2) }} ج.م
                            @endif
                        </td>
                        <td class="px-4 py-3 font-bold text-violet-600">{{ $c->redemptions_count }} {{ $c->usage_limit ? "/ {$c->usage_limit}" : '' }}</td>
                        <td class="px-4 py-3 text-rose-600 font-semibold">{{ number_format((float) ($c->total_discount ?? 0), 2) }}</td>
                        <td class="px-4 py-3 text-emerald-600 font-semibold">{{ number_format((float) ($c->total_revenue ?? 0), 2) }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-[10px] font-bold {{ $c->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $c->is_active ? 'مفعّل' : 'موقوف' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-12 text-slate-400">لا توجد كوبونات</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t">{{ $coupons->links() }}</div>
    </div>
</div>
@endsection
