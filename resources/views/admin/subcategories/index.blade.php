@extends('admin.layouts.app')

@section('title', 'التصنيفات الفرعية')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white border border-slate-200 p-6 rounded-3xl shadow-sm">
        <div>
            <h3 class="text-base font-bold text-slate-800">التصنيفات الفرعية للكليات</h3>
            <p class="text-xs text-slate-500 mt-1">تصنيفات داخل كل كلية — مثل: Clinical Tools, Electrical Engineering...</p>
        </div>
        <a href="{{ route('admin.subcategories.create') }}"
            class="bg-gradient-to-r from-indigo-600 to-violet-600 text-white font-bold text-sm px-6 py-3 rounded-2xl shadow-lg inline-flex items-center gap-2 self-start">
            <i class="fa-solid fa-plus"></i> إضافة تصنيف فرعي
        </a>
    </div>

    <div class="bg-white border border-slate-200 p-4 rounded-3xl shadow-sm">
        <form method="GET" class="flex flex-col sm:flex-row gap-4 items-end">
            <div class="flex-1">
                <label class="text-xs font-bold text-slate-500">تصفية حسب الكلية</label>
                <select name="college_id" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm mt-1">
                    <option value="">كل الكليات</option>
                    @foreach($colleges as $college)
                        <option value="{{ $college->id }}" @selected($collegeId == $college->id)>{{ $college->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="h-11 px-6 bg-slate-800 text-white font-bold rounded-2xl">تصفية</button>
            @if($collegeId)
            <a href="{{ route('admin.subcategories.index') }}" class="h-11 px-4 bg-slate-100 rounded-2xl flex items-center text-slate-600">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
            @endif
        </form>
    </div>

    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="text-right p-4">التصنيف</th>
                    <th class="text-right p-4">الكلية</th>
                    <th class="text-right p-4">منتجات</th>
                    <th class="text-right p-4">الحالة</th>
                    <th class="text-right p-4">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subcategories as $sub)
                <tr class="border-t border-slate-100 hover:bg-slate-50/50">
                    <td class="p-4">
                        <div class="font-bold text-slate-800">{{ $sub->name }}</div>
                        <div class="text-[11px] text-slate-400 font-mono">{{ $sub->slug }}</div>
                    </td>
                    <td class="p-4">
                        <span class="px-2 py-1 bg-violet-50 text-violet-700 rounded-lg text-xs font-bold">
                            {{ $sub->parent?->name }}
                        </span>
                    </td>
                    <td class="p-4 font-mono">{{ $sub->products_count }}</td>
                    <td class="p-4">
                        <span class="px-2 py-1 rounded-full text-xs font-bold {{ $sub->status ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $sub->status ? 'نشط' : 'معطل' }}
                        </span>
                    </td>
                    <td class="p-4">
                        <div class="flex gap-2">
                            <a href="{{ route('admin.subcategories.edit', $sub) }}" class="text-violet-600"><i class="fa-solid fa-pen"></i></a>
                            <a href="{{ route('category.show', $sub->slug) }}" target="_blank" class="text-slate-500"><i class="fa-solid fa-eye"></i></a>
                            <form method="POST" action="{{ route('admin.subcategories.destroy', $sub) }}" data-ajax-confirm="حذف التصنيف؟" data-ajax-remove>
                                @csrf @method('DELETE')
                                <button type="submit" class="text-rose-500"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-10 text-center text-slate-500">
                        لا توجد تصنيفات فرعية.
                        <a href="{{ route('admin.subcategories.create') }}" class="text-indigo-600 font-bold block mt-2">أضف تصنيف فرعي</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($subcategories->hasPages())
        <div class="p-4 border-t">{{ $subcategories->links() }}</div>
        @endif
    </div>
</div>
@endsection
