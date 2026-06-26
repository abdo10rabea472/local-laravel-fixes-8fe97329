@extends('admin.layouts.app')
@section('title', 'العملاء')

@section('content')
<x-admin.page title="العملاء" subtitle="إدارة جميع العملاء المسجلين بالموقع.">
    <x-admin.card title="قائمة العملاء" icon="fa-users" padding="p-0">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead class="bg-gray-50 dark:bg-dark-800 text-gray-500 dark:text-gray-400 text-xs">
                    <tr>
                        <th class="p-3 text-right">العميل</th>
                        <th class="p-3">المجموعة</th>
                        <th class="p-3">الطلبات</th>
                        <th class="p-3">الحالة</th>
                        <th class="p-3">تاريخ التسجيل</th>
                        <th class="p-3">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $c)
                    <tr class="border-t border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-dark-800/50">
                        <td class="p-3">
                            <div class="font-bold text-gray-800 dark:text-gray-200">{{ $c->name }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $c->email }}</div>
                        </td>
                        <td class="p-3 text-center text-xs">
                            @if($c->customerGroup)
                                <span class="px-2 py-1 rounded-full bg-{{ $c->customerGroup->badge_color }}-50 dark:bg-{{ $c->customerGroup->badge_color }}-950/30 text-{{ $c->customerGroup->badge_color }}-700 dark:text-{{ $c->customerGroup->badge_color }}-400 font-bold">{{ $c->customerGroup->name }}</span>
                            @else <span class="text-gray-400">—</span>@endif
                        </td>
                        <td class="p-3 text-center font-bold text-gray-900 dark:text-white">{{ $c->orders_count }}</td>
                        <td class="p-3 text-center">
                            @if($c->is_active)<span class="px-2 py-1 text-xs bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 rounded-full font-bold">مفعل</span>
                            @else<span class="px-2 py-1 text-xs bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-400 rounded-full font-bold">محظور</span>@endif
                        </td>
                        <td class="p-3 text-center text-xs text-gray-500 dark:text-gray-400">{{ $c->created_at->format('Y-m-d') }}</td>
                        <td class="p-3 text-center">
                            <a href="{{ route('admin.customers.show', $c) }}" class="text-primary-600 hover:underline font-bold text-xs">عرض</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="p-12 text-center text-gray-400">
                        <i class="fas fa-users text-3xl mb-3 block"></i>
                        لا يوجد عملاء.
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
        <div class="p-4 border-t border-gray-100 dark:border-gray-800">{{ $customers->links() }}</div>
        @endif
    </x-admin.card>

    <x-slot:side>
        <x-admin.card title="إجراءات" icon="fa-bolt">
            <a href="{{ route('admin.customer-groups.index') }}" class="w-full h-11 inline-flex items-center justify-center gap-2 bg-gray-900 dark:bg-dark-700 text-white font-bold rounded-xl hover:bg-gray-800 transition-colors">
                <i class="fa-solid fa-layer-group"></i> مجموعات العملاء
            </a>
        </x-admin.card>

        <x-admin.card title="فلترة وبحث" icon="fa-filter">
            <form method="GET" class="space-y-3">
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">بحث</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="اسم / إيميل / هاتف"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">المجموعة</label>
                    <select name="group" class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                        <option value="">كل المجموعات</option>
                        @foreach($groups as $g)<option value="{{ $g->id }}" @selected(request('group')==$g->id)>{{ $g->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">الحالة</label>
                    <select name="status" class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                        <option value="">كل الحالات</option>
                        <option value="active" @selected(request('status')==='active')>مفعل</option>
                        <option value="banned" @selected(request('status')==='banned')>محظور</option>
                    </select>
                </div>
                <button type="submit" class="w-full h-11 bg-gray-900 dark:bg-dark-700 text-white font-bold rounded-xl hover:bg-gray-800 transition-colors">
                    <i class="fas fa-filter ml-1"></i> تطبيق
                </button>
            </form>
        </x-admin.card>
    </x-slot:side>
</x-admin.page>
@endsection
