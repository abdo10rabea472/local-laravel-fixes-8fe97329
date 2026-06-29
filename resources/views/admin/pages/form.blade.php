@extends('admin.settings.layout')

@section('settings-content')
<div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
    <div class="border-b border-slate-100 px-6 py-4">
        <h3 class="text-base font-bold text-slate-800">{{ $page->exists ? 'تعديل صفحة: ' . $page->title : 'إضافة صفحة جديدة' }}</h3>
        <p class="text-xs text-slate-500 mt-1">{{ $page->exists ? 'تحديث محتوى وSEO الصفحة.' : 'إنشاء صفحة ثابتة جديدة.' }}</p>
    </div>

    <form method="POST" action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}" enctype="multipart/form-data" class="p-6 space-y-6">
        @csrf
        @if($page->exists) @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500">العنوان *</label>
                <input type="text" name="title" value="{{ old('title', $page->title) }}" required class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500">المعرف (Slug) *</label>
                <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" required class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
            </div>
        </div>

        <div class="space-y-2">
            <label class="text-xs font-bold text-slate-500">المحتوى</label>
            <textarea id="content-editor" name="content" rows="20" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm">{{ old('content', $page->content) }}</textarea>
            <p class="text-[11px] text-slate-400">سيظهر هذا المحتوى في الصفحة العامة بدل القالب الافتراضي.</p>
        </div>

        <div class="border-t border-slate-100 pt-6">
            <h4 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="h-6 w-1 rounded-full bg-violet-500"></span>
                SEO
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500">SEO Title</label>
                    <input type="text" name="seo_title" value="{{ old('seo_title', $page->seo_title) }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500">SEO Keywords</label>
                    <input type="text" name="seo_keywords" value="{{ old('seo_keywords', $page->seo_keywords) }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                </div>
                <div class="space-y-2 md:col-span-2">
                    <label class="text-xs font-bold text-slate-500">SEO Description</label>
                    <textarea name="seo_description" rows="3" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm">{{ old('seo_description', $page->seo_description) }}</textarea>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500">OG Title</label>
                    <input type="text" name="og_title" value="{{ old('og_title', $page->og_title) }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500">OG Image</label>
                    <input type="file" name="og_image" accept="image/*" class="w-full text-sm">
                    @if($page->og_image_url)
                    <div class="flex items-center gap-4 mt-2">
                        <img src="{{ $page->og_image_url }}" alt="" class="h-16 w-auto object-cover rounded-lg">
                        <label class="flex items-center gap-2 text-sm text-rose-600 cursor-pointer">
                            <input type="checkbox" name="remove_og_image" value="1">
                            حذف الصورة
                        </label>
                    </div>
                    @endif
                </div>
                <div class="space-y-2 md:col-span-2">
                    <label class="text-xs font-bold text-slate-500">OG Description</label>
                    <textarea name="og_description" rows="2" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm">{{ old('og_description', $page->og_description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 border-t border-slate-100 pt-6">
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500">Canonical URL</label>
                <input type="url" name="canonical_url" value="{{ old('canonical_url', $page->canonical_url) }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500">الترتيب</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $page->sort_order ?? 0) }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
            </div>
            <div class="flex items-center gap-2 h-11 mt-6">
                <input type="checkbox" name="status" value="1" @checked(old('status', $page->status ?? true)) class="rounded">
                <label class="text-sm font-semibold text-slate-700">نشط</label>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('admin.pages.index') }}" class="h-11 px-6 bg-slate-100 text-slate-700 font-bold rounded-xl flex items-center">إلغاء</a>
            <button type="submit" class="h-11 px-8 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl transition-colors shadow-lg shadow-emerald-500/20">
                {{ $page->exists ? 'تحديث' : 'حفظ' }}
            </button>
        </div>
    </form>
</div>
@endsection
