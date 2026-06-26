@extends('admin.layouts.app')
@section('title', 'مجموعات العملاء')
@section('content')
<div class="p-6 max-w-5xl mx-auto" x-data="{ editing: null, form: { name:'', discount_percent:0, description:'', badge_color:'violet', is_active:true } }">
    @if(session('success'))<div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl">{{ session('success') }}</div>@endif

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900">مجموعات العملاء</h1>
            <p class="text-sm text-slate-500 mt-1">VIP، Wholesale، خصم خاص لكل مجموعة.</p>
        </div>
    </div>

    <!-- Create form -->
    <form method="POST" action="{{ route('admin.customer-groups.store') }}" class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 grid grid-cols-1 md:grid-cols-5 gap-3">
        @csrf
        <input type="text" name="name" placeholder="اسم المجموعة (مثال: VIP)" required class="h-10 px-3 border border-slate-200 rounded-xl text-sm">
        <input type="number" step="0.01" min="0" max="100" name="discount_percent" placeholder="نسبة الخصم %" required class="h-10 px-3 border border-slate-200 rounded-xl text-sm">
        <select name="badge_color" class="h-10 px-3 border border-slate-200 rounded-xl text-sm">
            @foreach(['violet','emerald','sky','amber','rose','slate','indigo'] as $c)
                <option value="{{ $c }}">{{ $c }}</option>
            @endforeach
        </select>
        <input type="text" name="description" placeholder="وصف (اختياري)" class="h-10 px-3 border border-slate-200 rounded-xl text-sm">
        <button class="h-10 px-4 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-sm font-bold">إضافة</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600 text-xs"><tr>
                <th class="p-3 text-right">المجموعة</th><th class="p-3">الخصم</th><th class="p-3">العملاء</th><th class="p-3">الحالة</th><th class="p-3">إجراءات</th>
            </tr></thead>
            <tbody>
                @forelse($groups as $g)
                <tr class="border-t border-slate-100">
                    <td class="p-3">
                        <span class="px-2 py-1 rounded-full bg-{{ $g->badge_color }}-50 text-{{ $g->badge_color }}-700 font-bold">{{ $g->name }}</span>
                        @if($g->description)<p class="text-xs text-slate-500 mt-1">{{ $g->description }}</p>@endif
                    </td>
                    <td class="p-3 text-center font-bold text-emerald-600">{{ $g->discount_percent }}%</td>
                    <td class="p-3 text-center">{{ $g->users_count }}</td>
                    <td class="p-3 text-center">
                        @if($g->is_active)<span class="text-xs text-emerald-700">مفعل</span>@else<span class="text-xs text-rose-700">معطل</span>@endif
                    </td>
                    <td class="p-3 text-center">
                        <form method="POST" action="{{ route('admin.customer-groups.update', $g) }}" class="inline" x-data="{ open:false }">
                            @csrf @method('PUT')
                            <button type="button" @click="open=!open" class="text-violet-600 text-xs font-bold">تعديل</button>
                            <div x-show="open" x-cloak class="text-right p-3 bg-slate-50 rounded-xl mt-2 space-y-2" @click.stop>
                                <input type="text" name="name" value="{{ $g->name }}" class="w-full h-9 px-2 border border-slate-200 rounded-lg text-sm">
                                <input type="number" step="0.01" name="discount_percent" value="{{ $g->discount_percent }}" class="w-full h-9 px-2 border border-slate-200 rounded-lg text-sm">
                                <select name="badge_color" class="w-full h-9 px-2 border border-slate-200 rounded-lg text-sm">
                                    @foreach(['violet','emerald','sky','amber','rose','slate','indigo'] as $c)
                                        <option value="{{ $c }}" @selected($g->badge_color===$c)>{{ $c }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="description" value="{{ $g->description }}" class="w-full h-9 px-2 border border-slate-200 rounded-lg text-sm">
                                <label class="text-xs flex gap-1"><input type="checkbox" name="is_active" value="1" @checked($g->is_active)> مفعل</label>
                                <button class="w-full h-9 bg-violet-600 text-white rounded-lg text-sm font-bold">حفظ</button>
                            </div>
                        </form>
                        <form method="POST" action="{{ route('admin.customer-groups.destroy', $g) }}" class="inline" onsubmit="return confirm('تأكيد الحذف؟')">
                            @csrf @method('DELETE')
                            <button class="text-rose-600 text-xs font-bold mr-2">حذف</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="p-8 text-center text-slate-400">لا توجد مجموعات بعد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
