@extends('admin.layouts.app')
@section('title', 'العملاء')
@section('content')
<div class="p-6 max-w-7xl mx-auto">
    @if(session('success'))<div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl">{{ session('success') }}</div>@endif

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900">العملاء</h1>
            <p class="text-sm text-slate-500 mt-1">إدارة جميع العملاء المسجلين بالموقع.</p>
        </div>
        <a href="{{ route('admin.customer-groups.index') }}" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-sm font-bold"><i class="fa-solid fa-layer-group ml-1"></i> مجموعات العملاء</a>
    </div>

    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-2 mb-4">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="بحث (اسم / إيميل / هاتف)..." class="h-10 px-3 border border-slate-200 rounded-xl text-sm md:col-span-2">
        <select name="group" class="h-10 px-3 border border-slate-200 rounded-xl text-sm">
            <option value="">كل المجموعات</option>
            @foreach($groups as $g)<option value="{{ $g->id }}" @selected(request('group')==$g->id)>{{ $g->name }}</option>@endforeach
        </select>
        <select name="status" class="h-10 px-3 border border-slate-200 rounded-xl text-sm">
            <option value="">كل الحالات</option>
            <option value="active" @selected(request('status')==='active')>مفعل</option>
            <option value="banned" @selected(request('status')==='banned')>محظور</option>
        </select>
        <button class="h-10 px-4 bg-slate-900 text-white rounded-xl text-sm font-bold md:col-span-4">فلتر</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm min-w-[800px]">
            <thead class="bg-slate-50 text-slate-600 text-xs"><tr>
                <th class="p-3 text-right">العميل</th><th class="p-3">المجموعة</th><th class="p-3">الطلبات</th>
                <th class="p-3">الحالة</th><th class="p-3">تاريخ التسجيل</th><th class="p-3">إجراءات</th>
            </tr></thead>
            <tbody>
                @forelse($customers as $c)
                <tr class="border-t border-slate-100">
                    <td class="p-3">
                        <div class="font-bold text-slate-800">{{ $c->name }}</div>
                        <div class="text-xs text-slate-500">{{ $c->email }}</div>
                    </td>
                    <td class="p-3 text-center text-xs">
                        @if($c->customerGroup)
                            <span class="px-2 py-1 rounded-full bg-{{ $c->customerGroup->badge_color }}-50 text-{{ $c->customerGroup->badge_color }}-700 font-bold">{{ $c->customerGroup->name }}</span>
                        @else <span class="text-slate-400">—</span>@endif
                    </td>
                    <td class="p-3 text-center font-bold">{{ $c->orders_count }}</td>
                    <td class="p-3 text-center">
                        @if($c->is_active)<span class="px-2 py-1 text-xs bg-emerald-50 text-emerald-700 rounded-full font-bold">مفعل</span>
                        @else<span class="px-2 py-1 text-xs bg-rose-50 text-rose-700 rounded-full font-bold">محظور</span>@endif
                    </td>
                    <td class="p-3 text-center text-xs text-slate-500">{{ $c->created_at->format('Y-m-d') }}</td>
                    <td class="p-3 text-center">
                        <a href="{{ route('admin.customers.show', $c) }}" class="text-violet-600 hover:underline font-bold text-xs">عرض</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="p-8 text-center text-slate-400">لا يوجد عملاء.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $customers->links() }}</div>
</div>
@endsection
