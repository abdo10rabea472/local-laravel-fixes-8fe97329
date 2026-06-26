@extends('admin.layouts.app')

@section('title', 'Page Builder')

@section('content')
<div class="space-y-6" x-data="{
    showModal: false,
    isEdit: false,
    form: { id: null, category_id: '{{ $categoryId }}', section_type: 'banner', title: '', content: '', sort_order: 0, status: true, background_image: null },
    openCreate() {
        this.isEdit = false;
        this.form = { id: null, category_id: '{{ $categoryId }}', section_type: 'banner', title: '', content: '', sort_order: 0, status: true, background_image: null };
        this.showModal = true;
    },
    openEdit(section) {
        this.isEdit = true;
        this.form = {
            ...section,
            content: typeof section.content === 'object' ? JSON.stringify(section.content) : (section.content || ''),
            status: !!section.status
        };
        this.showModal = true;
    }
}">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white border border-slate-200 p-6 rounded-3xl shadow-sm">
        <div>
            <h3 class="text-base font-bold text-slate-800">Page Builder</h3>
            <p class="text-xs text-slate-500 mt-1">أقسام ديناميكية لصفحات التصنيفات</p>
        </div>
        <button @click="openCreate()" class="bg-gradient-to-r from-violet-600 to-indigo-600 text-white font-bold text-sm px-6 py-3 rounded-2xl shadow-lg">
            <i class="fa-solid fa-plus ml-2"></i> إضافة قسم
        </button>
    </div>

    <form method="GET" class="bg-white border border-slate-200 p-4 rounded-3xl shadow-sm flex gap-4 items-end">
        <div class="flex-1">
            <label class="text-xs font-bold text-slate-500">تصفية حسب التصنيف</label>
            <select name="category_id" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                <option value="">كل التصنيفات</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected($categoryId == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="h-11 px-6 bg-slate-800 text-white font-bold rounded-2xl">عرض</button>
    </form>

    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="text-right p-4">التصنيف</th>
                    <th class="text-right p-4">النوع</th>
                    <th class="text-right p-4">العنوان</th>
                    <th class="text-right p-4">الترتيب</th>
                    <th class="text-right p-4">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sections as $section)
                <tr class="border-t border-slate-100">
                    <td class="p-4">{{ $section->category->name }}</td>
                    <td class="p-4"><span class="px-2 py-1 bg-violet-50 text-violet-700 rounded-lg text-xs font-bold">{{ $section->section_type }}</span></td>
                    <td class="p-4">{{ $section->title ?? '—' }}</td>
                    <td class="p-4">{{ $section->sort_order }}</td>
                    <td class="p-4">
                        <div class="flex gap-2">
                            <button @click="openEdit(@js($section))" class="text-violet-600"><i class="fa-solid fa-pen"></i></button>
                            <form method="POST" action="{{ route('admin.sections.destroy', $section) }}" data-ajax-confirm="حذف القسم؟" data-ajax-remove>
                                @csrf @method('DELETE')
                                <button type="submit" class="text-rose-500"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="p-8 text-center text-slate-500">لا توجد أقسام.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t">{{ $sections->links() }}</div>
    </div>

    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-xl p-6">
            <h4 class="text-lg font-bold mb-4" x-text="isEdit ? 'تعديل قسم' : 'إضافة قسم'"></h4>
            <form :action="isEdit ? '{{ url('admin/sections') }}/' + form.id : '{{ route('admin.sections.store') }}'" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>

                <div>
                    <label class="text-xs font-bold text-slate-500">التصنيف *</label>
                    <select name="category_id" x-model="form.category_id" required class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500">نوع القسم *</label>
                    <select name="section_type" x-model="form.section_type" required class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                        @foreach(\App\Models\PageSection::TYPES as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500">العنوان</label>
                    <input type="text" name="title" x-model="form.title" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500">المحتوى (JSON أو نص)</label>
                    <textarea name="content" x-model="form.content" rows="4" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-mono"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-slate-500">الترتيب</label>
                        <input type="number" name="sort_order" x-model="form.sort_order" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">صورة</label>
                        <input type="file" name="image" accept="image/*" class="w-full text-sm">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500">صورة الخلفية</label>
                    <input type="file" name="background_image" accept="image/*" class="w-full text-sm">
                    <label class="flex items-center gap-2 text-sm text-rose-600 cursor-pointer mt-2">
                        <input type="checkbox" name="remove_background_image" value="1">
                        حذف صورة الخلفية
                    </label>
                </div>
                <label class="flex items-center gap-2 text-sm font-semibold">
                    <input type="checkbox" name="status" value="1" x-model="form.status" class="rounded"> نشط
                </label>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 h-11 bg-violet-600 text-white font-bold rounded-2xl">حفظ</button>
                    <button type="button" @click="showModal = false" class="h-11 px-6 bg-slate-100 rounded-2xl font-bold">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
