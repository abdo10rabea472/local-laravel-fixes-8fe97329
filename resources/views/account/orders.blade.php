@extends('account.layout')
@section('account_content')
<h1 class="text-2xl font-black text-slate-900 mb-6">طلباتي</h1>
<div class="bg-white rounded-2xl border border-slate-200 overflow-x-auto">
    <table class="w-full text-sm min-w-[700px]">
        <thead class="bg-slate-50 text-xs text-slate-600"><tr>
            <th class="p-3 text-right">رقم الطلب</th><th class="p-3">العناصر</th><th class="p-3">الإجمالي</th><th class="p-3">الحالة</th><th class="p-3">التاريخ</th><th class="p-3"></th>
        </tr></thead>
        <tbody>
            @forelse($orders as $o)
            <tr class="border-t border-slate-100">
                <td class="p-3 font-mono font-bold text-violet-700">{{ $o->order_number }}</td>
                <td class="p-3 text-center">{{ $o->items_count }}</td>
                <td class="p-3 text-center font-bold">{{ number_format($o->total, 2) }} {{ $o->currency }}</td>
                <td class="p-3 text-center"><span class="text-xs px-2 py-1 rounded-full bg-{{ $o->statusBadgeColor() }}-50 text-{{ $o->statusBadgeColor() }}-700 font-bold">{{ $o->statusLabel() }}</span></td>
                <td class="p-3 text-center text-xs text-slate-500">{{ $o->created_at->format('Y-m-d H:i') }}</td>
                <td class="p-3 text-center"><a href="{{ route('account.orders.show', $o) }}" class="text-violet-600 text-xs font-bold">تفاصيل</a></td>
            </tr>
            @empty
            <tr><td colspan="6" class="p-10 text-center text-slate-400">لا توجد طلبات.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $orders->links() }}</div>
@endsection
