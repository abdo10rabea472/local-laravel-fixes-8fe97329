@extends('layouts.app')

@section('content')
<div class="container py-5 text-center">
    @if(!empty($result['success']))
        <h1 class="text-success">✓ تم تأكيد الدفع بنجاح</h1>
        <p class="lead">رقم العملية: <code>{{ $result['payment_id'] ?? '—' }}</code></p>
    @else
        <h1 class="text-danger">✗ فشل التحقق من الدفع</h1>
        <p class="lead">{{ $result['message'] ?? 'يرجى المحاولة مجددًا.' }}</p>
    @endif
    <a href="{{ route('home') }}" class="btn btn-primary mt-3">العودة للرئيسية</a>
</div>
@endsection
