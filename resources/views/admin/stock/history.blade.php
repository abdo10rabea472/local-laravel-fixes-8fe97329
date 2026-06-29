@extends('admin.layouts.app')

@section('title', __('app.admin_stock_hist_title'))

@section('content')
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-gray-100 dark:text-white">{{ __('app.admin_stock_hist_heading') }}</h1>
        <a href="{{ route('admin.stock.index') }}" class="text-violet-600 dark:text-violet-400 hover:underline text-sm font-semibold">
            <i class="fa-solid fa-arrow-left mr-1"></i> {{ __('app.admin_stock_hist_back') }}
        </a>
    </div>

    <form method="GET" class="bg-white dark:bg-dark-900 rounded-2xl shadow-sm border p-4 flex flex-wrap items-center gap-3">
        <select name="product_id" class="rounded-xl border-slate-200 dark:border-gray-800 text-sm min-w-[200px]">
            <option value="">{{ __('app.admin_stock_all_products') }}</option>
            @foreach($products as $p)
                <option value="{{ $p->id }}" @selected($productId == $p->id)>{{ $p->name }}</option>
            @endforeach
        </select>
        <select name="type" class="rounded-xl border-slate-200 dark:border-gray-800 text-sm">
            <option value="">{{ __('app.admin_stock_hist_all_types') }}</option>
            @foreach([
                'manual' => __('app.admin_stock_hist_type_manual'),
                'order' => __('app.admin_stock_hist_type_order'),
                'order_cancel' => __('app.admin_stock_hist_type_order_cancel'),
                'return' => __('app.admin_stock_hist_type_return'),
                'adjustment' => __('app.admin_stock_hist_type_adjustment'),
                'bulk_update' => __('app.admin_stock_hist_type_bulk')
            ] as $k => $v)
                <option value="{{ $k }}" @selected($type === $k)>{{ $v }}</option>
            @endforeach
        </select>
        <button class="px-5 py-2 rounded-xl bg-violet-600 text-white text-sm font-semibold hover:bg-violet-700">{{ __('app.admin_common_filter') }}</button>
    </form>

    <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-dark-800 text-slate-600 dark:text-gray-300 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">{{ __('app.admin_common_date') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('app.admin_products_col_product') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('app.admin_discounts_type') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('app.admin_stock_hist_change') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('app.admin_stock_hist_before') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('app.admin_stock_hist_after') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('app.admin_stock_hist_note') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($movements as $m)
                        <tr>
                            <td class="px-4 py-3 text-slate-500 dark:text-gray-400 whitespace-nowrap">{{ $m->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3 font-medium text-slate-700 dark:text-gray-200">{{ $m->product?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-{{ $m->typeColor() }}-100 text-{{ $m->typeColor() }}-700">{{ $m->typeLabel() }}</span>
                            </td>
                            <td class="px-4 py-3 font-bold {{ $m->quantity_change > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $m->quantity_change > 0 ? '+' : '' }}{{ $m->quantity_change }}
                            </td>
                            <td class="px-4 py-3 text-slate-500 dark:text-gray-400">{{ $m->stock_before }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-gray-200 font-semibold">{{ $m->stock_after }}</td>
                            <td class="px-4 py-3 text-slate-500 dark:text-gray-400 text-xs">{{ $m->note ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-12 text-slate-400 dark:text-gray-500 dark:text-gray-400">{{ __('app.admin_stock_hist_empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t">{{ $movements->links() }}</div>
    </div>
</div>
@endsection
