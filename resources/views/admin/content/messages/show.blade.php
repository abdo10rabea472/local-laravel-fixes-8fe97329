@extends('admin.layouts.app')
@section('title', 'رسالة')
@section('content')
<div class="p-6 max-w-3xl">
    <a href="{{ route('admin.messages.index') }}" class="text-slate-500 text-sm">← رجوع للقائمة</a>
    <div class="bg-white p-6 rounded-xl shadow mt-4">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h1 class="text-xl font-bold">{{ $message->subject }}</h1>
                <p class="text-sm text-slate-500">من <strong>{{ $message->name }}</strong> &lt;{{ $message->email }}&gt; — {{ $message->created_at->format('Y-m-d H:i') }}</p>
                @if($message->phone)<p class="text-sm text-slate-500">هاتف: {{ $message->phone }}</p>@endif
            </div>
            <span class="px-3 py-1 rounded-full bg-slate-100 text-xs">{{ $message->status }}</span>
        </div>
        <div class="prose max-w-none whitespace-pre-wrap p-4 bg-slate-50 rounded-lg">{{ $message->message }}</div>

        <div class="flex gap-2 mt-6 flex-wrap">
            <a href="mailto:{{ $message->email }}?subject=Re: {{ $message->subject }}" class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm"><i class="fas fa-reply"></i> الرد عبر البريد</a>
            @foreach(['new'=>'جديد','read'=>'مقروء','replied'=>'تم الرد','archived'=>'أرشفة'] as $k=>$v)
                <form method="POST" action="{{ route('admin.messages.status', $message) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $k }}"><button class="px-3 py-2 bg-slate-100 rounded-lg text-sm">{{ $v }}</button></form>
            @endforeach
            <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" onsubmit="return confirm('حذف؟')">@csrf @method('DELETE')<button class="px-3 py-2 bg-red-600 text-white rounded-lg text-sm">حذف</button></form>
        </div>
    </div>
</div>
@endsection
