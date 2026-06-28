@extends('admin.layouts.app')
@section('title', 'رسائل التواصل')
@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-6">رسائل التواصل</h1>
    @if(session('success'))<div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>@endif

    <form class="mb-4 flex gap-2">
        <input name="q" value="{{ request('q') }}" placeholder="بحث..." class="px-3 py-2 border rounded-lg">
        <select name="status" class="px-3 py-2 border rounded-lg">
            <option value="">كل الحالات</option>
            @foreach(['new'=>'جديد','read'=>'مقروء','replied'=>'تم الرد','archived'=>'مؤرشف'] as $k=>$v)
                <option value="{{ $k }}" @selected(request('status')==$k)>{{ $v }}</option>
            @endforeach
        </select>
        <button class="px-4 py-2 bg-slate-100 rounded-lg">بحث</button>
    </form>

    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50"><tr><th class="p-3">الاسم</th><th class="p-3">البريد</th><th class="p-3">الموضوع</th><th class="p-3">الحالة</th><th class="p-3">التاريخ</th><th class="p-3"></th></tr></thead>
            <tbody>
            @forelse($messages as $m)
                <tr class="border-t {{ $m->status==='new' ? 'bg-amber-50' : '' }}">
                    <td class="p-3 font-semibold">{{ $m->name }}</td>
                    <td class="p-3 text-xs">{{ $m->email }}</td>
                    <td class="p-3">{{ \Illuminate\Support\Str::limit($m->subject, 50) }}</td>
                    <td class="p-3 text-center"><span class="text-xs px-2 py-1 rounded-full bg-slate-100">{{ $m->status }}</span></td>
                    <td class="p-3 text-xs">{{ $m->created_at->diffForHumans() }}</td>
                    <td class="p-3 text-center"><a href="{{ route('admin.messages.show', $m) }}" class="text-blue-600"><i class="fas fa-eye"></i></a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="p-6 text-center text-slate-400">لا توجد رسائل</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $messages->links() }}</div>
</div>
@endsection
