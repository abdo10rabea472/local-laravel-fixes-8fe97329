@extends('admin.layouts.app')
@section('title', __('app.admin_coupons_title'))

@section('content')
<x-admin.page :title="__('app.admin_coupons_title')" :subtitle="__('app.admin_coupons_subtitle')">
    <x-admin.card :title="__('app.admin_coupons_card_all')" icon="fa-ticket" padding="p-0">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-dark-800 text-gray-500 dark:text-gray-400 text-xs">
                    <tr>
                        <th class="p-3 text-left">{{ __('app.admin_coupons_th_code') }}</th>
                        <th class="p-3">{{ __('app.admin_coupons_th_value') }}</th>
                        <th class="p-3">{{ __('app.admin_coupons_th_scope') }}</th>
                        <th class="p-3">{{ __('app.admin_coupons_th_validity') }}</th>
                        <th class="p-3">{{ __('app.admin_coupons_th_usage') }}</th>
                        <th class="p-3">{{ __('app.admin_coupons_th_status') }}</th>
                        <th class="p-3">{{ __('app.admin_coupons_th_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $c)
                    <tr class="border-t border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-dark-800/50">
                        <td class="p-3 font-mono font-bold text-primary-600">{{ $c->code }}</td>
                        <td class="p-3 text-center font-bold text-gray-900 dark:text-white">{{ $c->type === 'percent' ? $c->value . '%' : money($c->value) }}</td>
                        <td class="p-3 text-center text-xs text-gray-600 dark:text-gray-400">{{ ['all'=>__('app.admin_coupons_scope_all'),'products'=>__('app.admin_coupons_scope_products'),'categories'=>__('app.admin_coupons_scope_categories')][$c->scope] }}</td>
                        <td class="p-3 text-center text-xs text-gray-500 dark:text-gray-400">
                            {{ $c->starts_at?->format('Y-m-d') ?: '—' }} → {{ $c->ends_at?->format('Y-m-d') ?: '∞' }}
                        </td>
                        <td class="p-3 text-center text-xs">{{ $c->used_count }} / {{ $c->usage_limit ?: '∞' }}</td>
                        <td class="p-3 text-center">
                            @if($c->is_active)
                                <span class="px-2 py-1 text-xs bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 rounded-full font-bold">{{ __('app.admin_coupons_status_active') }}</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-gray-100 dark:bg-dark-800 text-gray-600 dark:text-gray-400 rounded-full font-bold">{{ __('app.admin_coupons_status_inactive') }}</span>
                            @endif
                        </td>
                        <td class="p-3 text-center whitespace-nowrap">
                            <a href="{{ route('admin.coupons.edit', $c) }}" class="text-primary-600 hover:underline text-xs font-bold">{{ __('app.admin_coupons_btn_edit') }}</a>
                            <form action="{{ route('admin.coupons.toggle', $c) }}" method="POST" class="inline" data-ajax-toggle>
                                @csrf @method('PATCH')
                                <button data-toggle-state="{{ $c->is_active ? 'on' : 'off' }}" data-toggle-on="@lang('app.admin_coupons_btn_disable')" data-toggle-off="@lang('app.admin_coupons_btn_enable')" class="text-amber-600 hover:underline text-xs font-bold mx-2">{{ $c->is_active ? __('app.admin_coupons_btn_disable') : __('app.admin_coupons_btn_enable') }}</button>
                            </form>
                            <form action="{{ route('admin.coupons.destroy', $c) }}" method="POST" class="inline" data-ajax-confirm="@lang('app.admin_coupons_confirm_delete')" data-ajax-remove>
                                @csrf @method('DELETE')
                                <button class="text-rose-600 hover:underline text-xs font-bold">{{ __('app.admin_coupons_btn_delete') }}</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="p-12 text-center text-gray-400">
                        <i class="fas fa-ticket text-3xl mb-3 block"></i>
                        {{ __('app.admin_coupons_empty') }}
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($coupons->hasPages())
        <div class="p-4 border-t border-gray-100 dark:border-gray-800">{{ $coupons->links() }}</div>
        @endif
    </x-admin.card>

    <x-slot:side>
        <x-admin.card :title="__('app.admin_coupons_quick_actions')" icon="fa-bolt">
            <a href="{{ route('admin.coupons.create') }}" class="w-full h-12 inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/20 transition-colors">
                <i class="fa-solid fa-plus"></i> {{ __('app.admin_coupons_btn_create') }}
            </a>
        </x-admin.card>

        <x-admin.card :title="__('app.admin_coupons_filter_title')" icon="fa-filter">
            <form method="GET" class="space-y-3">
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">{{ __('app.admin_coupons_filter_search_label') }}</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('app.admin_coupons_filter_search_placeholder') }}"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">{{ __('app.admin_coupons_filter_status_label') }}</label>
                    <select name="status" class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                        <option value="">{{ __('app.admin_coupons_filter_all_statuses') }}</option>
                        <option value="active" @selected(request('status')==='active')>{{ __('app.admin_coupons_filter_active') }}</option>
                        <option value="inactive" @selected(request('status')==='inactive')>{{ __('app.admin_coupons_filter_inactive') }}</option>
                    </select>
                </div>
                <button type="submit" class="w-full h-11 bg-gray-900 dark:bg-dark-700 text-white font-bold rounded-xl hover:bg-gray-800 transition-colors">
                    <i class="fas fa-filter mr-1"></i> {{ __('app.admin_coupons_filter_apply') }}
                </button>
            </form>
        </x-admin.card>
    </x-slot:side>
</x-admin.page>
@endsection
