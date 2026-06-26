@extends('admin.layouts.app')
@section('title', 'التصنيفات الفرعية')

@section('content')
<x-admin.page title="التصنيفات الفرعية للكليات" subtitle="تصنيفات داخل كل كلية — مثل: Clinical Tools, Electrical Engineering...">
    <x-admin.card title="كل التصنيفات الفرعية" icon="fa-sitemap" padding="p-0">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-dark-800 text-gray-500 dark:text-gray-400 text-xs">
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
                    <tr class="border-t border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-dark-800/50">
                        <td class="p-4">
                            <div class="font-bold text-gray-800 dark:text-gray-200">{{ $sub->name }}</div>
                            <div class="text-[11px] text-gray-400 font-mono">{{ $sub->slug }}</div>
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-1 bg-primary-50 dark:bg-primary-950/30 text-primary-700 dark:text-primary-400 rounded-lg text-xs font-bold">
                                {{ $sub->parent?->name }}
                            </span>
                        </td>
                        <td class="p-4 font-mono text-gray-700 dark:text-gray-300">{{ $sub->products_count }}</td>
                        <td class="p-4">
                            <span class="px-2 py-1 rounded-full text-xs font-bold {{ $sub->status ? 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400' : 'bg-gray-100 dark:bg-dark-800 text-gray-500' }}">
                                {{ $sub->status ? 'نشط' : 'معطل' }}
                            </span>
                        </td>
                        <td class="p-4">
                            <div class="flex gap-3">
                                <a href="{{ route('admin.subcategories.edit', $sub) }}" class="text-primary-600 hover:text-primary-700"><i class="fa-solid fa-pen"></i></a>
                                <a href="{{ route('category.show', $sub->slug) }}" target="_blank" class="text-gray-500 hover:text-gray-700"><i class="fa-solid fa-eye"></i></a>
                                <form method="POST" action="{{ route('admin.subcategories.destroy', $sub) }}" data-ajax-confirm="حذف التصنيف؟" data-ajax-remove>
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-rose-500 hover:text-rose-600"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="p-12 text-center text-gray-400">
                        <i class="fa-solid fa-sitemap text-3xl mb-3 block"></i>
                        لا توجد تصنيفات فرعية.
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($subcategories->hasPages())
        <div class="p-4 border-t border-gray-100 dark:border-gray-800">{{ $subcategories->links() }}</div>
        @endif
    </x-admin.card>

    <x-slot:side>
        <x-admin.card title="إجراءات سريعة" icon="fa-bolt">
            <a href="{{ route('admin.subcategories.create') }}" class="w-full h-12 inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/20 transition-colors">
                <i class="fa-solid fa-plus"></i> إضافة تصنيف فرعي
            </a>
        </x-admin.card>

        <x-admin.card title="تصفية" icon="fa-filter">
            <form method="GET" class="space-y-3">
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">الكلية</label>
                    <select name="college_id" class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                        <option value="">كل الكليات</option>
                        @foreach($colleges as $college)
                            <option value="{{ $college->id }}" @selected($collegeId == $college->id)>{{ $college->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="w-full h-11 bg-gray-900 dark:bg-dark-700 text-white font-bold rounded-xl hover:bg-gray-800 transition-colors">
                    <i class="fas fa-filter ml-1"></i> تطبيق
                </button>
                @if($collegeId)
                <a href="{{ route('admin.subcategories.index') }}" class="w-full h-11 inline-flex items-center justify-center bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-gray-300 font-bold rounded-xl hover:bg-gray-200 dark:hover:bg-dark-700 transition-colors">
                    إعادة تعيين
                </a>
                @endif
            </form>
        </x-admin.card>
    </x-slot:side>
</x-admin.page>
@endsection
