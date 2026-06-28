@extends('admin.layouts.app')
@section('title', 'تصنيفات المقالات')
@section('content')
<div class="p-6 max-w-4xl">
    <h1 class="text-2xl font-bold mb-6">تصنيفات المقالات</h1>
    @if(session('success'))<div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>@endif

    <div class="bg-white p-6 rounded-xl shadow mb-6">
        <h3 class="font-bold mb-3">إضافة تصنيف</h3>
        <form method="POST" action="{{ route('admin.blog-categories.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            @csrf
            <input name="name" required placeholder="الاسم" class="px-3 py-2 border rounded-lg">
            <input name="slug" placeholder="slug (اختياري)" class="px-3 py-2 border rounded-lg">
            <input name="description" placeholder="وصف" class="px-3 py-2 border rounded-lg">
            <button class="bg-primary-600 text-white px-4 py-2 rounded-lg">إضافة</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow">
        <table class="w-full text-sm">
            <thead class="bg-slate-50"><tr><th class="p-3 text-right">الاسم</th><th class="p-3">Slug</th><th class="p-3">المقالات</th><th class="p-3"></th></tr></thead>
            <tbody>
            @foreach($categories as $c)
                <tr class="border-t">
                    <form method="POST" action="{{ route('admin.blog-categories.update', $c) }}" class="contents">@csrf @method('PUT')
                        <td class="p-3"><input name="name" value="{{ $c->name }}" class="w-full px-2 py-1 border rounded"></td>
                        <td class="p-3"><input name="slug" value="{{ $c->slug }}" class="w-full px-2 py-1 border rounded"></td>
                        <td class="p-3 text-center">{{ $c->posts_count }}</td>
                        <td class="p-3 text-center">
                            <button class="text-blue-600"><i class="fas fa-save"></i></button>
                    </form>
                            <form method="POST" action="{{ route('admin.blog-categories.destroy', $c) }}" class="inline" onsubmit="return confirm('حذف؟')">@csrf @method('DELETE')<button class="text-red-600 mr-2"><i class="fas fa-trash"></i></button></form>
                        </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $categories->links() }}</div>
</div>
@endsection
