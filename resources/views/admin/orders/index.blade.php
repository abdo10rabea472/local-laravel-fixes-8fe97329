@extends('admin.layouts.app')
@section('title', 'الطلبات')

@section('content')
<x-admin.page title="إدارة الطلبات" subtitle="عرض وتعديل وتتبع حالة جميع الطلبات.">
    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
        @php $cards = [
            ['الكل', $stats['total'], 'gray', 'fa-list'],
            ['قيد الانتظار', $stats['pending'], 'amber', 'fa-clock'],
            ['مدفوع', $stats['paid'], 'sky', 'fa-credit-card'],
            ['تم الشحن', $stats['shipped'], 'indigo', 'fa-truck'],
            ['تم التوصيل', $stats['delivered'], 'emerald', 'fa-check'],
            ['الإيرادات', number_format($stats['revenue'], 0).' EGP', 'primary', 'fa-coins'],
        ]; @endphp
        @foreach($cards as [$lbl,$val,$c,$ic])
            <div class="bg-white dark:bg-dark-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $lbl }}</p>
                    <p class="text-lg font-black text-{{ $c }}-600">{{ $val }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-{{ $c }}-50 dark:bg-{{ $c }}-950/30 grid place-items-center">
                    <i class="fas {{ $ic }} text-{{ $c }}-600"></i>
                </div>
            </div>
        @endforeach
    </div>

    <x-admin.card title="قائمة الطلبات" icon="fa-shopping-cart" padding="p-0">
        {{-- Bulk + Export toolbar --}}
        <form method="POST" action="{{ route('admin.orders.bulk-status') }}" id="bulk-orders-form">
            @csrf
            <div class="flex flex-wrap items-center gap-2 p-3 border-b border-gray-100 dark:border-gray-800 bg-gray-50/60 dark:bg-dark-800/40">
                <select name="status" class="h-10 px-3 bg-white dark:bg-dark-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs">
                    <option value="">— تغيير حالة جماعي —</option>
                    @foreach(['pending'=>'قيد الانتظار','paid'=>'مدفوع','shipped'=>'تم الشحن','delivered'=>'تم التوصيل','cancelled'=>'ملغي','refunded'=>'مسترد'] as $k=>$v)
                        <option value="{{ $k }}">{{ $v }}</option>
                    @endforeach
                </select>
                <label class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                    <input type="checkbox" name="notify" value="1" class="rounded"> إرسال إيميل للعميل
                </label>
                <button type="submit" onclick="return confirm('تطبيق الإجراء على الطلبات المحددة؟')"
                        class="h-10 px-4 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-lg">
                    <i class="fas fa-check ml-1"></i> تطبيق
                </button>
                <div class="flex-1"></div>
                <a href="{{ route('admin.orders.export', request()->query()) }}"
                   class="h-10 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg inline-flex items-center gap-1">
                    <i class="fas fa-file-csv"></i> تصدير CSV
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[850px]">
                    <thead class="bg-gray-50 dark:bg-dark-800 text-gray-500 dark:text-gray-400 text-xs">
                        <tr>
                            <th class="p-3 w-10"><input type="checkbox" id="check-all-orders" class="rounded"></th>
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
                        <tr class="border-t border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-dark-800/50" data-row="{{ $o->id }}">
                            <td class="p-3 text-center"><input type="checkbox" name="ids[]" value="{{ $o->id }}" class="row-check rounded"></td>
                            <td class="p-3 font-mono font-bold text-primary-600">{{ $o->order_number }}</td>
                            <td class="p-3 text-center text-xs">
                                <div class="font-semibold text-gray-800 dark:text-gray-200">{{ $o->customer_name ?: '—' }}</div>
                                <div class="text-gray-500 dark:text-gray-400">{{ $o->email }}</div>
                            </td>
                            <td class="p-3 text-center text-gray-700 dark:text-gray-300">{{ $o->items->count() }}</td>
                            <td class="p-3 text-center font-bold text-gray-900 dark:text-white">{{ number_format($o->total, 2) }} {{ $o->currency }}</td>
                            <td class="p-3 text-center">
                                @php $c = $o->statusBadgeColor(); @endphp
                                <span class="px-2 py-1 text-xs rounded-full font-bold bg-{{ $c }}-50 dark:bg-{{ $c }}-950/30 text-{{ $c }}-700 dark:text-{{ $c }}-400">{{ $o->statusLabel() }}</span>
                            </td>
                            <td class="p-3 text-center text-xs text-gray-500 dark:text-gray-400">{{ $o->created_at->format('Y-m-d H:i') }}</td>
                            <td class="p-3 text-center whitespace-nowrap">
                                <a href="{{ route('admin.orders.show', $o) }}" class="text-primary-600 hover:underline font-bold text-xs">عرض</a>
                                <a href="{{ route('admin.orders.invoice', $o) }}" target="_blank" class="text-gray-600 dark:text-gray-400 hover:underline font-bold text-xs mr-2">فاتورة</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="p-12 text-center text-gray-400">
                            <i class="fas fa-inbox text-3xl mb-3 block"></i>
                            لا توجد طلبات.
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
        <script>
            document.getElementById('check-all-orders')?.addEventListener('change', function(e){
                document.querySelectorAll('.row-check').forEach(c => c.checked = e.target.checked);
            });
        </script>
        @if($orders->hasPages())
        <div class="p-4 border-t border-gray-100 dark:border-gray-800">{{ $orders->links() }}</div>
        @endif
    </x-admin.card>

    <x-slot:side>
        <x-admin.card title="تصفية الطلبات" icon="fa-filter">
            <form method="GET" class="space-y-3">
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">بحث</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="رقم / إيميل / هاتف"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">الحالة</label>
                    <select name="status" class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                        <option value="">كل الحالات</option>
                        @foreach(['pending'=>'قيد الانتظار','paid'=>'مدفوع','shipped'=>'تم الشحن','delivered'=>'تم التوصيل','cancelled'=>'ملغي','refunded'=>'مسترد'] as $k=>$v)
                            <option value="{{ $k }}" @selected(request('status')===$k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">من</label>
                        <input type="date" name="from" value="{{ request('from') }}"
                               class="w-full h-11 px-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-xs focus:border-primary-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">إلى</label>
                        <input type="date" name="to" value="{{ request('to') }}"
                               class="w-full h-11 px-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-xs focus:border-primary-500 focus:outline-none">
                    </div>
                </div>
                <button type="submit" class="w-full h-11 bg-gray-900 dark:bg-dark-700 text-white font-bold rounded-xl hover:bg-gray-800 transition-colors">
                    <i class="fas fa-filter ml-1"></i> تطبيق الفلتر
                </button>
            </form>
        </x-admin.card>
    </x-slot:side>
</x-admin.page>
@endsection
