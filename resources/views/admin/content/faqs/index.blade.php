@extends('admin.layouts.app')
@section('title', __('app.admin_faqs_page_title'))

@php
    $defaultCats = ['Shipping', 'Payment', 'Warranty', 'Returns', 'Support', 'Orders', 'General'];
    $allCats = collect($defaultCats)->merge($categories ?? [])->unique()->values();
@endphp

@section('content')
<x-admin.page :title="__('app.admin_faqs_page_title')" :subtitle="__('app.admin_faqs_page_subtitle')">
    <x-admin.card :title="__('app.admin_faqs_card_all')" icon="fa-circle-question" padding="p-0">
        {{-- Stats strip --}}
        <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between gap-3 flex-wrap text-xs">
            <div class="text-gray-500 dark:text-gray-400">
                {{ __('app.admin_pages_form_faq_total') ?? 'الإجمالي' }}:
                <span class="font-bold text-gray-700 dark:text-gray-200">{{ $faqs->total() }}</span>
                @if(($q ?? '') !== '' || ($cat ?? '') !== '')
                    · <span class="text-primary-600 dark:text-primary-400">{{ __('app.admin_pages_form_faq_shown') ?? 'مصفّى' }}</span>
                @endif
            </div>
            <div class="text-gray-500 dark:text-gray-400">
                {{ __('app.admin_pages_form_page') ?? 'صفحة' }}
                <span class="font-bold text-gray-700 dark:text-gray-200">{{ $faqs->currentPage() }}</span>
                {{ __('app.admin_pages_form_of') ?? 'من' }}
                <span class="font-bold text-gray-700 dark:text-gray-200">{{ max(1, $faqs->lastPage()) }}</span>
            </div>
        </div>

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
                @foreach([5,10,20,30,50,100] as $pp)
                    <option value="{{ $pp }}" @selected(($perPage ?? 5) === $pp)>{{ $pp }} / {{ __('app.admin_pages_form_per_page') }}</option>
                @endforeach
            </select>
            <button class="md:col-span-1 h-11 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-bold">
                <i class="fa-solid fa-filter"></i>
            </button>
        </form>

        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($faqs as $i => $f)
                <div class="p-5 faq-row" data-idx="{{ $i }}">
                    <div class="flex items-center justify-between mb-3">
                        <span class="inline-flex items-center gap-2 text-xs font-bold text-gray-500 dark:text-gray-400">
                            <span class="inline-flex w-7 h-7 items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-950/30 text-primary-700 dark:text-primary-300">
                                {{ str_pad(($faqs->firstItem() ?? 1) + $i, 2, '0', STR_PAD_LEFT) }}
                            </span>
                            {{ __('app.admin_pages_form_faq_num') ?? 'سؤال' }}
                        </span>
                        @if($f->category)
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-full bg-violet-50 dark:bg-violet-950/30 text-violet-700 dark:text-violet-300">
                                <i class="fa-solid fa-tag text-[9px]"></i> {{ $f->category }}
                            </span>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('admin.faqs.update', $f) }}" class="space-y-3">
                        @csrf @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div class="flex items-center gap-2">
                                <select name="category" class="faq-cat-select flex-1 h-11 px-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                                    <option value="">{{ __('app.admin_pages_form_select_category') }}</option>
                                    @foreach($allCats as $c)
                                        <option value="{{ $c }}" @selected($f->category === $c)>{{ $c }}</option>
                                    @endforeach
                                    @if($f->category && !$allCats->contains($f->category))
                                        <option value="{{ $f->category }}" selected>{{ $f->category }}</option>
                                    @endif
                                </select>
                                <button type="button" class="faq-cat-new inline-flex items-center gap-1 h-11 px-3 bg-violet-50 dark:bg-violet-950/30 hover:bg-violet-100 text-violet-700 dark:text-violet-300 rounded-xl text-xs font-bold whitespace-nowrap">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
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
                            <button class="px-5 h-10 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-bold">
                                <i class="fa-solid fa-floppy-disk"></i> {{ __('app.admin_faqs_btn_save') }}
                            </button>
                    </form>
                            <form method="POST" action="{{ route('admin.faqs.toggle', $f) }}">
                                @csrf @method('PATCH')
                                <button class="px-5 h-10 bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 rounded-xl text-sm font-bold">
                                    <i class="fa-solid {{ $f->active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                    {{ $f->active ? __('app.admin_faqs_label_active') : __('app.admin_faqs_label_active') }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.faqs.destroy', $f) }}" data-confirm="{{ __('app.admin_faqs_confirm_delete') }}" onsubmit="return confirm(this.dataset.confirm)">
                                @csrf @method('DELETE')
                                <button class="px-5 h-10 bg-rose-50 dark:bg-rose-950/30 text-rose-600 hover:bg-rose-100 rounded-xl text-sm font-bold">
                                    <i class="fa-solid fa-trash"></i> {{ __('app.admin_faqs_btn_delete') }}
                                </button>
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
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <select name="category" id="faq-add-category" class="flex-1 h-11 px-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                            <option value="">{{ __('app.admin_pages_form_select_category') }}</option>
                            @foreach($allCats as $c)
                                <option value="{{ $c }}">{{ $c }}</option>
                            @endforeach
                            <option value="__new__">+ {{ __('app.admin_pages_form_add_faq') ?? 'تصنيف جديد' }}</option>
                        </select>
                    </div>
                    <input type="text" id="faq-add-category-new" placeholder="{{ __('app.admin_pages_form_add_faq') ?? 'اسم التصنيف الجديد' }}"
                           class="hidden w-full h-11 px-4 bg-violet-50 dark:bg-violet-950/30 border border-violet-200 dark:border-violet-800 rounded-xl text-sm focus:border-violet-500 focus:outline-none">
                </div>

                <input name="sort_order" type="number" placeholder="{{ __('app.admin_faqs_field_sort_order') }} — تلقائي"
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

        {{-- Categories management --}}
        <x-admin.card title="التصنيفات" icon="fa-tags">
            <form method="POST" action="{{ route('admin.faqs.categories.store') }}" class="flex items-center gap-2 mb-4">
                @csrf
                <input type="text" name="name" required placeholder="اسم التصنيف"
                       class="flex-1 h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                <button class="h-11 px-4 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-sm font-bold whitespace-nowrap">
                    <i class="fa-solid fa-plus"></i> إضافة تصنيف
                </button>
            </form>
            <div class="flex flex-wrap gap-2">
                @foreach($allCats as $c)
                    @php $inUse = ($usedCats ?? collect())->contains($c); @endphp
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-full text-xs font-bold text-gray-700 dark:text-gray-300">
                        <a href="{{ route('admin.faqs.index', ['category' => $c]) }}" class="hover:text-primary-600">
                            <i class="fa-solid fa-tag text-[10px] text-gray-400"></i> {{ $c }}
                        </a>
                        @if($inUse)
                            <span title="مرتبط بأسئلة — لا يمكن حذفه" class="text-gray-300 cursor-not-allowed">
                                <i class="fa-solid fa-lock text-[10px]"></i>
                            </span>
                        @else
                            <form method="POST" action="{{ route('admin.faqs.categories.destroy') }}" class="inline" onsubmit="return confirm('حذف التصنيف من القائمة؟')">
                                @csrf @method('DELETE')
                                <input type="hidden" name="name" value="{{ $c }}">
                                <button class="text-rose-500 hover:text-rose-700"><i class="fa-solid fa-xmark text-[10px]"></i></button>
                            </form>
                        @endif
                    </span>
                @endforeach
                @if($allCats->isEmpty())
                    <p class="text-xs text-gray-400">لا توجد تصنيفات بعد</p>
                @endif
            </div>
        </x-admin.card>



        {{-- SEO card --}}
        <x-admin.card title="SEO" icon="fa-magnifying-glass-chart">
            <form method="POST" action="{{ route('admin.faqs.seo.update') }}" class="space-y-3">
                @csrf @method('PUT')
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">{{ __('app.admin_pages_form_title_label') ?? 'العنوان' }}</label>
                    <input type="text" name="title" value="{{ old('title', $seoPage->title) }}"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">{{ __('app.admin_pages_form_seo_title') ?? 'SEO Title' }}</label>
                    <input type="text" name="seo_title" value="{{ old('seo_title', $seoPage->seo_title) }}" maxlength="70"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                    <p class="text-[11px] text-gray-400 mt-1">≤ 60 حرف موصى به</p>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">{{ __('app.admin_pages_form_seo_keywords') ?? 'Keywords' }}</label>
                    <input type="text" name="seo_keywords" value="{{ old('seo_keywords', $seoPage->seo_keywords) }}"
                           placeholder="faq, shipping, payment, support"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">{{ __('app.admin_pages_form_seo_description') ?? 'Meta Description' }}</label>
                    <textarea name="seo_description" rows="3" maxlength="160"
                              class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">{{ old('seo_description', $seoPage->seo_description) }}</textarea>
                    <p class="text-[11px] text-gray-400 mt-1">≤ 160 حرف</p>
                </div>
                <button class="w-full h-11 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl">
                    <i class="fa-solid fa-floppy-disk"></i> حفظ بيانات السيو
                </button>
            </form>
        </x-admin.card>
    </x-slot:side>
</x-admin.page>

<script>
    // Add-form: inline new-category input
    (function () {
        const sel = document.getElementById('faq-add-category');
        const inp = document.getElementById('faq-add-category-new');
        const form = document.getElementById('faq-add-form');
        if (!sel || !inp || !form) return;
        sel.addEventListener('change', function () {
            if (sel.value === '__new__') {
                inp.classList.remove('hidden');
                inp.focus();
            } else {
                inp.classList.add('hidden');
                inp.value = '';
            }
        });
        form.addEventListener('submit', function () {
            if (sel.value === '__new__') {
                const name = inp.value.trim();
                if (name) {
                    const opt = document.createElement('option');
                    opt.value = name; opt.textContent = name; opt.selected = true;
                    sel.appendChild(opt);
                    sel.value = name;
                } else {
                    sel.value = '';
                }
            }
        });
    })();

    document.querySelectorAll('.faq-row .faq-cat-new').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const name = prompt('{{ __('app.admin_pages_form_add_faq') ?? 'اسم التصنيف الجديد' }}');
            if (!name) return;
            const sel = btn.parentElement.querySelector('.faq-cat-select');
            if (!sel) return;
            const opt = document.createElement('option');
            opt.value = name; opt.textContent = name; opt.selected = true;
            sel.appendChild(opt);
        });
    });
</script>
@endsection
