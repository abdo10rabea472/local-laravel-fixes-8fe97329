@extends('admin.layouts.app')
@section('title', 'المقالات')
@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">المقالات</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.blog-categories.index') }}" class="px-4 py-2 bg-slate-100 rounded-lg text-sm">التصنيفات</a>
            <a href="{{ route('admin.blog.create') }}" class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm"><i class="fas fa-plus"></i> مقال جديد</a>
        </div>
    </div>

    @if(session('success'))<div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>@endif

    <form class="mb-4"><input name="q" value="{{ request('q') }}" placeholder="بحث..." class="w-full md:w-72 px-3 py-2 border rounded-lg"></form>

    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr><th class="p-3 text-right">العنوان</th><th class="p-3">التصنيف</th><th class="p-3">المشاهدات</th><th class="p-3">النشر</th><th class="p-3">إجراءات</th></tr>
            </thead>
            <tbody>
            @forelse($posts as $p)
                <tr class="border-t">
                    <td class="p-3 font-semibold">{{ $p->title }}</td>
                    <td class="p-3 text-center">{{ $p->category?->name ?? '—' }}</td>
                    <td class="p-3 text-center">{{ $p->views }}</td>
                    <td class="p-3 text-center text-xs">{{ $p->published_at?->format('Y-m-d') ?? 'مسودة' }}</td>
                    <td class="p-3 text-center">
                        <a href="{{ route('admin.blog.edit', $p) }}" class="text-blue-600"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{ route('admin.blog.destroy', $p) }}" class="inline" onsubmit="return confirm('حذف؟')">@csrf @method('DELETE')<button class="text-red-600 mr-2"><i class="fas fa-trash"></i></button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="p-6 text-center text-slate-400">لا توجد مقالات</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $posts->links() }}</div>
</div>
@endsection
