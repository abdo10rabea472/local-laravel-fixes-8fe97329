@extends('layouts.app')

@section('content')
<div class="container py-5 text-center">
    <h1 class="text-success">✓ تم إنشاء طلبك بنجاح</h1>
    <p class="lead">رقم الطلب: <strong>{{ $order->order_number }}</strong></p>
    @if($order->payment_gateway === 'cod')
        <p>سيتم تحصيل المبلغ <strong>{{ number_format((float)$order->total, 2) }} {{ $order->currency }}</strong> عند الاستلام.</p>
    @else
        <p>حالة الدفع: <strong>{{ $order->payment_status }}</strong></p>
    @endif
    <a href="{{ route('home') }}" class="btn btn-primary mt-3">متابعة التسوق</a>
</div>
@endsection
