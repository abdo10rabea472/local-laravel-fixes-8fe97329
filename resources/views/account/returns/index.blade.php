@extends('account.layout')

@section('account-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-800">مرتجعاتي</h1>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-2xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3 text-right">رقم RMA</th>
                    <th class="px-4 py-3 text-right">الطلب</th>
                    <th class="px-4 py-3 text-right">المبلغ</th>
                    <th class="px-4 py-3 text-right">الحالة</th>
                    <th class="px-4 py-3 text-right">التاريخ</th>
                    <th class="px-4 py-3 text-right"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($returns as $r)
                    <tr>
                        <td class="px-4 py-3 font-mono font-semibold text-slate-800">{{ $r->rma_number }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $r->order?->order_number }}</td>
                        <td class="px-4 py-3 font-semibold">{{ number_format($r->refund_amount, 2) }} ج.م</td>
                        <td class="px-4 py-3">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-{{ $r->statusColor() }}-100 text-{{ $r->statusColor() }}-700">{{ $r->statusLabel() }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-500 text-xs">{{ $r->created_at->format('Y-m-d') }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('account.returns.show', $r) }}" class="text-violet-600 font-semibold hover:underline text-xs">عرض</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-12 text-slate-400">
                        لا توجد مرتجعات. يمكنك طلب إرجاع من <a href="{{ route('account.orders') }}" class="text-violet-600 font-semibold">صفحة طلباتك</a>.
                    </td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t">{{ $returns->links() }}</div>
    </div>
</div>
@endsection
