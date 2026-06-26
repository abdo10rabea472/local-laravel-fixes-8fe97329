@extends('layouts.front')

@section('title', 'Payment Verification')

@section('content')
<div class="min-h-[70vh] bg-slate-50 px-4 py-12 text-center">
<div class="mx-auto max-w-2xl rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
    @if(!empty($result['success']))
        <div class="mb-3 text-5xl text-emerald-600"><i class="fa-solid fa-circle-check"></i></div>
        <h1 class="text-2xl font-black text-emerald-700">تم تأكيد الدفع بنجاح</h1>
        <p class="mt-2 text-slate-500">رقم العملية: <code>{{ $result['payment_id'] ?? '—' }}</code></p>
    @else
        <div class="mb-3 text-5xl text-rose-600"><i class="fa-solid fa-circle-xmark"></i></div>
        <h1 class="text-2xl font-black text-rose-700">فشل التحقق من الدفع</h1>
        <p class="mt-2 text-slate-500">{{ $result['message'] ?? 'يرجى المحاولة مجددًا.' }}</p>
    @endif
    <a href="{{ route('home') }}" class="mt-6 inline-flex h-11 items-center rounded-xl bg-violet-600 px-5 text-sm font-bold text-white transition-colors hover:bg-violet-700">العودة للرئيسية</a>
</div>
</div>
@endsection
