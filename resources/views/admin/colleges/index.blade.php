@extends('admin.layouts.app')

@section('title', 'تصنيفات الكليات')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white border border-slate-200 p-6 rounded-3xl shadow-sm">
        <div>
            <h3 class="text-base font-bold text-slate-800">تصنيفات الكليات</h3>
            <p class="text-xs text-slate-500 mt-1">إدارة صفحات الكليات: الاسم، الأيقونة، الألوان، الوصف، وSEO</p>
        </div>
        <a href="{{ route('admin.colleges.create') }}"
            class="bg-gradient-to-r from-violet-600 to-indigo-600 text-white font-bold text-sm px-6 py-3 rounded-2xl shadow-lg inline-flex items-center gap-2 self-start">
            <i class="fa-solid fa-plus"></i> إضافة كلية
        </a>
    </div>

    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="text-right p-4">الكلية</th>
                        <th class="text-right p-4">الألوان</th>
                        <th class="text-right p-4">تصنيفات فرعية</th>
                        <th class="text-right p-4">منتجات</th>
                        <th class="text-right p-4">الحالة</th>
                        <th class="text-right p-4">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($colleges as $college)
                    <tr class="border-t border-slate-100 hover:bg-slate-50/50">
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                @if($college->image)
                                    <img src="{{ asset('storage/' . $college->image) }}" class="h-10 w-10 object-contain rounded-xl bg-slate-50 p-1" alt="">
                                @else
                                    <div class="h-10 w-10 rounded-xl flex items-center justify-center text-white font-bold text-sm"
                                        style="background: {{ $college->primary_color ?? '#6366f1' }}">
                                        {{ substr($college->name, 0, 2) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="font-bold text-slate-800">{{ $college->name }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $college->slug }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="flex gap-1">
                                <span class="h-6 w-6 rounded-lg border border-slate-200" style="background:{{ $college->primary_color ?? '#6366f1' }}"></span>
                                <span class="h-6 w-6 rounded-lg border border-slate-200" style="background:{{ $college->secondary_color ?? '#8b5cf6' }}"></span>
                            </div>
                        </td>
                        <td class="p-4 font-mono">{{ $college->children_count }}</td>
                        <td class="p-4 font-mono">{{ $college->products_count }}</td>
                        <td class="p-4">
                            <span class="px-2 py-1 rounded-full text-xs font-bold {{ $college->status ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $college->status ? 'نشط' : 'معطل' }}
                            </span>
                        </td>
                        <td class="p-4">
                            <div class="flex gap-2">
                                <a href="{{ route('admin.colleges.edit', $college) }}" class="text-violet-600 hover:text-violet-800" title="تعديل">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <a href="{{ route('admin.subcategories.index', ['college_id' => $college->id]) }}" class="text-indigo-600 hover:text-indigo-800" title="التصنيفات الفرعية">
                                    <i class="fa-solid fa-sitemap"></i>
                                </a>
                                <a href="{{ route('category.show', $college->slug) }}" target="_blank" class="text-slate-500 hover:text-slate-700" title="معاينة">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.colleges.destroy', $college) }}" data-ajax-confirm="حذف الكلية؟" data-ajax-remove>
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-rose-500 hover:text-rose-700"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-10 text-center text-slate-500">
                            <i class="fa-solid fa-building-columns text-3xl text-slate-300 mb-3 block"></i>
                            لا توجد كليات بعد.
                            <a href="{{ route('admin.colleges.create') }}" class="text-violet-600 font-bold block mt-2">أضف أول كلية</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($colleges->hasPages())
        <div class="p-4 border-t border-slate-100">{{ $colleges->links() }}</div>
        @endif
    </div>
</div>
@endsection
