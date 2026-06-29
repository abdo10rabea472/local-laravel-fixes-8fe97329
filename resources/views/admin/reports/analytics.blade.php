@extends('admin.layouts.app')

@section('title', __('app.admin_reports_analytics_title'))

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="p-6 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">{{ __('app.admin_reports_analytics_heading') }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ __('app.admin_reports_analytics_subtitle') }}</p>
        </div>
        <div class="flex gap-2">
            @foreach([7=>'7 Days', 30=>'30 Days', 90=>'90 Days', 365=>'1 Year'] as $d => $lbl)
                <a href="?days={{ $d }}" class="px-4 py-2 rounded-xl text-sm font-bold {{ $range == $d ? 'bg-violet-600 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:border-violet-300' }}">{{ $lbl }}</a>
            @endforeach
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border rounded-2xl p-5">
            <p class="text-xs font-bold text-slate-500">{{ __('app.admin_reports_analytics_kpi_total_revenue') }}</p>
            <h3 class="text-2xl font-black text-emerald-600 mt-2">{{ money($kpi->revenue) }}</h3>
        </div>
        <div class="bg-white border rounded-2xl p-5">
            <p class="text-xs font-bold text-slate-500">{{ __('app.admin_reports_analytics_kpi_orders') }}</p>
            <h3 class="text-2xl font-black text-violet-600 mt-2">{{ (int) $kpi->orders_count }}</h3>
            <p class="text-[11px] text-slate-400 mt-1">{{ __('app.admin_reports_analytics_kpi_paid') }}: {{ (int) $kpi->paid_count }} · {{ __('app.admin_reports_analytics_kpi_pending') }}: {{ (int) $kpi->pending_count }}</p>
        </div>
        <div class="bg-white border rounded-2xl p-5">
            <p class="text-xs font-bold text-slate-500">{{ __('app.admin_reports_analytics_kpi_aov') }}</p>
            <h3 class="text-2xl font-black text-sky-600 mt-2">
                {{ money($kpi->paid_count > 0 ? $kpi->revenue / $kpi->paid_count : 0) }}
            </h3>
        </div>
        <div class="bg-white border rounded-2xl p-5">
            <p class="text-xs font-bold text-slate-500">{{ __('app.admin_reports_analytics_kpi_cancelled_refunded') }}</p>
            <h3 class="text-2xl font-black text-rose-600 mt-2">{{ (int) $kpi->cancelled_count + (int) $kpi->refunded_count }}</h3>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white border rounded-2xl p-6">
            <h3 class="font-bold text-slate-800 mb-4">{{ __('app.admin_reports_analytics_chart_daily_revenue') }}</h3>
            <canvas id="revenueChart" height="100"></canvas>
        </div>
        <div class="bg-white border rounded-2xl p-6">
            <h3 class="font-bold text-slate-800 mb-4">{{ __('app.admin_reports_analytics_chart_order_statuses') }}</h3>
            <canvas id="statusChart"></canvas>
        </div>
    </div>

    <div class="bg-white border rounded-2xl p-6">
        <h3 class="font-bold text-slate-800 mb-4">{{ __('app.admin_reports_analytics_top_products') }}</h3>
        @if($topProducts->isEmpty())
            <p class="text-center text-slate-400 py-8">{{ __('app.admin_reports_analytics_no_data') }}</p>
        @else
        <table class="w-full text-sm">
            <thead class="text-xs uppercase text-slate-500 border-b">
                <tr>
                    <th class="py-2 text-left">#</th>
                    <th class="py-2 text-left">{{ __('app.admin_reports_analytics_col_product') }}</th>
                    <th class="py-2 text-left">{{ __('app.admin_reports_analytics_col_qty_sold') }}</th>
                    <th class="py-2 text-left">{{ __('app.admin_reports_analytics_col_revenue') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($topProducts as $i => $p)
                    <tr>
                        <td class="py-3 text-slate-400">#{{ $i+1 }}</td>
                        <td class="py-3 font-semibold text-slate-700">{{ $p->name }}</td>
                        <td class="py-3 font-bold text-violet-600">{{ (int) $p->qty }}</td>
                        <td class="py-3 font-bold text-emerald-600">{{ money($p->revenue) }}</td>
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
                label: 'Revenue',
                data: series.map(s => s.revenue),
                borderColor: '#7c3aed',
                backgroundColor: 'rgba(124,58,237,0.1)',
                fill: true, tension: 0.35, pointRadius: 3,
            }, {
                label: 'Orders',
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
                y: { beginAtZero: true, position: 'left' },
                y2: { beginAtZero: true, position: 'right', grid: { display: false } }
            }
        }
    });

    const labels = { pending:'Pending', paid:'Paid', shipped:'Shipped', delivered:'Delivered', cancelled:'Cancelled', refunded:'Refunded' };
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
