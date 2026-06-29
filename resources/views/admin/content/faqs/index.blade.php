@extends('admin.layouts.app')
@section('title', __('app.admin_faqs_page_title'))

@section('content')
<x-admin.page :title="__('app.admin_faqs_page_title')" :subtitle="__('app.admin_faqs_page_subtitle')">
    <x-admin.card :title="__('app.admin_faqs_card_all')" icon="fa-circle-question" padding="p-0">
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($faqs as $f)
                <div class="p-5">
                    <form method="POST" action="{{ route('admin.faqs.update', $f) }}" class="space-y-3">
                        @csrf @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <input name="category" value="{{ $f->category }}" placeholder="{{ __('app.admin_faqs_field_category') }}"
                                   class="h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                            <input name="sort_order" type="number" value="{{ $f->sort_order }}" placeholder="{{ __('app.admin_faqs_field_sort_order') }}"
                                   class="h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                            <label class="h-11 flex items-center gap-2 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm cursor-pointer">
                                <input type="checkbox" name="active" value="1" @checked(\$f->active) class="accent-primary-600"> {{ __('app.admin_faqs_label_active') }}
                            </label>
                        </div>
                        <input name="question" value="{{ $f->question }}" placeholder="{{ __('app.admin_faqs_field_question') }}"
                               class="w-full h-11 px-4 bg-white dark:bg-dark-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-bold focus:border-primary-500 focus:outline-none">
                        <textarea name="answer" rows="3" placeholder="{{ __('app.admin_faqs_field_answer') }}"
                                  class="w-full px-4 py-3 bg-white dark:bg-dark-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">{{ $f->answer }}</textarea>
                        <div class="flex gap-2">
                            <button class="px-5 h-10 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-bold">{{ __('app.admin_faqs_btn_save') }}</button>
                    </form>
                            <form method="POST" action="{{ route('admin.faqs.destroy', $f) }}" onsubmit="return confirm('{{ __(\'app.admin_faqs_confirm_delete\') }}')">
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
            <form method="POST" action="{{ route('admin.faqs.store') }}" class="space-y-3">
                @csrf
                <input name="category" placeholder="{{ __('app.admin_faqs_new_category_placeholder') }}"
                       class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                <input name="sort_order" type="number" value="0" placeholder="ترتيب"
                       class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                <input name="question" required placeholder="السؤال"
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
    </x-slot:side>
</x-admin.page>
@endsection
