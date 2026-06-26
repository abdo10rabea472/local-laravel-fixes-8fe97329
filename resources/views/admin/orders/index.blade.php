@extends('admin.layouts.app')
@section('title', 'الطلبات')

@section('content')
<div class="p-6 max-w-7xl mx-auto" x-data="ordersIndex()">
    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900">إدارة الطلبات</h1>
            <p class="text-sm text-slate-500 mt-1">عرض وتعديل وتتبع حالة جميع الطلبات.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-6 gap-3 mb-6">
        @php $cards = [
            ['الكل', $stats['total'], 'slate'],
            ['قيد الانتظار', $stats['pending'], 'amber'],
            ['مدفوع', $stats['paid'], 'sky'],
            ['تم الشحن', $stats['shipped'], 'indigo'],
            ['تم التوصيل', $stats['delivered'], 'emerald'],
            ['الإيرادات', number_format($stats['revenue'], 0).' EGP', 'violet'],
        ]; @endphp
        @foreach($cards as [$lbl,$val,$c])
            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <p class="text-xs text-slate-500 mb-1">{{ $lbl }}</p>
                <p class="text-xl font-black text-{{ $c }}-600">{{ $val }}</p>
            </div>
        @endforeach
    </div>

    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-2 mb-4">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="بحث (رقم الطلب / إيميل / هاتف)..." class="h-10 px-3 border border-slate-200 rounded-xl text-sm md:col-span-2">
        <select name="status" class="h-10 px-3 border border-slate-200 rounded-xl text-sm">
            <option value="">كل الحالات</option>
            @foreach(['pending'=>'قيد الانتظار','paid'=>'مدفوع','shipped'=>'تم الشحن','delivered'=>'تم التوصيل','cancelled'=>'ملغي','refunded'=>'مسترد'] as $k=>$v)
                <option value="{{ $k }}" @selected(request('status')===$k)>{{ $v }}</option>
            @endforeach
        </select>
        <input type="date" name="from" value="{{ request('from') }}" class="h-10 px-3 border border-slate-200 rounded-xl text-sm">
        <input type="date" name="to" value="{{ request('to') }}" class="h-10 px-3 border border-slate-200 rounded-xl text-sm">
        <button class="h-10 px-4 bg-slate-900 text-white rounded-xl text-sm font-bold md:col-span-5">فلتر</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm min-w-[800px]">
            <thead class="bg-slate-50 text-slate-600 text-xs">
                <tr>
                    <th class="p-3 text-right">رقم الطلب</th>
                    <th class="p-3">العميل</th>
                    <th class="p-3">العناصر</th>
                    <th class="p-3">الإجمالي</th>
                    <th class="p-3">الحالة</th>
                    <th class="p-3">التاريخ</th>
                    <th class="p-3">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $o)
                <tr class="border-t border-slate-100" data-row="{{ $o->id }}">
                    <td class="p-3 font-mono font-bold text-violet-700">{{ $o->order_number }}</td>
                    <td class="p-3 text-center text-xs">
                        <div class="font-semibold text-slate-800">{{ $o->customer_name ?: '—' }}</div>
                        <div class="text-slate-500">{{ $o->email }}</div>
                    </td>
                    <td class="p-3 text-center">{{ $o->items->count() }}</td>
                    <td class="p-3 text-center font-bold">{{ number_format($o->total, 2) }} {{ $o->currency }}</td>
                    <td class="p-3 text-center">
                        @php $c = $o->statusBadgeColor(); @endphp
                        <span class="px-2 py-1 text-xs rounded-full font-bold bg-{{ $c }}-50 text-{{ $c }}-700">{{ $o->statusLabel() }}</span>
                    </td>
                    <td class="p-3 text-center text-xs text-slate-500">{{ $o->created_at->format('Y-m-d H:i') }}</td>
                    <td class="p-3 text-center">
                        <a href="{{ route('admin.orders.show', $o) }}" class="text-violet-600 hover:underline font-bold text-xs">عرض</a>
                        <a href="{{ route('admin.orders.invoice', $o) }}" target="_blank" class="text-slate-600 hover:underline font-bold text-xs mr-2">فاتورة</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="p-8 text-center text-slate-400">لا توجد طلبات.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
</div>

<script>
function ordersIndex(){ return {}; }
</script>
@endsection
