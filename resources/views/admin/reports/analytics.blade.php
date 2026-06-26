@extends('admin.layouts.app')

@section('title', 'تحليلات المبيعات')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="p-6 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">لوحة التحليلات</h1>
            <p class="text-sm text-slate-500 mt-1">أداء المبيعات والإيرادات وأكثر المنتجات طلباً</p>
        </div>
        <div class="flex gap-2">
            @foreach([7=>'7 أيام', 30=>'30 يوم', 90=>'90 يوم', 365=>'سنة'] as $d => $lbl)
                <a href="?days={{ $d }}" class="px-4 py-2 rounded-xl text-sm font-bold {{ $range == $d ? 'bg-violet-600 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:border-violet-300' }}">{{ $lbl }}</a>
            @endforeach
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border rounded-2xl p-5">
            <p class="text-xs font-bold text-slate-500">إجمالي الإيرادات</p>
            <h3 class="text-2xl font-black text-emerald-600 mt-2">{{ number_format((float) $kpi->revenue, 0) }} <span class="text-xs">ج.م</span></h3>
        </div>
        <div class="bg-white border rounded-2xl p-5">
            <p class="text-xs font-bold text-slate-500">عدد الطلبات</p>
            <h3 class="text-2xl font-black text-violet-600 mt-2">{{ (int) $kpi->orders_count }}</h3>
            <p class="text-[11px] text-slate-400 mt-1">مدفوع: {{ (int) $kpi->paid_count }} · قيد الانتظار: {{ (int) $kpi->pending_count }}</p>
        </div>
        <div class="bg-white border rounded-2xl p-5">
            <p class="text-xs font-bold text-slate-500">متوسط قيمة الطلب</p>
            <h3 class="text-2xl font-black text-sky-600 mt-2">
                {{ $kpi->paid_count > 0 ? number_format($kpi->revenue / $kpi->paid_count, 0) : 0 }} <span class="text-xs">ج.م</span>
            </h3>
        </div>
        <div class="bg-white border rounded-2xl p-5">
            <p class="text-xs font-bold text-slate-500">ملغية / مرتجعة</p>
            <h3 class="text-2xl font-black text-rose-600 mt-2">{{ (int) $kpi->cancelled_count + (int) $kpi->refunded_count }}</h3>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white border rounded-2xl p-6">
            <h3 class="font-bold text-slate-800 mb-4">الإيرادات اليومية</h3>
            <canvas id="revenueChart" height="100"></canvas>
        </div>
        <div class="bg-white border rounded-2xl p-6">
            <h3 class="font-bold text-slate-800 mb-4">حالات الطلبات</h3>
            <canvas id="statusChart"></canvas>
        </div>
    </div>

    <div class="bg-white border rounded-2xl p-6">
        <h3 class="font-bold text-slate-800 mb-4">أكثر المنتجات مبيعاً</h3>
        @if($topProducts->isEmpty())
            <p class="text-center text-slate-400 py-8">لا توجد بيانات بعد</p>
        @else
        <table class="w-full text-sm">
            <thead class="text-xs uppercase text-slate-500 border-b">
                <tr>
                    <th class="py-2 text-right">#</th>
                    <th class="py-2 text-right">المنتج</th>
                    <th class="py-2 text-right">الكمية المباعة</th>
                    <th class="py-2 text-right">الإيرادات</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($topProducts as $i => $p)
                    <tr>
                        <td class="py-3 text-slate-400">#{{ $i+1 }}</td>
                        <td class="py-3 font-semibold text-slate-700">{{ $p->name }}</td>
                        <td class="py-3 font-bold text-violet-600">{{ (int) $p->qty }}</td>
                        <td class="py-3 font-bold text-emerald-600">{{ number_format((float) $p->revenue, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
window.addEventListener('load', function(){
    if (typeof Chart === 'undefined') return;
    const series = @json($series);
    const statusBreakdown = @json($statusBreakdown);

    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: series.map(s => s.date.slice(5)),
            datasets: [{
                label: 'الإيرادات',
                data: series.map(s => s.revenue),
                borderColor: '#7c3aed',
                backgroundColor: 'rgba(124,58,237,0.1)',
                fill: true, tension: 0.35, pointRadius: 3,
            }, {
                label: 'عدد الطلبات',
                data: series.map(s => s.orders),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16,185,129,0.1)',
                tension: 0.35, yAxisID: 'y2', pointRadius: 3,
            }]
        },
        options: {
            responsive: true,
            interaction: { intersect: false, mode: 'index' },
            scales: {
                y: { beginAtZero: true, position: 'right' },
                y2: { beginAtZero: true, position: 'left', grid: { display: false } }
            }
        }
    });

    const labels = { pending:'قيد الانتظار', paid:'مدفوع', shipped:'شحن', delivered:'تم التوصيل', cancelled:'ملغي', refunded:'مسترد' };
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: statusBreakdown.map(s => labels[s.status] || s.status),
            datasets: [{
                data: statusBreakdown.map(s => s.count),
                backgroundColor: ['#f59e0b','#0ea5e9','#6366f1','#10b981','#ef4444','#64748b'],
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
});
</script>
@endpush

