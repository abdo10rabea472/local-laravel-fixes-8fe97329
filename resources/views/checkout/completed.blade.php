@extends('layouts.front')

@section('title', 'Payment Status')

@section('content')
@php
    $isPaid     = in_array($order->payment_status, ['paid'], true);
    $isCod      = $order->payment_gateway === 'cod';
    $isPending  = in_array($order->payment_status, ['pending', 'unpaid'], true);
    $isFailed   = in_array($order->payment_status, ['failed'], true);
    $payError   = session('error');
@endphp

<div class="min-h-[70vh] bg-slate-50 py-10 px-4">
<div class="mx-auto w-full max-w-2xl">

    @if($payError)
        <div class="mb-4 flex items-start gap-2 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-rose-700">
            <i class="fa-solid fa-circle-exclamation mt-1"></i>
            <div>
                <strong class="mb-1 block">فشل بدء عملية الدفع</strong>
                <small class="leading-relaxed">{{ $payError }}</small>
            </div>
        </div>
    @endif

    <div class="rounded-3xl border border-slate-200 bg-white p-6 text-center shadow-sm md:p-10">
        @if($isPaid)
            <div class="mb-3 text-5xl text-emerald-600"><i class="fa-solid fa-circle-check"></i></div>
            <h1 class="text-2xl font-black text-emerald-700">تم الدفع بنجاح</h1>
            <p class="mt-2 text-slate-500">شكرًا لطلبك! تم استلام دفعتك بنجاح.</p>
        @elseif($isCod)
            <div class="mb-3 text-5xl text-emerald-600"><i class="fa-solid fa-circle-check"></i></div>
            <h1 class="text-2xl font-black text-emerald-700">تم إنشاء طلبك بنجاح</h1>
            <p class="mt-2 text-slate-500">
                سيتم تحصيل المبلغ
                <strong>{{ number_format((float)$order->total, 2) }} {{ $order->currency }}</strong>
                عند الاستلام.
            </p>
        @elseif($isFailed || $payError)
            <div class="mb-3 text-5xl text-rose-600"><i class="fa-solid fa-circle-xmark"></i></div>
            <h1 class="text-2xl font-black text-rose-700">لم يتم الدفع</h1>
            <p class="mt-2 text-slate-500">
                طلبك محفوظ بحالة "بانتظار الدفع" ويمكنك المحاولة مرة أخرى.
            </p>
        @elseif($isPending)
            <div class="mb-3 text-5xl text-amber-500"><i class="fa-solid fa-clock"></i></div>
            <h1 class="text-2xl font-black text-amber-600">بانتظار إتمام الدفع</h1>
            <p class="mt-2 text-slate-500">لم نستلم تأكيد الدفع من بوابة الدفع بعد.</p>
        @else
            <div class="mb-3 text-5xl text-violet-600"><i class="fa-solid fa-receipt"></i></div>
            <h1 class="text-2xl font-black text-slate-900">تفاصيل الطلب</h1>
        @endif

        <hr class="my-6 border-slate-100">

        <dl class="space-y-3 text-start text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">رقم الطلب</dt><dd class="font-bold text-slate-900">#{{ $order->order_number }}</dd></div>

            <div class="flex justify-between gap-4"><dt class="text-slate-500">طريقة الدفع</dt><dd class="font-bold text-slate-900">{{ $order->payment_gateway ?? '—' }}</dd></div>

            <div class="flex justify-between gap-4"><dt class="text-slate-500">حالة الدفع</dt><dd class="font-bold text-slate-900">{{ $order->payment_status ?? '—' }}</dd></div>

            <div class="flex justify-between gap-4"><dt class="text-slate-500">الإجمالي</dt><dd class="font-bold text-slate-900">{{ number_format((float)$order->total, 2) }} {{ $order->currency }}</dd></div>
        </dl>

        <div class="mt-6 flex flex-wrap justify-center gap-2">
            @if(! $isPaid && ! $isCod && $order->payment_gateway)
                <a href="{{ route('checkout.pay', $order) }}?gateway={{ urlencode($order->payment_gateway) }}"
                   class="inline-flex h-11 items-center rounded-xl bg-violet-600 px-5 text-sm font-bold text-white transition-colors hover:bg-violet-700">
                    <i class="fa-solid fa-rotate-right ms-1"></i> إعادة محاولة الدفع
                </a>
            @endif
            <a href="{{ route('home') }}" class="inline-flex h-11 items-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-bold text-slate-700 transition-colors hover:bg-slate-50">متابعة التسوق</a>
        </div>
    </div>
</div>
</div>
@endsection
