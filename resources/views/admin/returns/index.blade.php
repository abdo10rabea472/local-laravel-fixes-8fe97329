@extends('admin.layouts.app')

@section('title', __('app.admin_returns_title'))

@section('content')
<div class="p-6 space-y-6">
    <h1 class="text-2xl font-bold text-slate-800 dark:text-gray-100 dark:text-white">{{ __('app.admin_returns_heading') }}</h1>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-sm border p-5">
            <p class="text-xs text-slate-500 dark:text-gray-400 font-semibold">{{ __('app.admin_returns_stat_total') }}</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-gray-100 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-sm border p-5">
            <p class="text-xs text-amber-600 font-semibold">{{ __('app.admin_orders_status_pending') }}</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-sm border p-5">
            <p class="text-xs text-sky-600 font-semibold">{{ __('app.admin_returns_approved') }}</p>
            <p class="text-2xl font-bold text-sky-600 mt-1">{{ $stats['approved'] }}</p>
        </div>
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-sm border p-5">
            <p class="text-xs text-emerald-600 font-semibold">{{ __('app.admin_orders_status_refunded') }}</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['refunded'] }}</p>
        </div>
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-sm border p-5">
            <p class="text-xs text-emerald-600 font-semibold">{{ __('app.admin_returns_total_refunded') }}</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ money($stats['total_refunded']) }}</p>
        </div>
    </div>

    <form method="GET" class="bg-white dark:bg-dark-900 rounded-2xl shadow-sm border p-4 flex flex-wrap items-center gap-3">
        <input type="text" name="q" value="{{ $search }}" placeholder="{{ __('app.admin_returns_search_ph') }}" class="flex-1 min-w-[200px] rounded-xl border-slate-200 dark:border-gray-800 text-sm">
        <select name="status" class="rounded-xl border-slate-200 dark:border-gray-800 text-sm">
            <option value="">{{ __('app.admin_common_all_statuses') }}</option>
            @foreach(\App\Models\ReturnRequest::STATUSES as $s)
                <option value="{{ $s }}" @selected($status === $s)>{{ (new \App\Models\ReturnRequest(['status'=>$s]))->statusLabel() }}</option>
            @endforeach
        </select>
        <button class="px-5 py-2 rounded-xl bg-violet-600 text-white text-sm font-semibold hover:bg-violet-700">{{ __('app.admin_common_filter') }}</button>
    </form>

    <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-dark-800 text-slate-600 dark:text-gray-300 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">{{ __('app.admin_returns_col_rma') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('app.admin_returns_col_order') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('app.admin_orders_col_customer') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('app.admin_returns_col_amount') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('app.admin_common_status') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('app.admin_common_date') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('app.admin_common_action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($returns as $r)
                        <tr class="hover:bg-slate-50 dark:hover:bg-dark-800 dark:bg-dark-800">
                            <td class="px-4 py-3 font-mono font-semibold text-slate-800 dark:text-gray-100">{{ $r->rma_number }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.orders.show', $r->order_id) }}" class="text-violet-600 dark:text-violet-400 font-semibold hover:underline">{{ $r->order?->order_number }}</a>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-gray-300">{{ $r->user?->name ?? $r->order?->email }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-800 dark:text-gray-100">{{ money($r->refund_amount) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-{{ $r->statusColor() }}-100 text-{{ $r->statusColor() }}-700">{{ $r->statusLabel() }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-500 dark:text-gray-400 text-xs whitespace-nowrap">{{ $r->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.returns.show', $r) }}" class="px-3 py-1 rounded-lg bg-violet-100 text-violet-700 dark:text-violet-300 text-xs font-semibold hover:bg-violet-200">{{ __('app.admin_returns_manage') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-12 text-slate-400 dark:text-gray-500 dark:text-gray-400">{{ __('app.admin_returns_empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t">{{ $returns->links() }}</div>
    </div>
</div>
@endsection
