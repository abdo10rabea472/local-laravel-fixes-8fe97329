@extends('admin.layouts.app')

@section('title', __('app.admin_carriers_title'))

@section('content')
<div class="p-6 space-y-6" x-data="{ showForm: false, editing: null }">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-gray-100 dark:text-white">{{ __('app.admin_carriers_heading') }}</h1>
        <button @click="showForm = true; editing = null" class="px-4 py-2 rounded-xl bg-violet-600 text-white text-sm font-semibold hover:bg-violet-700">
            <i class="fa-solid fa-plus mr-2"></i> {{ __('app.admin_carriers_add') }}
        </button>
    </div>

    {{-- Aramex one-click install --}}
    <div class="rounded-2xl border border-amber-200 bg-gradient-to-r from-amber-50 to-orange-50 p-5 flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-orange-500 text-white flex items-center justify-center text-xl font-bold">A</div>
            <div>
                <h3 class="font-bold text-slate-800 dark:text-gray-100">Aramex</h3>
                <p class="text-xs text-slate-600 dark:text-gray-300">
                    @if(!$aramexInstalled)
                        <span class="text-rose-600 dark:text-rose-400 font-semibold">{{ __('app.admin_carriers_aramex_not_installed') }}</span> {{ __('app.admin_carriers_aramex_run') }}: <code class="bg-white dark:bg-dark-900 px-2 py-0.5 rounded">composer require octw/aramex</code>
                    @elseif(!$aramexConfigured)
                        <span class="text-amber-700 dark:text-amber-300 font-semibold">{{ __('app.admin_carriers_aramex_not_configured') }}</span> {{ __('app.admin_carriers_aramex_publish') }}: <code class="bg-white dark:bg-dark-900 px-2 py-0.5 rounded">php artisan vendor:publish --provider="Octw\Aramex\AramexServiceProvider"</code>
                    @else
                        <span class="text-emerald-700 dark:text-emerald-300 font-semibold">✓ {{ __('app.admin_carriers_aramex_ready') }}</span>
                    @endif
                </p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.shipping-carriers.install-aramex') }}">
            @csrf
            <button type="submit"
                style="background-color:#ea580c;color:#ffffff;"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold shadow-md hover:shadow-lg hover:brightness-110 transition border border-orange-700">
                <i class="fa-solid fa-bolt"></i>
                <span>{{ __('app.admin_carriers_aramex_enable') }}</span>
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 text-emerald-700 dark:text-emerald-300">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 text-rose-700 dark:text-rose-300">{{ session('error') }}</div>
    @endif

    {{-- Form Modal --}}
    <div x-show="showForm" x-transition class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="showForm = false">
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-2xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
            <h2 class="text-lg font-bold text-slate-800 dark:text-gray-100 mb-4" x-text="editing ? '{{ __('app.admin_carriers_edit') }}' : '{{ __('app.admin_carriers_add') }}'"></h2>
            <form :action="editing ? `/admin/shipping-carriers/${editing.id}` : '{{ route('admin.shipping-carriers.store') }}'" method="POST" class="space-y-4">
                @csrf
                <template x-if="editing">@method('PUT')</template>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-gray-200 mb-1">{{ __('app.admin_carriers_name') }} *</label>
                    <input type="text" name="name" :value="editing?.name" required class="w-full rounded-xl border-slate-200 dark:border-gray-800">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-gray-200 mb-1">{{ __('app.admin_carriers_code') }} *</label>
                    <input type="text" name="code" :value="editing?.code" required class="w-full rounded-xl border-slate-200 dark:border-gray-800" placeholder="dhl, aramex, bosta...">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-gray-200 mb-1">{{ __('app.admin_carriers_tracking_url') }}</label>
                    <input type="text" name="tracking_url_template" :value="editing?.tracking_url_template" class="w-full rounded-xl border-slate-200 dark:border-gray-800" placeholder="https://example.com/track/{tracking}">
                </div>

                <div class="rounded-xl border border-violet-100 bg-violet-50 dark:bg-violet-900/30/40 p-3 space-y-3">
                    <p class="text-xs font-bold text-violet-700 dark:text-violet-300"><i class="fa-solid fa-plug"></i> {{ __('app.admin_carriers_api_settings') }}</p>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-gray-200 mb-1">{{ __('app.admin_carriers_api_url') }}</label>
                        <input type="text" name="api_endpoint" :value="editing?.api_endpoint" class="w-full rounded-xl border-slate-200 dark:border-gray-800" placeholder="https://api.carrier.com/v1/tracking/{tracking}">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-gray-200 mb-1">{{ __('app.admin_carriers_api_key') }}</label>
                            <input type="text" name="api_key" :value="editing?.api_key" class="w-full rounded-xl border-slate-200 dark:border-gray-800">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-gray-200 mb-1">{{ __('app.admin_carriers_webhook_secret') }}</label>
                            <input type="text" name="webhook_secret" :value="editing?.webhook_secret" class="w-full rounded-xl border-slate-200 dark:border-gray-800" placeholder="{{ __('app.admin_carriers_webhook_secret_ph') }}">
                        </div>
                    </div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="auto_track" value="1" :checked="editing?.auto_track" class="rounded">
                        <span class="text-xs font-semibold text-slate-700 dark:text-gray-200">{{ __('app.admin_carriers_auto_track') }}</span>
                    </label>
                    <p class="text-[11px] text-slate-500 dark:text-gray-400 leading-relaxed">
                        {{ __('app.admin_carriers_webhook_info') }}<br>
                        <code class="text-[10px] bg-white dark:bg-dark-900 px-2 py-1 rounded border" x-text="`{{ url('/api/shipping') }}/${editing?.code ?? '<code>'}/webhook`"></code>
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-gray-200 mb-1">{{ __('app.admin_carriers_phone') }}</label>
                        <input type="text" name="contact_phone" :value="editing?.contact_phone" class="w-full rounded-xl border-slate-200 dark:border-gray-800">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-gray-200 mb-1">{{ __('app.admin_carriers_email') }}</label>
                        <input type="email" name="contact_email" :value="editing?.contact_email" class="w-full rounded-xl border-slate-200 dark:border-gray-800">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-gray-200 mb-1">{{ __('app.admin_carriers_default_cost') }}</label>
                        <input type="number" step="0.01" name="default_cost" :value="editing?.default_cost ?? 0" class="w-full rounded-xl border-slate-200 dark:border-gray-800">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-gray-200 mb-1">{{ __('app.admin_common_display_order') }}</label>
                        <input type="number" name="sort_order" :value="editing?.sort_order ?? 0" class="w-full rounded-xl border-slate-200 dark:border-gray-800">
                    </div>
                </div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" :checked="editing ? editing.is_active : true" class="rounded">
                    <span class="text-sm font-semibold text-slate-700 dark:text-gray-200">{{ __('app.admin_common_active') }}</span>
                </label>
                <div class="flex gap-2 pt-4 border-t">
                    <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-violet-600 text-white font-semibold hover:bg-violet-700">{{ __('app.admin_common_save') }}</button>
                    <button type="button" @click="showForm = false" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-dark-800 text-slate-700 dark:text-gray-200 font-semibold hover:bg-slate-200">{{ __('app.admin_common_cancel') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-sm border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-dark-800 text-slate-600 dark:text-gray-300 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('app.admin_carriers_name') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('app.admin_carriers_code') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('app.admin_carriers_phone') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('app.admin_carriers_cost') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('app.admin_common_status') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('app.admin_customers_col_orders') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('app.admin_common_actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($carriers as $c)
                    <tr>
                        <td class="px-4 py-3 font-semibold text-slate-800 dark:text-gray-100">{{ $c->name }}</td>
                        <td class="px-4 py-3 text-slate-500 dark:text-gray-400 font-mono">{{ $c->code }}</td>
                        <td class="px-4 py-3 text-slate-500 dark:text-gray-400">{{ $c->contact_phone ?: '—' }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-gray-300">{{ money($c->default_cost) }}</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('admin.shipping-carriers.toggle', $c) }}">
                                @csrf @method('PATCH')
                                <button class="px-3 py-1 rounded-full text-xs font-bold {{ $c->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $c->is_active ? __('app.admin_common_active') : __('app.admin_common_inactive') }}
                                </button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-slate-500 dark:text-gray-400">{{ $c->orders()->count() }}</td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <button @click="editing = @js($c); showForm = true" class="px-3 py-1 rounded-lg bg-sky-100 text-sky-700 dark:text-sky-300 text-xs font-semibold hover:bg-sky-200">{{ __('app.admin_common_edit') }}</button>
                                <form method="POST" action="{{ route('admin.shipping-carriers.destroy', $c) }}" onsubmit="return confirm('{{ __('app.admin_common_confirm_delete') }}')">
                                    @csrf @method('DELETE')
                                    <button class="px-3 py-1 rounded-lg bg-rose-100 text-rose-700 dark:text-rose-300 text-xs font-semibold hover:bg-rose-200">{{ __('app.admin_common_delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-12 text-slate-400 dark:text-gray-500 dark:text-gray-400">{{ __('app.admin_carriers_empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
