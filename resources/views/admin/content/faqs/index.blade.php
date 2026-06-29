@extends('admin.layouts.app')
@section('title', __('app.admin_faqs_page_title'))

@php
    $defaultCats = ['Shipping', 'Payment', 'Warranty', 'Returns', 'Support', 'Orders', 'General'];
    $allCats = collect($defaultCats)->merge($categories ?? [])->unique()->values();
@endphp

@section('content')
<x-admin.page :title="__('app.admin_faqs_page_title')" :subtitle="__('app.admin_faqs_page_subtitle')">
    <x-admin.card :title="__('app.admin_faqs_card_all')" icon="fa-circle-question" padding="p-0">
        {{-- Toolbar: search + category + per page --}}
        <form method="GET" class="p-4 border-b border-gray-100 dark:border-gray-800 grid grid-cols-1 md:grid-cols-12 gap-3">
            <div class="md:col-span-6 relative">
                <input type="text" name="q" value="{{ $q ?? '' }}"
                       placeholder="{{ __('app.admin_pages_form_faq_search_placeholder') }}"
                       class="w-full h-11 ps-10 pe-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                <i class="fa-solid fa-magnifying-glass absolute start-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            </div>
            <select name="category" class="md:col-span-3 h-11 px-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm">
                <option value="">{{ __('app.admin_pages_form_select_category') }}</option>
                @foreach($allCats as $c)
                    <option value="{{ $c }}" @selected(($cat ?? '') === $c)>{{ $c }}</option>
                @endforeach
            </select>
            <select name="per_page" class="md:col-span-2 h-11 px-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm">
                @foreach([20,30,50,100] as $pp)
                    <option value="{{ $pp }}" @selected(($perPage ?? 30) === $pp)>{{ $pp }} / {{ __('app.admin_pages_form_per_page') }}</option>
                @endforeach
            </select>
            <button class="md:col-span-1 h-11 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-bold">
                <i class="fa-solid fa-filter"></i>
            </button>
        </form>

        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($faqs as $f)
                <div class="p-5">
                    <form method="POST" action="{{ route('admin.faqs.update', $f) }}" class="space-y-3">
                        @csrf @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <select name="category" class="h-11 px-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                                <option value="">{{ __('app.admin_pages_form_select_category') }}</option>
                                @foreach($allCats as $c)
                                    <option value="{{ $c }}" @selected($f->category === $c)>{{ $c }}</option>
                                @endforeach
                                @if($f->category && !$allCats->contains($f->category))
                                    <option value="{{ $f->category }}" selected>{{ $f->category }}</option>
                                @endif
                            </select>
                            <input name="sort_order" type="number" value="{{ $f->sort_order }}" placeholder="{{ __('app.admin_faqs_field_sort_order') }}"
                                   class="h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                            <label class="h-11 flex items-center gap-2 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm cursor-pointer">
                                <input type="checkbox" name="active" value="1" @checked($f->active) class="accent-primary-600"> {{ __('app.admin_faqs_label_active') }}
                            </label>
                        </div>
                        <input name="question" value="{{ $f->question }}" placeholder="{{ __('app.admin_faqs_field_question') }}"
                               class="w-full h-11 px-4 bg-white dark:bg-dark-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-bold focus:border-primary-500 focus:outline-none">
                        <textarea name="answer" rows="3" placeholder="{{ __('app.admin_faqs_field_answer') }}"
                                  class="w-full px-4 py-3 bg-white dark:bg-dark-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">{{ $f->answer }}</textarea>
                        <div class="flex gap-2">
                            <button class="px-5 h-10 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-bold">{{ __('app.admin_faqs_btn_save') }}</button>
                    </form>
                            <form method="POST" action="{{ route('admin.faqs.destroy', $f) }}" data-confirm="{{ __('app.admin_faqs_confirm_delete') }}" onsubmit="return confirm(this.dataset.confirm)">
                                @csrf @method('DELETE')
                                <button class="px-5 h-10 bg-rose-50 dark:bg-rose-950/30 text-rose-600 hover:bg-rose-100 rounded-xl text-sm font-bold">{{ __('app.admin_faqs_btn_delete') }}</button>
                            </form>
                        </div>
                </div>
            @empty
                <div class="p-12 text-center text-gray-400">
                    <i class="fa-regular fa-circle-question text-3xl mb-3 block"></i>
                    {{ __('app.admin_faqs_empty') }}
                </div>
            @endforelse
        </div>
        @if($faqs->hasPages())
        <div class="p-4 border-t border-gray-100 dark:border-gray-800">{{ $faqs->links() }}</div>
        @endif
    </x-admin.card>

    <x-slot:side>
        <x-admin.card :title="__('app.admin_faqs_card_add')" icon="fa-plus">
            <form method="POST" action="{{ route('admin.faqs.store') }}" class="space-y-3" id="faq-add-form">
                @csrf
                <div class="flex items-center gap-2">
                    <select name="category" id="faq-add-category" class="flex-1 h-11 px-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                        <option value="">{{ __('app.admin_pages_form_select_category') }}</option>
                        @foreach($allCats as $c)
                            <option value="{{ $c }}">{{ $c }}</option>
                        @endforeach
                    </select>
                    <button type="button" id="faq-cat-new" class="inline-flex items-center gap-1 h-11 px-3 bg-violet-50 dark:bg-violet-950/30 hover:bg-violet-100 text-violet-700 dark:text-violet-300 rounded-xl text-xs font-bold whitespace-nowrap">
                        <i class="fa-solid fa-plus"></i> {{ __('app.admin_pages_form_add_faq') ?? 'تصنيف جديد' }}
                    </button>
                </div>
                <input name="sort_order" type="number" value="0" placeholder="{{ __('app.admin_faqs_field_sort_order') }}"
                       class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                <input name="question" required placeholder="{{ __('app.admin_faqs_field_question') }}"
                       class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                <textarea name="answer" required rows="4" placeholder="{{ __('app.admin_faqs_field_answer') }}"
                          class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none"></textarea>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="active" value="1" checked class="accent-primary-600"> {{ __('app.admin_faqs_label_active') }}
                </label>
                <button class="w-full h-12 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/20">
                    <i class="fa-solid fa-plus"></i> {{ __('app.admin_faqs_btn_add') }}
                </button>
            </form>
        </x-admin.card>

        {{-- Categories quick stats --}}
        <x-admin.card :title="__('app.admin_pages_form_select_category')" icon="fa-tags">
            <div class="flex flex-wrap gap-2">
                @foreach($allCats as $c)
                    <a href="{{ route('admin.faqs.index', ['category' => $c]) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-50 dark:bg-dark-800 hover:bg-primary-50 dark:hover:bg-primary-950/30 border border-gray-200 dark:border-gray-700 rounded-full text-xs font-bold text-gray-700 dark:text-gray-300 hover:text-primary-700 dark:hover:text-primary-300 transition">
                        <i class="fa-solid fa-tag text-[10px] text-gray-400"></i> {{ $c }}
                    </a>
                @endforeach
                @if($allCats->isEmpty())
                    <p class="text-xs text-gray-400">{{ __('app.admin_faqs_empty') }}</p>
                @endif
            </div>
        </x-admin.card>
    </x-slot:side>
</x-admin.page>

<script>
    document.getElementById('faq-cat-new')?.addEventListener('click', function () {
        const name = prompt('{{ __('app.admin_pages_form_add_faq') ?? 'اسم التصنيف الجديد' }}');
        if (!name) return;
        const sel = document.getElementById('faq-add-category');
        const opt = document.createElement('option');
        opt.value = name; opt.textContent = name; opt.selected = true;
        sel.appendChild(opt);
    });
</script>
@endsection
