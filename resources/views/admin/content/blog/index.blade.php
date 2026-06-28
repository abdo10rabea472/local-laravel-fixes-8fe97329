@extends('admin.layouts.app')
@section('title', 'المقالات')

@section('content')
<x-admin.page title="المقالات" subtitle="إدارة جميع مقالات المدونة وتحسين الـ SEO.">
    <x-admin.card title="كل المقالات" icon="fa-newspaper" padding="p-0">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-dark-800 text-gray-500 dark:text-gray-400 text-xs">
                    <tr>
                        <th class="p-3 text-right">العنوان</th>
                        <th class="p-3">التصنيف</th>
                        <th class="p-3">المشاهدات</th>
                        <th class="p-3">النشر</th>
                        <th class="p-3">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($posts as $p)
                    <tr class="border-t border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-dark-800/50">
                        <td class="p-3 font-bold text-gray-900 dark:text-white">{{ $p->title }}</td>
                        <td class="p-3 text-center text-xs text-gray-600 dark:text-gray-400">{{ $p->category?->name ?? '—' }}</td>
                        <td class="p-3 text-center text-xs">{{ $p->views }}</td>
                        <td class="p-3 text-center text-xs">
                            @if($p->published_at)
                                <span class="px-2 py-1 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 rounded-full font-bold">{{ $p->published_at->format('Y-m-d') }}</span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 dark:bg-dark-800 text-gray-600 dark:text-gray-400 rounded-full font-bold">مسودة</span>
                            @endif
                        </td>
                        <td class="p-3 text-center whitespace-nowrap">
                            <a href="{{ route('admin.blog.edit', $p) }}" class="text-primary-600 hover:underline text-xs font-bold">تعديل</a>
                            <form action="{{ route('admin.blog.destroy', $p) }}" method="POST" class="inline" onsubmit="return confirm('حذف المقال؟')">
                                @csrf @method('DELETE')
                                <button class="text-rose-600 hover:underline text-xs font-bold mr-2">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-12 text-center text-gray-400">
                        <i class="fas fa-newspaper text-3xl mb-3 block"></i>
                        لا توجد مقالات بعد.
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($posts->hasPages())
        <div class="p-4 border-t border-gray-100 dark:border-gray-800">{{ $posts->links() }}</div>
        @endif
    </x-admin.card>

    <x-slot:side>
        <x-admin.card title="إجراءات سريعة" icon="fa-bolt">
            <a href="{{ route('admin.blog.create') }}" class="w-full h-12 inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/20 transition-colors">
                <i class="fa-solid fa-plus"></i> مقال جديد
            </a>
        </x-admin.card>

        <x-admin.card title="بحث" icon="fa-search">
            <form method="GET" class="space-y-3">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="عنوان أو محتوى..."
                       class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                <button class="w-full h-11 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-xl text-sm font-bold">بحث</button>
            </form>
        </x-admin.card>
    </x-slot:side>
</x-admin.page>
@endsection
