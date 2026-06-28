@extends('admin.layouts.app')
@section('title', $post->exists ? 'تعديل مقال' : 'مقال جديد')

@section('content')
<x-admin.page
    :title="$post->exists ? 'تعديل مقال' : 'مقال جديد'"
    :subtitle="$post->exists ? 'تعديل بيانات المقال وإعدادات الـ SEO.' : 'إنشاء مقال جديد للمدونة.'"
    :back="route('admin.blog.index')"
    backLabel="العودة للمقالات">

    <form method="POST" action="{{ $post->exists ? route('admin.blog.update', $post) : route('admin.blog.store') }}" enctype="multipart/form-data" class="space-y-6" id="blog-form">
        @csrf @if($post->exists) @method('PUT') @endif

        @if($errors->any())
            <div class="bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-400 p-4 rounded-xl border border-rose-200 dark:border-rose-900">
                <ul class="list-disc pr-5 text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- Main content --}}
        <x-admin.card title="بيانات المقال" icon="fa-pen-to-square">
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1.5">العنوان *</label>
                    <input id="title" name="title" value="{{ old('title', $post->title) }}" required
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1.5">Slug (الرابط)</label>
                    <input id="slug" name="slug" value="{{ old('slug', $post->slug) }}" dir="ltr"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-mono focus:border-primary-500 focus:outline-none">
                    <p class="text-xs text-gray-500 mt-1">اتركه فارغًا لتوليده تلقائيًا من العنوان.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1.5">مقتطف قصير</label>
                    <textarea name="excerpt" rows="2" maxlength="500"
                              class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">{{ old('excerpt', $post->excerpt) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1.5">المحتوى *</label>
                    <textarea id="content-editor" name="content" rows="20"
                              class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm">{{ old('content', $post->content) }}</textarea>
                </div>
            </div>
        </x-admin.card>

    </form>

    <x-slot:side>
        <x-admin.card title="النشر" icon="fa-paper-plane">
            <div class="space-y-3">
                <button form="blog-form" class="w-full h-12 inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/20">
                    <i class="fa-solid fa-save"></i> {{ $post->exists ? 'حفظ التعديلات' : 'نشر المقال' }}
                </button>
                <a href="{{ route('admin.blog.index') }}" class="w-full h-11 inline-flex items-center justify-center bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-gray-200 rounded-xl text-sm font-bold">إلغاء</a>
                @if($post->exists && $post->published_at)
                    <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="w-full h-11 inline-flex items-center justify-center gap-2 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-bold">
                        <i class="fa-solid fa-eye"></i> عرض المقال
                    </a>
                @endif
            </div>
        </x-admin.card>

        <x-admin.card title="التصنيف" icon="fa-folder">
            <select form="blog-form" name="blog_category_id" class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                <option value="">— بدون تصنيف —</option>
                @php $byParent = $categories->groupBy('parent_id'); @endphp
                @foreach($byParent->get(null, collect())->merge($byParent->get(0, collect())) as $root)
                    <option value="{{ $root->id }}" @selected(old('blog_category_id', $post->blog_category_id) == $root->id)>{{ $root->name }}</option>
                    @foreach($byParent->get($root->id, []) as $child)
                        <option value="{{ $child->id }}" @selected(old('blog_category_id', $post->blog_category_id) == $child->id)>— {{ $child->name }}</option>
                    @endforeach
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-2">من تصنيفات المنتجات.</p>
        </x-admin.card>

        <x-admin.card title="تاريخ النشر" icon="fa-calendar">
            <input form="blog-form" type="datetime-local" name="published_at"
                   value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}"
                   class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
            <p class="text-xs text-gray-500 mt-2">اتركه فارغًا للنشر فورًا.</p>
        </x-admin.card>

        <x-admin.card title="الصورة الرئيسية" icon="fa-image">
            <input form="blog-form" type="file" name="image" accept="image/*"
                   class="w-full text-sm file:mr-3 file:px-4 file:py-2 file:border-0 file:rounded-lg file:bg-primary-50 file:text-primary-700 file:font-bold file:cursor-pointer">
            @if($post->image)
                <img src="{{ asset('storage/'.$post->image) }}" class="mt-3 w-full rounded-xl shadow">
            @endif
        </x-admin.card>
    </x-slot:side>
</x-admin.page>

{{-- TinyMCE rich editor --}}
<script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#content-editor',
        height: 600,
        directionality: 'rtl',
        language: 'ar',
        language_url: 'https://cdn.jsdelivr.net/npm/tinymce-i18n@latest/langs7/ar.js',
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount emoticons codesample',
        toolbar: 'undo redo | blocks fontsize | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table codesample | removeformat code fullscreen preview',
        toolbar_mode: 'wrap',
        menubar: 'edit view insert format tools table help',
        image_advtab: true,
        branding: false,
        promotion: false,
        content_style: 'body { font-family: Inter, system-ui, sans-serif; font-size: 15px; line-height: 1.7; }',
    });

    document.querySelector('[name=meta_title]')?.addEventListener('input', e => {
        document.getElementById('serp-title').textContent = e.target.value || document.getElementById('title').value || 'عنوان المقال';
    });
    document.querySelector('[name=meta_description]')?.addEventListener('input', e => {
        document.getElementById('serp-desc').textContent = e.target.value || 'وصف المقال يظهر هنا...';
    });
</script>
@endsection
