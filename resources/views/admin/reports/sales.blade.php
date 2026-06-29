@extends('admin.layouts.app')
@section('title', __('app.admin_reports_sales_title'))

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="p-6 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-gray-100">{{ __('app.admin_reports_sales_heading') }}</h1>
            <p class="text-sm text-slate-500 mt-1 dark:text-gray-400">{{ __('app.admin_reports_sales_subtitle') }}</p>
        </div>
        <a href="{{ route('admin.reports.sales', array_merge(request()->all(), ['export' => 'csv'])) }}"
           class="px-4 py-2 rounded-xl text-sm font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg shadow-emerald-500/20">
            <i class="fa-solid fa-file-csv mr-1"></i> {{ __('app.admin_reports_sales_export_csv') }}
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white border border-slate-200 rounded-2xl p-4 grid grid-cols-1 sm:grid-cols-4 gap-3 dark:bg-dark-900 dark:border-dark-700">
        <div>
            <label class="text-xs font-bold text-slate-500 dark:text-gray-400">{{ __('app.admin_reports_sales_filter_from') }}</label>
            <input type="date" name="from" value="{{ $from }}" class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm dark:bg-dark-800 dark:border-dark-700">
        </div>
        <div>
            <label class="text-xs font-bold text-slate-500 dark:text-gray-400">{{ __('app.admin_reports_sales_filter_to') }}</label>
            <input type="date" name="to" value="{{ $to }}" class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm dark:bg-dark-800 dark:border-dark-700">
        </div>
        <div>
            <label class="text-xs font-bold text-slate-500 dark:text-gray-400">{{ __('app.admin_reports_sales_filter_status') }}</label>
            <select name="status" class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm dark:bg-dark-800 dark:border-dark-700">
                <option value="">{{ __('app.admin_reports_sales_filter_paid_default') }}</option>
                @foreach($statuses as $s)
                    <option value="{{ $s }}" @selected($status===$s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button class="w-full h-10 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-sm font-bold">{{ __('app.admin_reports_sales_filter_submit') }}</button>
        </div>
    </form>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $cards = [
                [__('app.admin_reports_sales_kpi_total_revenue'), (float) $kpi->revenue, 'text-emerald-600'],
                [__('app.admin_reports_sales_kpi_orders'), (int) $kpi->orders, 'text-violet-600', false],
                [__('app.admin_reports_sales_kpi_aov'), (float) $kpi->aov, 'text-sky-600'],
                [__('app.admin_reports_sales_kpi_total_discounts'), (float) $kpi->discount, 'text-rose-600'],
                [__('app.admin_reports_sales_kpi_shipping'), (float) $kpi->shipping, 'text-amber-600'],
                [__('app.admin_reports_sales_kpi_taxes'), (float) $kpi->tax, 'text-slate-600'],
                [__('app.admin_reports_sales_kpi_subtotal'), (float) $kpi->subtotal, 'text-indigo-600'],
            ];
        @endphp
        @foreach($cards as $c)
            <div class="bg-white border border-slate-200 rounded-2xl p-5 dark:bg-dark-900 dark:border-dark-700">
                <p class="text-xs font-bold text-slate-500 dark:text-gray-400">{{ $c[0] }}</p>
                <h3 class="text-2xl font-black mt-2 {{ $c[2] }}">
                    {{ ($c[3] ?? true) ? money($c[1]) : $c[1] }}
                </h3>
            </div>
        @endforeach
    </div>

    {{-- Chart --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-5 dark:bg-dark-900 dark:border-dark-700">
        <h3 class="text-sm font-bold text-slate-700 mb-3 dark:text-gray-200">{{ __('app.admin_reports_sales_chart_daily_revenue') }}</h3>
        <canvas id="salesChart" height="80"></canvas>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Daily table --}}
        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden dark:bg-dark-900 dark:border-dark-700">
            <div class="px-5 py-3 border-b border-slate-100 dark:border-dark-700"><h3 class="text-sm font-bold text-slate-700 dark:text-gray-200">{{ __('app.admin_reports_sales_section_daily') }}</h3></div>
            <div class="overflow-x-auto max-h-96">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-xs text-slate-500 sticky top-0 dark:bg-dark-800 dark:text-gray-400">
                        <tr>
                            <th class="p-3 text-left">{{ __('app.admin_reports_sales_col_date') }}</th>
                            <th class="p-3">{{ __('app.admin_reports_sales_col_orders') }}</th>
                            <th class="p-3">{{ __('app.admin_reports_sales_col_revenue') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($daily as $row)
                        <tr class="border-t border-slate-100 dark:border-dark-700">
                            <td class="p-3 font-mono">{{ $row->d }}</td>
                            <td class="p-3 text-center">{{ $row->orders }}</td>
                            <td class="p-3 text-center font-bold text-emerald-600">{{ money($row->revenue) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="p-8 text-center text-slate-400 dark:text-gray-500">{{ __('app.admin_reports_sales_no_data') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Payment methods --}}
        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden dark:bg-dark-900 dark:border-dark-700">
            <div class="px-5 py-3 border-b border-slate-100 dark:border-dark-700"><h3 class="text-sm font-bold text-slate-700 dark:text-gray-200">{{ __('app.admin_reports_sales_section_payment_methods') }}</h3></div>
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-xs text-slate-500 dark:bg-dark-800 dark:text-gray-400">
                    <tr>
                        <th class="p-3 text-left">{{ __('app.admin_reports_sales_col_method') }}</th>
                        <th class="p-3">{{ __('app.admin_reports_sales_col_orders') }}</th>
                        <th class="p-3">{{ __('app.admin_reports_sales_col_revenue') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($byPayment as $row)
                    <tr class="border-t border-slate-100 dark:border-dark-700">
                        <td class="p-3">{{ $row->method }}</td>
                        <td class="p-3 text-center">{{ $row->orders }}</td>
                        <td class="p-3 text-center font-bold text-emerald-600">{{ money($row->revenue) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="p-8 text-center text-slate-400 dark:text-gray-500">—</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Top products --}}
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden dark:bg-dark-900 dark:border-dark-700">
        <div class="px-5 py-3 border-b border-slate-100 dark:border-dark-700"><h3 class="text-sm font-bold text-slate-700 dark:text-gray-200">{{ __('app.admin_reports_sales_section_top_products') }}</h3></div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 dark:bg-dark-800 dark:text-gray-400">
                <tr>
                    <th class="p-3 text-left">{{ __('app.admin_reports_sales_col_product') }}</th>
                    <th class="p-3">{{ __('app.admin_reports_sales_col_quantity') }}</th>
                    <th class="p-3">{{ __('app.admin_reports_sales_col_revenue') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse($topProducts as $p)
                <tr class="border-t border-slate-100 dark:border-dark-700">
                    <td class="p-3 font-bold text-slate-800 dark:text-gray-100">{{ $p->name }}</td>
                    <td class="p-3 text-center">{{ (int) $p->qty }}</td>
                    <td class="p-3 text-center font-bold text-emerald-600">{{ money($p->revenue) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="p-8 text-center text-slate-400 dark:text-gray-500">{{ __('app.admin_reports_sales_no_data') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
(function(){
    const ctx = document.getElementById('salesChart');
    if (!ctx || !window.Chart) return;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($daily->pluck('d')),
            datasets: [{
                label: 'Revenue',
                data: @json($daily->pluck('revenue')->map(fn($v) => (float)$v)),
                borderColor: '#7c3aed', backgroundColor: 'rgba(124,58,237,.1)',
                fill: true, tension: .3, borderWidth: 2,
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });
})();
</script>
@endsection
