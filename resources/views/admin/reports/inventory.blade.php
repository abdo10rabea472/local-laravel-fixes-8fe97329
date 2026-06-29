@extends('admin.layouts.app')

@section('title', __('app.admin_reports_inventory_title'))

@section('content')
<div class="p-6 space-y-6">
    <h1 class="text-2xl font-bold text-slate-800 dark:text-gray-100">{{ __('app.admin_reports_inventory_heading') }}</h1>

    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
        @foreach([
            [__('app.admin_reports_inventory_kpi_total_products'), $stats['total'], 'box', 'sky'],
            [__('app.admin_reports_inventory_kpi_units_in_stock'), number_format($stats['units']), 'boxes-stacked', 'indigo'],
            [__('app.admin_reports_inventory_kpi_stock_value'), money($stats['value']), 'sack-dollar', 'emerald'],
            [__('app.admin_reports_inventory_kpi_low_stock'), $stats['low'], 'triangle-exclamation', 'amber'],
            [__('app.admin_reports_inventory_kpi_out_of_stock'), $stats['out'], 'circle-xmark', 'rose'],
            [__('app.admin_reports_inventory_kpi_movements'), $stats['movements_30d'], 'arrows-rotate', 'violet'],
        ] as [$lbl,$val,$icon,$c])
        <div class="bg-white border rounded-2xl p-4 dark:bg-dark-900 dark:border-dark-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 dark:text-gray-400">{{ $lbl }}</p>
                    <h4 class="text-xl font-black text-{{ $c }}-600 mt-1">{{ $val }}</h4>
                </div>
                <i class="fa-solid fa-{{ $icon }} text-{{ $c }}-300 text-2xl"></i>
            </div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white border rounded-2xl overflow-hidden dark:bg-dark-900 dark:border-dark-700">
            <div class="px-5 py-4 border-b flex items-center justify-between">
                <h3 class="font-bold text-slate-800 dark:text-gray-100"><i class="fa-solid fa-triangle-exclamation text-amber-500"></i> {{ __('app.admin_reports_inventory_section_low_stock') }}</h3>
                <a href="{{ route('admin.stock.index', ['filter'=>'low']) }}" class="text-violet-600 text-xs font-bold">{{ __('app.admin_reports_inventory_manage') }}</a>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500 dark:bg-dark-800 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-2 text-left">{{ __('app.admin_reports_inventory_col_product') }}</th>
                        <th class="px-4 py-2 text-left">{{ __('app.admin_reports_inventory_col_sku') }}</th>
                        <th class="px-4 py-2 text-left">{{ __('app.admin_reports_inventory_col_stock') }}</th>
                        <th class="px-4 py-2 text-left">{{ __('app.admin_reports_inventory_col_threshold') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($low as $p)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-slate-700 dark:text-gray-200">{{ $p->name }}</td>
                            <td class="px-4 py-3 text-slate-500 text-xs dark:text-gray-400">{{ $p->sku ?: '—' }}</td>
                            <td class="px-4 py-3 font-bold text-amber-600">{{ $p->stock }}</td>
                            <td class="px-4 py-3 text-slate-500 dark:text-gray-400">{{ $p->low_stock_threshold }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-8 text-slate-400 dark:text-gray-500">{{ __('app.admin_reports_inventory_no_low_stock') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t">{{ $low->links() }}</div>
        </div>

        <div class="bg-white border rounded-2xl overflow-hidden dark:bg-dark-900 dark:border-dark-700">
            <div class="px-5 py-4 border-b flex items-center justify-between">
                <h3 class="font-bold text-slate-800 dark:text-gray-100"><i class="fa-solid fa-circle-xmark text-rose-500"></i> {{ __('app.admin_reports_inventory_section_out_of_stock') }}</h3>
                <a href="{{ route('admin.stock.index', ['filter'=>'out']) }}" class="text-violet-600 text-xs font-bold">{{ __('app.admin_reports_inventory_manage') }}</a>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500 dark:bg-dark-800 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-2 text-left">{{ __('app.admin_reports_inventory_col_product') }}</th>
                        <th class="px-4 py-2 text-left">{{ __('app.admin_reports_inventory_col_sku') }}</th>
                        <th class="px-4 py-2 text-left">{{ __('app.admin_reports_inventory_col_category') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($out as $p)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-slate-700 dark:text-gray-200">{{ $p->name }}</td>
                            <td class="px-4 py-3 text-slate-500 text-xs dark:text-gray-400">{{ $p->sku ?: '—' }}</td>
                            <td class="px-4 py-3 text-slate-500 text-xs dark:text-gray-400">{{ $p->category?->name }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center py-8 text-slate-400 dark:text-gray-500">{{ __('app.admin_reports_inventory_no_out_of_stock') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t">{{ $out->links() }}</div>
        </div>
    </div>

    <div class="bg-white border rounded-2xl p-6 dark:bg-dark-900 dark:border-dark-700">
        <h3 class="font-bold text-slate-800 mb-4 dark:text-gray-100"><i class="fa-solid fa-fire text-orange-500"></i> {{ __('app.admin_reports_inventory_section_top_movers') }}</h3>
        <table class="w-full text-sm">
            <thead class="text-xs uppercase text-slate-500 border-b dark:text-gray-400">
                <tr>
                    <th class="py-2 text-left">{{ __('app.admin_reports_inventory_col_product') }}</th>
                    <th class="py-2 text-left">{{ __('app.admin_reports_inventory_col_sku') }}</th>
                    <th class="py-2 text-left">{{ __('app.admin_reports_inventory_col_current_stock') }}</th>
                    <th class="py-2 text-left">{{ __('app.admin_reports_inventory_col_total_movement') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($topMovers as $m)
                    <tr>
                        <td class="py-3 font-semibold text-slate-700 dark:text-gray-200">{{ $m->product?->name ?? '—' }}</td>
                        <td class="py-3 text-slate-500 text-xs dark:text-gray-400">{{ $m->product?->sku ?: '—' }}</td>
                        <td class="py-3">{{ $m->product?->stock ?? 0 }}</td>
                        <td class="py-3 font-bold text-violet-600">{{ (int) $m->movement }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center py-8 text-slate-400 dark:text-gray-500">{{ __('app.admin_reports_inventory_no_movements') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
