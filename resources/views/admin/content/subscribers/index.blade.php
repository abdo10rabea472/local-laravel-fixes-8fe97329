@extends('admin.layouts.app')
@section('title', 'مشتركو النشرة')

@section('content')
<x-admin.page title="مشتركو النشرة البريدية" subtitle="قائمة جميع المشتركين في النشرة البريدية للمتجر.">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-admin.card padding="p-5">
            <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">إجمالي المشتركين</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($total) }}</p>
        </x-admin.card>
        <x-admin.card padding="p-5">
            <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">المفعّلون</p>
            <p class="text-3xl font-bold text-emerald-600 mt-2">{{ number_format($active) }}</p>
        </x-admin.card>
    </div>

    <x-admin.card title="كل المشتركين" icon="fa-envelope-open-text" padding="p-0">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-dark-800 text-gray-500 dark:text-gray-400 text-xs">
                    <tr>
                        <th class="p-3 text-right">البريد الإلكتروني</th>
                        <th class="p-3">الحالة</th>
                        <th class="p-3">تاريخ الاشتراك</th>
                        <th class="p-3">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($subscribers as $s)
                    <tr class="border-t border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-dark-800/50">
                        <td class="p-3 font-mono text-gray-900 dark:text-white">{{ $s->email }}</td>
                        <td class="p-3 text-center">
                            @if($s->active)
                                <span class="px-2 py-1 text-xs bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 rounded-full font-bold">مفعّل</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-rose-50 dark:bg-rose-950/30 text-rose-600 rounded-full font-bold">معطّل</span>
                            @endif
                        </td>
                        <td class="p-3 text-center text-xs text-gray-500">{{ $s->subscribed_at?->format('Y-m-d') ?? '—' }}</td>
                        <td class="p-3 text-center whitespace-nowrap">
                            <form method="POST" action="{{ route('admin.subscribers.toggle', $s) }}" class="inline">
                                @csrf @method('PATCH')
                                <button class="text-amber-600 hover:underline text-xs font-bold">{{ $s->active ? 'تعطيل' : 'تفعيل' }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.subscribers.destroy', $s) }}" class="inline mr-2" onsubmit="return confirm('حذف؟')">
                                @csrf @method('DELETE')
                                <button class="text-rose-600 hover:underline text-xs font-bold">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-12 text-center text-gray-400">
                        <i class="fa-regular fa-envelope-open text-3xl mb-3 block"></i>
                        لا يوجد مشتركون بعد.
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($subscribers->hasPages())
        <div class="p-4 border-t border-gray-100 dark:border-gray-800">{{ $subscribers->links() }}</div>
        @endif
    </x-admin.card>

    <x-slot:side>
        <x-admin.card title="إرسال مقال للمشتركين" icon="fa-paper-plane">
            <form method="POST" action="{{ route('admin.subscribers.send-article') }}" class="space-y-3"
                  onsubmit="return confirm('سيتم إرسال المقال إلى جميع المشتركين المفعّلين. متابعة؟');">
                @csrf
                <select name="blog_post_id" required
                        class="w-full h-11 px-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                    <option value="">اختر مقالاً...</option>
                    @foreach($posts as $p)
                        <option value="{{ $p->id }}">{{ $p->title }}</option>
                    @endforeach
                </select>
                <button class="w-full h-12 inline-flex items-center justify-center gap-2 bg-violet-600 hover:bg-violet-700 text-white font-bold rounded-xl shadow-lg shadow-violet-500/20">
                    <i class="fa-solid fa-paper-plane"></i> إرسال للمشتركين
                </button>
                @if(session('success'))
                    <p class="text-xs text-emerald-600 font-bold">{{ session('success') }}</p>
                @endif
            </form>
        </x-admin.card>

        <x-admin.card title="إجراءات سريعة" icon="fa-bolt">
            <a href="{{ route('admin.subscribers.export') }}" class="w-full h-12 inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-500/20">
                <i class="fa-solid fa-download"></i> تصدير CSV
            </a>
        </x-admin.card>


        <x-admin.card title="بحث" icon="fa-search">
            <form method="GET" class="space-y-3">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="بحث بالبريد..."
                       class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                <button class="w-full h-11 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-xl text-sm font-bold">بحث</button>
            </form>
        </x-admin.card>
    </x-slot:side>
</x-admin.page>
@endsection
