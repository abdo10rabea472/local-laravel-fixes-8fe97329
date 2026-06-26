@extends('admin.settings.layout')

@section('settings-content')
<div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 px-6 py-4 border-b border-slate-100">
        <div>
            <h3 class="text-base font-bold text-slate-800">الصفحات الثابتة</h3>
            <p class="text-xs text-slate-500 mt-1">إدارة محتوى صفحات FAQs، Privacy، Returns، إلخ.</p>
        </div>
        <a href="{{ route('admin.pages.create') }}" class="bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-sm px-5 py-2.5 rounded-xl shadow-md shadow-emerald-500/20 text-center transition-colors">
            <i class="fa-solid fa-plus ml-2"></i> إضافة صفحة
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="text-right p-4">العنوان</th>
                    <th class="text-right p-4">المعرف</th>
                    <th class="text-right p-4">الحالة</th>
                    <th class="text-right p-4">الترتيب</th>
                    <th class="text-right p-4">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pages as $page)
                <tr class="border-t border-slate-100">
                    <td class="p-4 font-semibold text-slate-800">{{ $page->title }}</td>
                    <td class="p-4 text-slate-500 font-mono">{{ $page->slug }}</td>
                    <td class="p-4">
                        @if($page->status)
                            <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">نشط</span>
                        @else
                            <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-full">معطل</span>
                        @endif
                    </td>
                    <td class="p-4">{{ $page->sort_order }}</td>
                    <td class="p-4">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.pages.edit', $page) }}" class="text-violet-600 hover:text-violet-800">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" data-ajax-confirm="حذف الصفحة؟" data-ajax-remove>
                                @csrf @method('DELETE')
                                <button type="submit" class="text-rose-500 hover:text-rose-700">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="p-8 text-center text-slate-500">لا توجد صفحات مضافة.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-slate-100">{{ $pages->links() }}</div>
</div>
@endsection
