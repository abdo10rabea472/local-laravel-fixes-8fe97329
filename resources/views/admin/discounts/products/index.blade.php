@extends('admin.layouts.app')
@section('title', __('app.admin_discounts_title'))

@section('content')
<div class="p-6 max-w-7xl mx-auto" x-data="{ openForm: false, edit: null }">
    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white">{{ __('app.admin_discounts_heading') }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ __('app.admin_discounts_subtitle') }}</p>
        </div>
        <button @click="openForm = true; edit = null" class="px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white font-bold rounded-xl">
            <i class="fa-solid fa-plus mr-1"></i> {{ __('app.admin_discounts_add') }}
        </button>
    </div>

    <form method="GET" class="flex gap-2 mb-4">
        <select name="status" class="h-10 px-3 border border-slate-200 rounded-xl text-sm">
            <option value="">{{ __('app.admin_discounts_all_statuses') }}</option>
            <option value="active" @selected(request('status')==='active')>{{ __('app.admin_discounts_status_active') }}</option>
            <option value="inactive" @selected(request('status')==='inactive')>{{ __('app.admin_common_inactive') }}</option>
            <option value="expired" @selected(request('status')==='expired')>{{ __('app.admin_discounts_expired') }}</option>
        </select>
        <button class="h-10 px-4 bg-slate-900 text-white rounded-xl text-sm font-bold">{{ __('app.admin_common_filter') }}</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600 text-xs">
                <tr>
                    <th class="p-3 text-left">{{ __('app.admin_products_col_product') }}</th>
                    <th class="p-3">{{ __('app.admin_discounts_type') }}</th>
                    <th class="p-3">{{ __('app.admin_discounts_value') }}</th>
                    <th class="p-3">{{ __('app.admin_discounts_from') }}</th>
                    <th class="p-3">{{ __('app.admin_discounts_to') }}</th>
                    <th class="p-3">{{ __('app.admin_common_status') }}</th>
                    <th class="p-3">{{ __('app.admin_common_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($discounts as $d)
                <tr class="border-t border-slate-100">
                    <td class="p-3 font-bold">{{ $d->product->name ?? '—' }}</td>
                    <td class="p-3 text-center">{{ $d->type === 'percent' ? __('app.admin_discounts_type_percent') : __('app.admin_discounts_type_fixed') }}</td>
                    <td class="p-3 text-center">{{ $d->type === 'percent' ? $d->value . '%' : money($d->value) }}</td>
                    <td class="p-3 text-center text-xs text-slate-500">{{ $d->starts_at?->format('Y-m-d H:i') ?: '—' }}</td>
                    <td class="p-3 text-center text-xs text-slate-500">{{ $d->ends_at?->format('Y-m-d H:i') ?: '—' }}</td>
                    <td class="p-3 text-center">
                        @if($d->isCurrentlyActive())
                            <span class="px-2 py-1 text-xs bg-emerald-50 text-emerald-700 rounded-full font-bold">{{ __('app.admin_common_active') }}</span>
                        @elseif(! $d->is_active)
                            <span class="px-2 py-1 text-xs bg-slate-100 text-slate-600 rounded-full font-bold">{{ __('app.admin_common_disabled') }}</span>
                        @else
                            <span class="px-2 py-1 text-xs bg-amber-50 text-amber-700 rounded-full font-bold">{{ __('app.admin_discounts_out_of_period') }}</span>
                        @endif
                    </td>
                    <td class="p-3 text-center">
                        <button @click='edit = @json($d); openForm = true' class="text-violet-600 hover:underline text-xs font-bold">{{ __('app.admin_common_edit') }}</button>
                        <form action="{{ route('admin.product-discounts.toggle', $d) }}" method="POST" class="inline" data-ajax-toggle>
                            @csrf @method('PATCH')
                            <button data-toggle-state="{{ $d->is_active ? 'on' : 'off' }}" data-toggle-on="{{ __('app.admin_common_disable') }}" data-toggle-off="{{ __('app.admin_common_enable') }}" class="text-amber-600 hover:underline text-xs font-bold mx-2">{{ $d->is_active ? __('app.admin_common_disable') : __('app.admin_common_enable') }}</button>
                        </form>
                        <form action="{{ route('admin.product-discounts.destroy', $d) }}" method="POST" class="inline" data-ajax-confirm="{{ __('app.admin_discounts_confirm_delete') }}" data-ajax-remove>
                            @csrf @method('DELETE')
                            <button class="text-rose-600 hover:underline text-xs font-bold">{{ __('app.admin_common_delete') }}</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="p-8 text-center text-slate-400">{{ __('app.admin_discounts_empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $discounts->links() }}</div>

    {{-- Modal --}}
    <div x-show="openForm" x-cloak class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-4" @click.self="openForm = false">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6">
            <h3 class="text-lg font-bold mb-4" x-text="edit ? '{{ __('app.admin_discounts_edit') }}' : '{{ __('app.admin_discounts_add') }}'"></h3>
            <form :action="edit ? `{{ url('admin/product-discounts') }}/${edit.id}` : `{{ route('admin.product-discounts.store') }}`" method="POST" class="space-y-3" data-ajax data-success-toast="{{ __('app.admin_common_saved') }}">
                @csrf
                <template x-if="edit">@method('PUT')</template>
                <input type="hidden" name="_method" :value="edit ? 'PUT' : 'POST'">

                <div>
                    <label class="text-xs font-bold text-slate-500 block mb-1">{{ __('app.admin_products_col_product') }}</label>
                    <select name="product_id" required class="w-full h-11 px-3 border border-slate-200 rounded-xl text-sm">
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" x-bind:selected="edit && edit.product_id == {{ $p->id }}">{{ $p->name }} ({{ money($p->price) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-slate-500 block mb-1">{{ __('app.admin_discounts_type') }}</label>
                        <select name="type" class="w-full h-11 px-3 border border-slate-200 rounded-xl text-sm">
                            <option value="percent" x-bind:selected="!edit || edit.type === 'percent'">{{ __('app.admin_discounts_type_percent') }}</option>
                            <option value="fixed" x-bind:selected="edit && edit.type === 'fixed'">{{ __('app.admin_discounts_type_fixed') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 block mb-1">{{ __('app.admin_discounts_value') }}</label>
                        <input type="number" step="0.01" min="0" name="value" required class="w-full h-11 px-3 border border-slate-200 rounded-xl text-sm" :value="edit ? edit.value : ''">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-slate-500 block mb-1">{{ __('app.admin_discounts_starts_at') }}</label>
                        <input type="datetime-local" name="starts_at" class="w-full h-11 px-3 border border-slate-200 rounded-xl text-sm" :value="edit && edit.starts_at ? edit.starts_at.substring(0,16) : ''">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 block mb-1">{{ __('app.admin_discounts_ends_at') }}</label>
                        <input type="datetime-local" name="ends_at" class="w-full h-11 px-3 border border-slate-200 rounded-xl text-sm" :value="edit && edit.ends_at ? edit.ends_at.substring(0,16) : ''">
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" x-bind:checked="!edit || edit.is_active"> {{ __('app.admin_common_active') }}
                </label>
                <div class="flex gap-2 pt-2">
                    <button class="px-5 py-2.5 bg-violet-600 text-white font-bold rounded-xl">{{ __('app.admin_common_save') }}</button>
                    <button type="button" @click="openForm = false" class="px-5 py-2.5 bg-slate-100 text-slate-700 font-bold rounded-xl">{{ __('app.admin_common_cancel') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
