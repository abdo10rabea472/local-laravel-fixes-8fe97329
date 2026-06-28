@extends('admin.layouts.app')
@section('title', $post->exists ? 'تعديل مقال' : 'مقال جديد')
@section('content')
<div class="p-6 max-w-4xl">
    <h1 class="text-2xl font-bold mb-6">{{ $post->exists ? 'تعديل مقال' : 'مقال جديد' }}</h1>

    @if($errors->any())<div class="bg-red-100 text-red-700 p-3 rounded mb-4"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <form method="POST" action="{{ $post->exists ? route('admin.blog.update', $post) : route('admin.blog.store') }}" enctype="multipart/form-data" class="bg-white p-6 rounded-xl shadow space-y-4">
        @csrf @if($post->exists) @method('PUT') @endif

        <div>
            <label class="block text-sm font-semibold mb-1">العنوان *</label>
            <input name="title" value="{{ old('title', $post->title) }}" required class="w-full px-3 py-2 border rounded-lg">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-1">Slug</label>
                <input name="slug" value="{{ old('slug', $post->slug) }}" class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">التصنيف</label>
                <select name="blog_category_id" class="w-full px-3 py-2 border rounded-lg">
                    <option value="">— بدون —</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" @selected(old('blog_category_id', $post->blog_category_id)==$c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">مقتطف</label>
            <textarea name="excerpt" rows="2" class="w-full px-3 py-2 border rounded-lg">{{ old('excerpt', $post->excerpt) }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">المحتوى *</label>
            <textarea name="content" rows="12" required class="w-full px-3 py-2 border rounded-lg font-mono text-sm">{{ old('content', $post->content) }}</textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-1">الصورة</label>
                <input type="file" name="image" accept="image/*" class="w-full">
                @if($post->image)<img src="{{ asset('storage/'.$post->image) }}" class="mt-2 h-24 rounded">@endif
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">تاريخ النشر</label>
                <input type="datetime-local" name="published_at" value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}" class="w-full px-3 py-2 border rounded-lg">
                <p class="text-xs text-slate-500 mt-1">اتركه فارغًا لحفظه كمسودة.</p>
            </div>
        </div>

        <div class="flex gap-2">
            <button class="px-6 py-2 bg-primary-600 text-white rounded-lg">حفظ</button>
            <a href="{{ route('admin.blog.index') }}" class="px-6 py-2 bg-slate-100 rounded-lg">إلغاء</a>
        </div>
    </form>
</div>
@endsection
