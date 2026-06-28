@extends('admin.layouts.app')
@section('title', 'رسائل التواصل')

@php
    $statusMap = ['new'=>'جديد','read'=>'مقروء','replied'=>'تم الرد','archived'=>'مؤرشف'];
    $statusColors = [
        'new'      => 'bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400',
        'read'     => 'bg-sky-50 dark:bg-sky-950/30 text-sky-700 dark:text-sky-400',
        'replied'  => 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400',
        'archived' => 'bg-gray-100 dark:bg-dark-800 text-gray-600 dark:text-gray-400',
    ];
@endphp

@section('content')
<x-admin.page title="رسائل التواصل" subtitle="رسائل العملاء الواردة من نموذج التواصل في الموقع.">
    <x-admin.card title="كل الرسائل" icon="fa-envelope" padding="p-0">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-dark-800 text-gray-500 dark:text-gray-400 text-xs">
                    <tr>
                        <th class="p-3 text-right">المرسل</th>
                        <th class="p-3">البريد</th>
                        <th class="p-3">الموضوع</th>
                        <th class="p-3">الحالة</th>
                        <th class="p-3">التاريخ</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($messages as $m)
                    <tr class="border-t border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-dark-800/50 {{ $m->status==='new' ? 'bg-amber-50/40 dark:bg-amber-950/10' : '' }}">
                        <td class="p-3 font-bold text-gray-900 dark:text-white">{{ $m->name }}</td>
                        <td class="p-3 text-xs text-gray-600 dark:text-gray-400 font-mono">{{ $m->email }}</td>
                        <td class="p-3 text-gray-700 dark:text-gray-300">{{ \Illuminate\Support\Str::limit($m->subject, 50) }}</td>
                        <td class="p-3 text-center">
                            <span class="px-2 py-1 text-xs rounded-full font-bold {{ $statusColors[$m->status] ?? '' }}">{{ $statusMap[$m->status] ?? $m->status }}</span>
                        </td>
                        <td class="p-3 text-center text-xs text-gray-500">{{ $m->created_at->diffForHumans() }}</td>
                        <td class="p-3 text-center">
                            <a href="{{ route('admin.messages.show', $m) }}" class="text-primary-600 hover:underline text-xs font-bold">عرض</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-12 text-center text-gray-400">
                        <i class="fa-regular fa-envelope text-3xl mb-3 block"></i>
                        لا توجد رسائل.
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($messages->hasPages())
        <div class="p-4 border-t border-gray-100 dark:border-gray-800">{{ $messages->links() }}</div>
        @endif
    </x-admin.card>

    <x-slot:side>
        <x-admin.card title="فلترة" icon="fa-filter">
            <form method="GET" class="space-y-3">
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">بحث</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="اسم/بريد/موضوع..."
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">الحالة</label>
                    <select name="status" class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                        <option value="">كل الحالات</option>
                        @foreach($statusMap as $k=>$v)
                            <option value="{{ $k }}" @selected(request('status')==$k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="w-full h-11 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-xl text-sm font-bold">تطبيق</button>
            </form>
        </x-admin.card>
    </x-slot:side>
</x-admin.page>
@endsection
