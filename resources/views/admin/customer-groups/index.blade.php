@extends('admin.layouts.app')
@section('title', __('app.admin_groups_title'))

@section('content')
<x-admin.page :title="__('app.admin_groups_heading')" :subtitle="__('app.admin_groups_subtitle')">

    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    <x-admin.card :title="__('app.admin_common_add')" icon="fa-plus" class="mb-6">
        <form method="POST" action="{{ route('admin.customer-groups.store') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3">
            @csrf
            <input type="text" name="name" placeholder="{{ __('app.admin_groups_name_ph') }}" required
                class="h-10 px-3 border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-800 text-gray-900 dark:text-gray-100 rounded-xl text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none">
            <input type="number" step="0.01" min="0" max="100" name="discount_percent" placeholder="{{ __('app.admin_groups_discount_ph') }}" required
                class="h-10 px-3 border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-800 text-gray-900 dark:text-gray-100 rounded-xl text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none">
            <select name="badge_color"
                class="h-10 px-3 border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-800 text-gray-900 dark:text-gray-100 rounded-xl text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none">
                @foreach(['violet','emerald','sky','amber','rose','slate','indigo'] as $c)
                    <option value="{{ $c }}">{{ $c }}</option>
                @endforeach
            </select>
            <input type="text" name="description" placeholder="{{ __('app.admin_groups_description_ph') }}"
                class="h-10 px-3 border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-800 text-gray-900 dark:text-gray-100 rounded-xl text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none">
            <button class="h-10 px-4 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-bold">
                <i class="fas fa-plus mr-1"></i> {{ __('app.admin_common_add') }}
            </button>
        </form>
    </x-admin.card>

    <x-admin.card :title="__('app.admin_groups_title')" icon="fa-users-gear" padding="p-0">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-dark-800 text-gray-500 dark:text-gray-400 text-xs">
                    <tr>
                        <th class="p-3 text-left">{{ __('app.admin_groups_col_group') }}</th>
                        <th class="p-3">{{ __('app.admin_groups_col_discount') }}</th>
                        <th class="p-3">{{ __('app.admin_groups_col_customers') }}</th>
                        <th class="p-3">{{ __('app.admin_common_status') }}</th>
                        <th class="p-3">{{ __('app.admin_common_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($groups as $g)
                    <tr class="border-t border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-dark-800/50 align-top">
                        <td class="p-3">
                            <span class="px-2 py-1 rounded-full bg-{{ $g->badge_color }}-50 dark:bg-{{ $g->badge_color }}-900/30 text-{{ $g->badge_color }}-700 dark:text-{{ $g->badge_color }}-300 font-bold text-xs">{{ $g->name }}</span>
                            @if($g->description)
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $g->description }}</p>
                            @endif
                        </td>
                        <td class="p-3 text-center font-bold text-emerald-600 dark:text-emerald-400">{{ $g->discount_percent }}%</td>
                        <td class="p-3 text-center text-gray-700 dark:text-gray-300">{{ $g->users_count }}</td>
                        <td class="p-3 text-center">
                            @if($g->is_active)
                                <span class="px-2 py-1 text-xs bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 rounded-full font-bold">{{ __('app.admin_common_active') }}</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-gray-100 dark:bg-dark-800 text-gray-600 dark:text-gray-400 rounded-full font-bold">{{ __('app.admin_common_disabled') }}</span>
                            @endif
                        </td>
                        <td class="p-3 text-center whitespace-nowrap" x-data="{ open:false }">
                            <button type="button" @click="open=!open" class="text-primary-600 hover:underline text-xs font-bold">
                                <i class="fas fa-edit"></i> {{ __('app.admin_common_edit') }}
                            </button>
                            <form method="POST" action="{{ route('admin.customer-groups.destroy', $g) }}" class="inline" onsubmit="return confirm('{{ __('app.admin_common_confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="text-rose-600 dark:text-rose-400 hover:underline text-xs font-bold ml-2">
                                    <i class="fas fa-trash"></i> {{ __('app.admin_common_delete') }}
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.customer-groups.update', $g) }}" x-show="open" x-cloak
                                  class="text-left p-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl mt-3 space-y-2">
                                @csrf @method('PUT')
                                <input type="text" name="name" value="{{ $g->name }}" class="w-full h-9 px-2 border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-900 text-gray-900 dark:text-gray-100 rounded-lg text-sm">
                                <input type="number" step="0.01" name="discount_percent" value="{{ $g->discount_percent }}" class="w-full h-9 px-2 border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-900 text-gray-900 dark:text-gray-100 rounded-lg text-sm">
                                <select name="badge_color" class="w-full h-9 px-2 border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-900 text-gray-900 dark:text-gray-100 rounded-lg text-sm">
                                    @foreach(['violet','emerald','sky','amber','rose','slate','indigo'] as $c)
                                        <option value="{{ $c }}" @selected($g->badge_color===$c)>{{ $c }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="description" value="{{ $g->description }}" class="w-full h-9 px-2 border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-900 text-gray-900 dark:text-gray-100 rounded-lg text-sm">
                                <label class="text-xs flex items-center gap-1 text-gray-700 dark:text-gray-300">
                                    <input type="checkbox" name="is_active" value="1" @checked($g->is_active)> {{ __('app.admin_common_active') }}
                                </label>
                                <button class="w-full h-9 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-bold">{{ __('app.admin_common_save') }}</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="p-8 text-center text-gray-400 dark:text-gray-500">{{ __('app.admin_groups_empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
</x-admin.page>
@endsection
