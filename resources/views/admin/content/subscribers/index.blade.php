@extends('admin.layouts.app')
@section('title', 'مشتركي النشرة')
@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">مشتركي النشرة البريدية</h1>
        <a href="{{ route('admin.subscribers.export') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm"><i class="fas fa-download"></i> تصدير CSV</a>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-white p-4 rounded-xl shadow"><p class="text-xs text-slate-500">إجمالي</p><p class="text-2xl font-bold">{{ $total }}</p></div>
        <div class="bg-white p-4 rounded-xl shadow"><p class="text-xs text-slate-500">المفعّلون</p><p class="text-2xl font-bold text-emerald-600">{{ $active }}</p></div>
    </div>

    <form class="mb-4"><input name="q" value="{{ request('q') }}" placeholder="بحث بالبريد..." class="px-3 py-2 border rounded-lg w-72"></form>

    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50"><tr><th class="p-3">البريد</th><th class="p-3">الحالة</th><th class="p-3">الاشتراك</th><th class="p-3"></th></tr></thead>
            <tbody>
            @forelse($subscribers as $s)
                <tr class="border-t">
                    <td class="p-3 font-mono">{{ $s->email }}</td>
                    <td class="p-3 text-center">
                        @if($s->active)<span class="text-emerald-700 bg-emerald-100 px-2 py-1 rounded-full text-xs">مفعّل</span>
                        @else<span class="text-red-700 bg-red-100 px-2 py-1 rounded-full text-xs">معطّل</span>@endif
                    </td>
                    <td class="p-3 text-xs">{{ $s->subscribed_at?->format('Y-m-d') }}</td>
                    <td class="p-3 text-center">
                        <form method="POST" action="{{ route('admin.subscribers.toggle', $s) }}" class="inline">@csrf @method('PATCH')<button class="text-blue-600 text-xs">{{ $s->active ? 'تعطيل' : 'تفعيل' }}</button></form>
                        <form method="POST" action="{{ route('admin.subscribers.destroy', $s) }}" class="inline mr-2" onsubmit="return confirm('حذف؟')">@csrf @method('DELETE')<button class="text-red-600"><i class="fas fa-trash"></i></button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="p-6 text-center text-slate-400">لا يوجد مشتركون</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $subscribers->links() }}</div>
</div>
@endsection
