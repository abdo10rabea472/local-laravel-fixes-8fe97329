@extends('account.layout')
@section('account_content')
<h1 class="text-2xl font-black text-slate-900 mb-6">مرحباً، {{ $user->name }} 👋</h1>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <div class="bg-white p-5 rounded-2xl border border-slate-200">
        <p class="text-xs text-slate-500">إجمالي الطلبات</p>
        <p class="text-3xl font-black text-violet-600 mt-1">{{ $ordersCount }}</p>
    </div>
    <div class="bg-white p-5 rounded-2xl border border-slate-200">
        <p class="text-xs text-slate-500">إجمالي الإنفاق</p>
        <p class="text-3xl font-black text-emerald-600 mt-1">{{ number_format($totalSpent, 0) }} <span class="text-sm">EGP</span></p>
    </div>
    <div class="bg-white p-5 rounded-2xl border border-slate-200">
        <p class="text-xs text-slate-500">مراجعاتي</p>
        <p class="text-3xl font-black text-amber-600 mt-1">{{ $reviewsCount }}</p>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
        <h3 class="font-bold">آخر الطلبات</h3>
        <a href="{{ route('account.orders') }}" class="text-xs text-violet-600 font-bold">عرض الكل ←</a>
    </div>
    @if($recentOrders->count())
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-xs text-slate-600"><tr>
            <th class="p-3 text-right">رقم الطلب</th><th class="p-3">العناصر</th><th class="p-3">الإجمالي</th><th class="p-3">الحالة</th><th class="p-3">التاريخ</th><th class="p-3"></th>
        </tr></thead>
        <tbody>
            @foreach($recentOrders as $o)
            <tr class="border-t border-slate-100">
                <td class="p-3 font-mono font-bold text-violet-700">{{ $o->order_number }}</td>
                <td class="p-3 text-center">{{ $o->items_count }}</td>
                <td class="p-3 text-center font-bold">{{ number_format($o->total, 2) }}</td>
                <td class="p-3 text-center"><span class="text-xs px-2 py-1 rounded-full bg-{{ $o->statusBadgeColor() }}-50 text-{{ $o->statusBadgeColor() }}-700 font-bold">{{ $o->statusLabel() }}</span></td>
                <td class="p-3 text-center text-xs text-slate-500">{{ $o->created_at->format('Y-m-d') }}</td>
                <td class="p-3 text-center"><a href="{{ route('account.orders.show', $o) }}" class="text-violet-600 text-xs font-bold">تفاصيل</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="p-10 text-center text-slate-400">لا توجد طلبات بعد. <a href="{{ route('products.index') }}" class="text-violet-600 font-bold">تسوق الآن</a></div>
    @endif
</div>
@endsection
