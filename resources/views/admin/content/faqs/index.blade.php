@extends('admin.layouts.app')
@section('title', 'الأسئلة الشائعة')
@section('content')
<div class="p-6 max-w-5xl">
    <h1 class="text-2xl font-bold mb-6">الأسئلة الشائعة (FAQs)</h1>
    @if(session('success'))<div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="bg-red-100 text-red-700 p-3 rounded mb-4"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <div class="bg-white p-6 rounded-xl shadow mb-6">
        <h3 class="font-bold mb-3">إضافة سؤال</h3>
        <form method="POST" action="{{ route('admin.faqs.store') }}" class="space-y-3">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <input name="category" placeholder="التصنيف (مثال: الطلبات)" class="px-3 py-2 border rounded-lg">
                <input name="sort_order" type="number" value="0" placeholder="ترتيب" class="px-3 py-2 border rounded-lg">
            </div>
            <input name="question" required placeholder="السؤال" class="w-full px-3 py-2 border rounded-lg">
            <textarea name="answer" required rows="3" placeholder="الإجابة" class="w-full px-3 py-2 border rounded-lg"></textarea>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="active" value="1" checked> مفعّل</label>
            <button class="bg-primary-600 text-white px-4 py-2 rounded-lg">إضافة</button>
        </form>
    </div>

    <div class="space-y-3">
        @foreach($faqs as $f)
            <div class="bg-white p-4 rounded-xl shadow">
                <form method="POST" action="{{ route('admin.faqs.update', $f) }}" class="space-y-2">@csrf @method('PUT')
                    <div class="grid grid-cols-3 gap-2">
                        <input name="category" value="{{ $f->category }}" placeholder="تصنيف" class="px-2 py-1 border rounded text-sm">
                        <input name="sort_order" type="number" value="{{ $f->sort_order }}" class="px-2 py-1 border rounded text-sm">
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="active" value="1" @checked($f->active)> مفعّل</label>
                    </div>
                    <input name="question" value="{{ $f->question }}" class="w-full px-2 py-1 border rounded font-semibold">
                    <textarea name="answer" rows="2" class="w-full px-2 py-1 border rounded text-sm">{{ $f->answer }}</textarea>
                    <div class="flex gap-2">
                        <button class="bg-blue-600 text-white px-3 py-1 rounded text-sm">حفظ</button>
                </form>
                        <form method="POST" action="{{ route('admin.faqs.destroy', $f) }}" onsubmit="return confirm('حذف؟')">@csrf @method('DELETE')<button class="bg-red-600 text-white px-3 py-1 rounded text-sm">حذف</button></form>
                    </div>
            </div>
        @endforeach
    </div>
    <div class="mt-4">{{ $faqs->links() }}</div>
</div>
@endsection
