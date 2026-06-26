@extends('admin.layouts.app')

@section('title', 'إدارة المرتجعات')

@section('content')
<div class="p-6 space-y-6">
    <h1 class="text-2xl font-bold text-slate-800">المرتجعات (RMA)</h1>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border p-5">
            <p class="text-xs text-slate-500 font-semibold">الإجمالي</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border p-5">
            <p class="text-xs text-amber-600 font-semibold">قيد المراجعة</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border p-5">
            <p class="text-xs text-sky-600 font-semibold">مقبول</p>
            <p class="text-2xl font-bold text-sky-600 mt-1">{{ $stats['approved'] }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border p-5">
            <p class="text-xs text-emerald-600 font-semibold">تم الاسترداد</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['refunded'] }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border p-5">
            <p class="text-xs text-emerald-600 font-semibold">إجمالي المسترد</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($stats['total_refunded'], 2) }} ج.م</p>
        </div>
    </div>

    <form method="GET" class="bg-white rounded-2xl shadow-sm border p-4 flex flex-wrap items-center gap-3">
        <input type="text" name="q" value="{{ $search }}" placeholder="ابحث برقم RMA أو الطلب أو الإيميل..." class="flex-1 min-w-[200px] rounded-xl border-slate-200 text-sm">
        <select name="status" class="rounded-xl border-slate-200 text-sm">
            <option value="">كل الحالات</option>
            @foreach(\App\Models\ReturnRequest::STATUSES as $s)
                <option value="{{ $s }}" @selected($status === $s)>{{ (new \App\Models\ReturnRequest(['status'=>$s]))->statusLabel() }}</option>
            @endforeach
        </select>
        <button class="px-5 py-2 rounded-xl bg-violet-600 text-white text-sm font-semibold hover:bg-violet-700">فلترة</button>
    </form>

    <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-right">رقم RMA</th>
                        <th class="px-4 py-3 text-right">الطلب</th>
                        <th class="px-4 py-3 text-right">العميل</th>
                        <th class="px-4 py-3 text-right">المبلغ</th>
                        <th class="px-4 py-3 text-right">الحالة</th>
                        <th class="px-4 py-3 text-right">التاريخ</th>
                        <th class="px-4 py-3 text-right">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($returns as $r)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono font-semibold text-slate-800">{{ $r->rma_number }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.orders.show', $r->order_id) }}" class="text-violet-600 font-semibold hover:underline">{{ $r->order?->order_number }}</a>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $r->user?->name ?? $r->order?->email }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ number_format($r->refund_amount, 2) }} ج.م</td>
                            <td class="px-4 py-3">
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-{{ $r->statusColor() }}-100 text-{{ $r->statusColor() }}-700">{{ $r->statusLabel() }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-500 text-xs whitespace-nowrap">{{ $r->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.returns.show', $r) }}" class="px-3 py-1 rounded-lg bg-violet-100 text-violet-700 text-xs font-semibold hover:bg-violet-200">إدارة</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-12 text-slate-400">لا توجد طلبات إرجاع</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t">{{ $returns->links() }}</div>
    </div>
</div>
@endsection
