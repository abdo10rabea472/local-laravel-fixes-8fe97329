@extends('admin.layouts.app')

@section('title', __('app.admin_products_title'))

@section('content')
<x-admin.page :title="__('app.admin_products_heading')" :subtitle="__('app.admin_products_subtitle')">
    {{-- ============ MAIN ============ --}}
    <x-admin.card :title="__('app.admin_products_card_title')" icon="fa-boxes" padding="p-0">
        <form method="POST" action="{{ route('admin.products.bulk-action') }}" id="bulk-products-form">
            @csrf
            <div class="flex flex-wrap items-center gap-2 p-3 border-b border-gray-100 dark:border-gray-800 bg-gray-50/60 dark:bg-dark-800/40">
                <select name="action" class="h-10 px-3 bg-white dark:bg-dark-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs">
                    <option value="">— {{ __('app.admin_common_bulk_action') }} —</option>
                    <option value="feature">{{ __('app.admin_products_bulk_feature') }}</option>
                    <option value="unfeature">{{ __('app.admin_products_bulk_unfeature') }}</option>
                    <option value="delete">{{ __('app.admin_products_bulk_delete') }}</option>
                </select>
                <button type="submit" onclick="return confirm('{{ __('app.admin_products_confirm_bulk') }}')"
                        class="h-10 px-4 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-lg">
                    <i class="fas fa-check mr-1"></i> {{ __('app.admin_common_apply') }}
                </button>
                <div class="flex-1"></div>
                <a href="{{ route('admin.products.export') }}"
                   class="h-10 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg inline-flex items-center gap-1">
                    <i class="fas fa-file-csv"></i> {{ __('app.admin_common_export_csv') }}
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-dark-800 text-gray-500 dark:text-gray-400 text-xs">
                        <tr>
                            <th class="p-4 w-10"><input type="checkbox" id="check-all-products" class="rounded"></th>
                            <th class="text-left p-4">{{ __('app.admin_products_col_product') }}</th>
                            <th class="text-left p-4">{{ __('app.admin_products_col_sku') }}</th>
                            <th class="text-left p-4">{{ __('app.admin_products_col_category') }}</th>
                            <th class="text-left p-4">{{ __('app.admin_products_col_price') }}</th>
                            <th class="text-left p-4">{{ __('app.admin_products_col_stock') }}</th>
                            <th class="text-left p-4">{{ __('app.admin_common_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        @php $img = $product->images->first(); @endphp
                        <tr class="border-t border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-dark-800/50">
                            <td class="p-4 text-center"><input type="checkbox" name="ids[]" value="{{ $product->id }}" class="row-check-p rounded"></td>
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    @if($img)
                                        <img src="{{ $img->getUrl('thumb') }}" class="w-12 h-12 rounded-xl object-cover shrink-0" alt="">
                                    @else
                                        <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-dark-800 grid place-items-center shrink-0"><i class="fas fa-image text-gray-400"></i></div>
                                    @endif
                                    <div class="min-w-0">
                                        <div class="font-bold text-gray-900 dark:text-white truncate">{{ $product->name }}</div>
                                        @if($product->featured)<span class="text-[10px] text-amber-600 font-bold">{{ __('app.admin_products_featured') }}</span>@endif
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 font-mono text-xs text-gray-600 dark:text-gray-400">{{ $product->sku ?? '—' }}</td>
                            <td class="p-4 text-gray-700 dark:text-gray-300">{{ $product->category?->name ?? '—' }}</td>
                            <td class="p-4 font-bold text-gray-900 dark:text-white">{{ money($product->sale_price ?? $product->price) }}</td>
                            <td class="p-4">
                                <span class="px-2 py-1 text-xs rounded-full font-bold {{ $product->stock > 5 ? 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400' : 'bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400' }}">{{ $product->stock }}</span>
                            </td>
                            <td class="p-4">
                                <div class="flex gap-3">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="text-primary-600 hover:text-primary-700" title="{{ __('app.admin_common_edit') }}"><i class="fa-solid fa-pen"></i></a>
                                    <a href="{{ route('product.show', $product->slug) }}" target="_blank" class="text-gray-500 hover:text-gray-700" title="{{ __('app.admin_common_view') }}"><i class="fa-solid fa-eye"></i></a>
                                    <button type="submit" form="dup-{{ $product->id }}" class="text-sky-600 hover:text-sky-700" title="{{ __('app.admin_common_duplicate') }}"><i class="fa-solid fa-clone"></i></button>
                                    <button type="submit" form="del-{{ $product->id }}" onclick="return confirm('{{ __('app.admin_products_confirm_delete') }}')" class="text-rose-500 hover:text-rose-600" title="{{ __('app.admin_common_delete') }}"><i class="fa-solid fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="p-12 text-center text-gray-500 dark:text-gray-400">
                            <i class="fas fa-box-open text-3xl mb-3 block"></i>
                            {{ __('app.admin_products_empty') }}
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        @foreach($products as $product)
            <form id="dup-{{ $product->id }}" method="POST" action="{{ route('admin.products.duplicate', $product) }}" class="hidden">@csrf</form>
            <form id="del-{{ $product->id }}" method="POST" action="{{ route('admin.products.destroy', $product) }}" class="hidden">@csrf @method('DELETE')</form>
        @endforeach

        <script>
            document.getElementById('check-all-products')?.addEventListener('change', function(e){
                document.querySelectorAll('.row-check-p').forEach(c => c.checked = e.target.checked);
            });
        </script>
        @if($products->hasPages())
        <div class="p-4 border-t border-gray-100 dark:border-gray-800">{{ $products->links() }}</div>
        @endif
    </x-admin.card>

    {{-- ============ SIDE ============ --}}
    <x-slot:side>
        <x-admin.card :title="__('app.admin_common_quick_actions')" icon="fa-bolt">
            <a href="{{ route('admin.products.create') }}" class="w-full h-12 inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/20 transition-colors">
                <i class="fa-solid fa-plus"></i> {{ __('app.admin_products_add_new') }}
            </a>
        </x-admin.card>

        <x-admin.card :title="__('app.admin_common_filter_search')" icon="fa-filter">
            <form method="GET" class="space-y-3">
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">{{ __('app.admin_common_search') }}</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('app.admin_products_search_placeholder') }}"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">{{ __('app.admin_products_col_category') }}</label>
                    <select name="category_id" class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                        <option value="">{{ __('app.admin_products_all_categories') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="w-full h-11 bg-gray-900 dark:bg-dark-700 text-white font-bold rounded-xl hover:bg-gray-800 dark:hover:bg-dark-600 transition-colors">
                    <i class="fas fa-filter mr-1"></i> {{ __('app.admin_common_apply_filter') }}
                </button>
                @if(request()->hasAny(['search','category_id']))
                <a href="{{ route('admin.products.index') }}" class="w-full h-11 inline-flex items-center justify-center bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-gray-300 font-bold rounded-xl hover:bg-gray-200 dark:hover:bg-dark-700 transition-colors">
                    {{ __('app.admin_common_reset') }}
                </a>
                @endif
            </form>
        </x-admin.card>
    </x-slot:side>
</x-admin.page>
@endsection
