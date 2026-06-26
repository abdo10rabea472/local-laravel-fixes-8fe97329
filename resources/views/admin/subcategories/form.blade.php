@extends('admin.layouts.app')
@section('title', $title)

@section('content')
<form method="POST" action="{{ $category->exists ? route('admin.subcategories.update', $category) : route('admin.subcategories.store') }}" enctype="multipart/form-data">
    @csrf
    @if($category->exists) @method('PUT') @endif

    <x-admin.page :title="$title" subtitle="تصنيف فرعي تابع لكلية معينة" :back="route('admin.subcategories.index')" backLabel="العودة للتصنيفات">
        <x-admin.card title="بيانات التصنيف الفرعي" icon="fa-sitemap">
            <div class="space-y-4">
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">الكلية *</label>
                    <select name="parent_id" required class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                        <option value="">— اختر الكلية —</option>
                        @foreach($colleges as $college)
                            <option value="{{ $college->id }}" @selected(old('parent_id', $category->parent_id) == $college->id)>{{ $college->name }}</option>
                        @endforeach
                    </select>
                    @error('parent_id')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">اسم التصنيف *</label>
                        <input type="text" name="name" value="{{ old('name', $category->name) }}" required placeholder="مثال: Clinical Tools"
                               class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                        @error('name')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug', $category->slug) }}"
                               class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-mono focus:border-primary-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">صورة التصنيف</label>
                    @if($category->image)
                    <div class="mb-2"><img src="{{ asset('storage/' . $category->image) }}" class="h-16 rounded-xl object-cover" alt=""></div>
                    @endif
                    <input type="file" name="image" accept="image/*"
                           class="w-full text-sm file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-primary-50 file:text-primary-700 file:font-bold">
                </div>

                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">الوصف</label>
                    <textarea name="description" rows="3"
                              class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">{{ old('description', $category->description) }}</textarea>
                </div>
            </div>
        </x-admin.card>

        <x-admin.card title="إعدادات SEO" icon="fa-magnifying-glass-chart">
            <div class="space-y-3">
                <input type="text" name="seo_title" value="{{ old('seo_title', $category->exists ? $category->getRawOriginal('seo_title') : '') }}" placeholder="SEO Title"
                       class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                <textarea name="seo_description" rows="2" placeholder="Meta Description"
                          class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">{{ old('seo_description', $category->exists ? $category->getRawOriginal('seo_description') : '') }}</textarea>
                <input type="text" name="seo_keywords" value="{{ old('seo_keywords', $category->seo_keywords) }}" placeholder="Keywords"
                       class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                <input type="url" name="canonical_url" value="{{ old('canonical_url', $category->canonical_url) }}" placeholder="Canonical URL"
                       class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
            </div>
        </x-admin.card>

        <x-slot:side>
            <x-admin.card title="حالة التصنيف" icon="fa-toggle-on">
                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">ترتيب العرض</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}"
                               class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                    </div>
                    <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200 cursor-pointer">
                        <input type="checkbox" name="status" value="1" @checked(old('status', $category->exists ? $category->status : true))
                               class="rounded text-primary-600 focus:ring-primary-500">
                        التصنيف نشط
                    </label>
                </div>

                <div class="mt-5 pt-5 border-t border-gray-100 dark:border-gray-800 space-y-2">
                    <button type="submit" class="w-full h-12 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/20 transition-colors">
                        <i class="fa-solid fa-floppy-disk ml-1"></i>
                        {{ $category->exists ? 'حفظ التعديلات' : 'إضافة التصنيف' }}
                    </button>
                    <a href="{{ route('admin.subcategories.index') }}" class="w-full h-11 inline-flex items-center justify-center bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-gray-300 font-bold rounded-xl hover:bg-gray-200 dark:hover:bg-dark-700 transition-colors">
                        إلغاء
                    </a>
                </div>
            </x-admin.card>
        </x-slot:side>
    </x-admin.page>
</form>
@endsection
