@extends('admin.layouts.app')

@section('title', __('app.admin_reports_coupons_title'))

@section('content')
<div class="p-6 space-y-6">
    <h1 class="text-2xl font-bold text-slate-800">{{ __('app.admin_reports_coupons_heading') }}</h1>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white border rounded-2xl p-5">
            <p class="text-xs font-bold text-slate-500">{{ __('app.admin_reports_coupons_kpi_redemptions') }}</p>
            <h3 class="text-2xl font-black text-violet-600 mt-2">{{ (int) $totals->redemptions }}</h3>
        </div>
        <div class="bg-white border rounded-2xl p-5">
            <p class="text-xs font-bold text-slate-500">{{ __('app.admin_reports_coupons_kpi_discounts') }}</p>
            <h3 class="text-2xl font-black text-rose-600 mt-2">{{ money($totals->discount) }}</h3>
        </div>
        <div class="bg-white border rounded-2xl p-5">
            <p class="text-xs font-bold text-slate-500">{{ __('app.admin_reports_coupons_kpi_revenue') }}</p>
            <h3 class="text-2xl font-black text-emerald-600 mt-2">{{ money($totals->revenue) }}</h3>
        </div>
    </div>

    <div class="bg-white border rounded-2xl overflow-hidden">
        <div class="px-5 py-4 border-b">
            <h3 class="font-bold text-slate-800">{{ __('app.admin_reports_coupons_section_all') }}</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-2 text-left">{{ __('app.admin_reports_coupons_col_code') }}</th>
                    <th class="px-4 py-2 text-left">{{ __('app.admin_reports_coupons_col_value') }}</th>
                    <th class="px-4 py-2 text-left">{{ __('app.admin_reports_coupons_col_uses') }}</th>
                    <th class="px-4 py-2 text-left">{{ __('app.admin_reports_coupons_col_total_discount') }}</th>
                    <th class="px-4 py-2 text-left">{{ __('app.admin_reports_coupons_col_order_revenue') }}</th>
                    <th class="px-4 py-2 text-left">{{ __('app.admin_reports_coupons_col_status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($coupons as $c)
                    <tr>
                        <td class="px-4 py-3 font-mono font-bold text-slate-800">{{ $c->code }}</td>
                        <td class="px-4 py-3">
                            @if($c->type === 'percent')
                                {{ rtrim(rtrim(number_format($c->value, 2), '0'), '.') }}%
                            @else
                                {{ money($c->value) }}
                            @endif
                        </td>
                        <td class="px-4 py-3 font-bold text-violet-600">{{ $c->redemptions_count }} {{ $c->usage_limit ? "/ {$c->usage_limit}" : '' }}</td>
                        <td class="px-4 py-3 text-rose-600 font-semibold">{{ money($c->total_discount ?? 0) }}</td>
                        <td class="px-4 py-3 text-emerald-600 font-semibold">{{ money($c->total_revenue ?? 0) }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-[10px] font-bold {{ $c->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $c->is_active ? __('app.admin_reports_coupons_status_active') : __('app.admin_reports_coupons_status_inactive') }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-12 text-slate-400">{{ __('app.admin_reports_coupons_no_coupons') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t">{{ $coupons->links() }}</div>
    </div>
</div>
@endsection
