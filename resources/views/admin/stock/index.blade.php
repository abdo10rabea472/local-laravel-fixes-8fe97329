@extends('admin.layouts.app')

@section('title', __('app.admin_stock_title'))

@section('content')
<div class="p-6 space-y-6" x-data="stockManager()">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-gray-100 dark:text-white">{{ __('app.admin_stock_heading') }}</h1>
            <p class="text-sm text-slate-500 dark:text-gray-400 mt-1">{{ __('app.admin_stock_subtitle') }}</p>
        </div>
        <a href="{{ route('admin.stock.history') }}" class="px-4 py-2 rounded-xl bg-slate-800 text-white text-sm font-semibold hover:bg-slate-900">
            <i class="fa-solid fa-clock-rotate-left mr-2"></i> {{ __('app.admin_stock_change_log') }}
        </a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-sm border border-slate-200 dark:border-gray-800 p-5">
            <p class="text-xs text-slate-500 dark:text-gray-400 font-semibold">{{ __('app.admin_stock_total_products') }}</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-gray-100 mt-1">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-sm border border-slate-200 dark:border-gray-800 p-5">
            <p class="text-xs text-rose-500 font-semibold">{{ __('app.admin_stock_out') }}</p>
            <p class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-1">{{ number_format($stats['out']) }}</p>
        </div>
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-sm border border-slate-200 dark:border-gray-800 p-5">
            <p class="text-xs text-amber-600 font-semibold">{{ __('app.admin_stock_low') }}</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($stats['low']) }}</p>
        </div>
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-sm border border-slate-200 dark:border-gray-800 p-5">
            <p class="text-xs text-emerald-600 font-semibold">{{ __('app.admin_stock_value') }}</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ money($stats['value']) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white dark:bg-dark-900 rounded-2xl shadow-sm border border-slate-200 dark:border-gray-800 p-4 flex flex-wrap items-center gap-3">
        <input type="text" name="q" value="{{ $search }}" placeholder="{{ __('app.admin_stock_search_ph') }}" class="flex-1 min-w-[200px] rounded-xl border-slate-200 dark:border-gray-800 text-sm bg-white dark:bg-dark-800 text-slate-800 dark:text-gray-100 placeholder:text-slate-400 dark:placeholder:text-gray-500">
        <select name="filter" class="rounded-xl border-slate-200 dark:border-gray-800 text-sm bg-white dark:bg-dark-800 text-slate-800 dark:text-gray-100 placeholder:text-slate-400 dark:placeholder:text-gray-500">
            <option value="">{{ __('app.admin_stock_all_products') }}</option>
            <option value="low" @selected($filter==='low')>{{ __('app.admin_stock_low') }}</option>
            <option value="out" @selected($filter==='out')>{{ __('app.admin_stock_out') }}</option>
        </select>
        <button class="px-5 py-2 rounded-xl bg-violet-600 text-white text-sm font-semibold hover:bg-violet-700">{{ __('app.admin_common_filter') }}</button>
    </form>

    {{-- Bulk save bar --}}
    <div x-show="dirty.length > 0" x-transition class="sticky top-2 z-10 bg-violet-600 text-white rounded-2xl shadow-xl p-4 flex items-center justify-between">
        <span class="font-semibold text-sm">{{ __('app.admin_stock_unsaved_prefix') }} <span x-text="dirty.length"></span> {{ __('app.admin_stock_unsaved_suffix') }}</span>
        <div class="flex gap-2">
            <button @click="reset()" class="px-4 py-2 rounded-lg bg-white/20 text-white text-sm font-semibold hover:bg-white/30">{{ __('app.admin_common_cancel') }}</button>
            <button @click="saveBulk()" :disabled="saving" class="px-5 py-2 rounded-lg bg-white text-violet-700 text-sm font-bold hover:bg-violet-50 disabled:opacity-50">
                <span x-show="!saving">{{ __('app.admin_stock_save_all') }}</span>
                <span x-show="saving">{{ __('app.admin_common_saving') }}</span>
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-sm border border-slate-200 dark:border-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-dark-800 text-slate-600 dark:text-gray-300 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">{{ __('app.admin_products_col_product') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('app.admin_products_col_sku') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('app.admin_products_col_category') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('app.admin_products_col_price') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('app.admin_products_col_stock') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('app.admin_stock_low_threshold') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('app.admin_common_status') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-gray-800">
                    @forelse($products as $p)
                        @php
                            $status = $p->stock == 0 ? 'out' : (($p->low_stock_threshold && $p->stock <= $p->low_stock_threshold) ? 'low' : 'ok');
                            $statusMap = [
                                'out' => ['bg-rose-100 text-rose-700', __('app.admin_stock_status_out')],
                                'low' => ['bg-amber-100 text-amber-700', __('app.admin_stock_status_low')],
                                'ok'  => ['bg-emerald-100 text-emerald-700', __('app.admin_stock_status_in')],
                            ];
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-dark-800">
                            <td class="px-4 py-3 font-medium text-slate-800 dark:text-gray-100">{{ $p->name }}</td>
                            <td class="px-4 py-3 text-slate-500 dark:text-gray-400">{{ $p->sku ?: '—' }}</td>
                            <td class="px-4 py-3 text-slate-500 dark:text-gray-400">{{ $p->category->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-gray-300">{{ money($p->sale_price ?? $p->price) }}</td>
                            <td class="px-4 py-3">
                                <input type="number" min="0"
                                    :data-original="{{ $p->stock }}"
                                    value="{{ $p->stock }}"
                                    @input="markDirty({{ $p->id }}, $event.target.value, 'stock')"
                                    class="w-24 rounded-lg border-slate-200 dark:border-gray-800 text-sm bg-white dark:bg-dark-800 text-slate-800 dark:text-gray-100 placeholder:text-slate-400 dark:placeholder:text-gray-500">
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" min="0"
                                    value="{{ $p->low_stock_threshold }}"
                                    @input="markDirty({{ $p->id }}, $event.target.value, 'threshold')"
                                    class="w-20 rounded-lg border-slate-200 dark:border-gray-800 text-sm bg-white dark:bg-dark-800 text-slate-800 dark:text-gray-100 placeholder:text-slate-400 dark:placeholder:text-gray-500">
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $statusMap[$status][0] }}">{{ $statusMap[$status][1] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-12 text-slate-400 dark:text-gray-500 dark:text-gray-400">{{ __('app.admin_stock_empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100 dark:border-gray-800">{{ $products->links() }}</div>
    </div>
</div>

<script>
function stockManager() {
    return {
        dirty: [],
        saving: false,
        markDirty(id, value, field) {
            const idx = this.dirty.findIndex(d => d.id === id);
            if (idx >= 0) {
                this.dirty[idx][field] = value;
            } else {
                this.dirty.push({ id, [field]: value });
            }
        },
        reset() { location.reload(); },
        async saveBulk() {
            this.saving = true;
            const updates = this.dirty.filter(d => d.stock !== undefined).map(d => ({ id: d.id, stock: parseInt(d.stock) || 0 }));
            try {
                const res = await fetch('{{ route('admin.stock.bulk-update') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ updates, note: 'Bulk update from dashboard' })
                });
                const data = await res.json();
                if (data.ok) {
                    alert('{{ __('app.admin_stock_updated_prefix') }} ' + data.changed + ' {{ __('app.admin_stock_updated_suffix') }}');
                    location.reload();
                } else throw new Error('Failed');
            } catch (e) { alert('{{ __('app.admin_stock_save_failed') }}'); }
            finally { this.saving = false; }
        }
    };
}
</script>
@endsection
