@extends('admin.layouts.app')
@section('title', 'أكواد الخصم')

@section('content')
<div class="p-6 max-w-7xl mx-auto">
    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900">أكواد الخصم</h1>
            <p class="text-sm text-slate-500 mt-1">إدارة جميع كوبونات الخصم المتاحة في المتجر.</p>
        </div>
        <a href="{{ route('admin.coupons.create') }}" class="px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white font-bold rounded-xl">
            <i class="fa-solid fa-plus ml-1"></i> إنشاء كود جديد
        </a>
    </div>

    <form method="GET" class="flex gap-2 mb-4">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="بحث بالكود..." class="h-10 px-3 border border-slate-200 rounded-xl text-sm flex-1">
        <select name="status" class="h-10 px-3 border border-slate-200 rounded-xl text-sm">
            <option value="">كل الحالات</option>
            <option value="active" @selected(request('status')==='active')>مفعل</option>
            <option value="inactive" @selected(request('status')==='inactive')>غير مفعل</option>
        </select>
        <button class="h-10 px-4 bg-slate-900 text-white rounded-xl text-sm font-bold">فلتر</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600 text-xs">
                <tr>
                    <th class="p-3 text-right">الكود</th>
                    <th class="p-3">القيمة</th>
                    <th class="p-3">النطاق</th>
                    <th class="p-3">الصلاحية</th>
                    <th class="p-3">الاستخدام</th>
                    <th class="p-3">الحالة</th>
                    <th class="p-3">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($coupons as $c)
                <tr class="border-t border-slate-100">
                    <td class="p-3 font-mono font-bold text-violet-700">{{ $c->code }}</td>
                    <td class="p-3 text-center">{{ $c->type === 'percent' ? $c->value . '%' : number_format($c->value, 2) . ' EGP' }}</td>
                    <td class="p-3 text-center text-xs">{{ ['all'=>'الكل','products'=>'منتجات','categories'=>'أقسام'][$c->scope] }}</td>
                    <td class="p-3 text-center text-xs text-slate-500">
                        {{ $c->starts_at?->format('Y-m-d') ?: '—' }} → {{ $c->ends_at?->format('Y-m-d') ?: '∞' }}
                    </td>
                    <td class="p-3 text-center text-xs">{{ $c->used_count }} / {{ $c->usage_limit ?: '∞' }}</td>
                    <td class="p-3 text-center">
                        @if($c->is_active)
                            <span class="px-2 py-1 text-xs bg-emerald-50 text-emerald-700 rounded-full font-bold">مفعل</span>
                        @else
                            <span class="px-2 py-1 text-xs bg-slate-100 text-slate-600 rounded-full font-bold">معطل</span>
                        @endif
                    </td>
                    <td class="p-3 text-center">
                        <a href="{{ route('admin.coupons.edit', $c) }}" class="text-violet-600 hover:underline text-xs font-bold">تعديل</a>
                        <form action="{{ route('admin.coupons.toggle', $c) }}" method="POST" class="inline" data-ajax-toggle>
                            @csrf @method('PATCH')
                            <button data-toggle-state="{{ $c->is_active ? 'on' : 'off' }}" data-toggle-on="تعطيل" data-toggle-off="تفعيل" class="text-amber-600 hover:underline text-xs font-bold mx-2">{{ $c->is_active ? 'تعطيل' : 'تفعيل' }}</button>
                        </form>
                        <form action="{{ route('admin.coupons.destroy', $c) }}" method="POST" class="inline" data-ajax-confirm="حذف الكود؟" data-ajax-remove>
                            @csrf @method('DELETE')
                            <button class="text-rose-600 hover:underline text-xs font-bold">حذف</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="p-8 text-center text-slate-400">لا توجد أكواد بعد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $coupons->links() }}</div>
</div>
@endsection
