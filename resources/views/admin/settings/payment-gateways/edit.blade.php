@extends('admin.layouts.app')

@section('title', 'تعديل بوابة دفع')

@php
    // Suggested config keys per driver (so the admin sees the right fields).
    $fieldsByDriver = [
        'cod'          => [],
        'Paymob'       => ['PAYMOB_API_KEY','PAYMOB_INTEGRATION_ID','PAYMOB_IFRAME_ID','PAYMOB_HMAC','PAYMOB_CURRENCY'],
        'PaymobWallet' => ['PAYMOB_API_KEY','PAYMOB_WALLET_INTEGRATION_ID','PAYMOB_HMAC','PAYMOB_CURRENCY'],
        'Fawry'        => ['FAWRY_URL','FAWRY_SECRET','FAWRY_MERCHANT'],
        'Kashier'      => ['KASHIER_ACCOUNT_KEY','KASHIER_IFRAME_KEY','KASHIER_TOKEN','KASHIER_URL','KASHIER_MODE','KASHIER_CURRENCY','KASHIER_WEBHOOK_URL'],
        'HyperPay'     => ['HYPERPAY_BASE_URL','HYPERPAY_TOKEN','HYPERPAY_CREDIT_ID','HYPERPAY_MADA_ID','HYPERPAY_APPLE_ID','HYPERPAY_CURRENCY'],
        'PayPal'       => ['PAYPAL_CLIENT_ID','PAYPAL_SECRET','PAYPAL_CURRENCY','PAYPAL_MODE'],
        'Stripe'       => ['STRIPE_SECRET_KEY','STRIPE_PUBLIC_KEY','STRIPE_CURRENCY'],
        'Tap'          => ['TAP_SECRET_KEY','TAP_PUBLIC_KEY','TAP_CURRENCY','TAP_LANG_KEY'],
        'Opay'         => ['OPAY_SECRET_KEY','OPAY_PUBLIC_KEY','OPAY_MERCHANT_ID','OPAY_COUNTRY_CODE','OPAY_BASE_URL','OPAY_CURRENCY'],
        'PayTabs'      => ['PAYTABS_PROFILE_ID','PAYTABS_SERVER_KEY','PAYTABS_BASE_URL','PAYTABS_CHECKOUT_LANG','PAYTABS_CURRENCY'],
        'Thawani'      => ['THAWANI_API_KEY','THAWANI_URL','THAWANI_PUBLISHABLE_KEY'],
        'Telr'         => ['TELR_MERCHANT_ID','TELR_API_KEY','TELR_MODE'],
        'ClickPay'     => ['CLICKPAY_SERVER_KEY','CLICKPAY_PROFILE_ID'],
        'Binance'      => ['BINANCE_API','BINANCE_SECRET'],
        'NowPayments'  => ['NOWPAYMENTS_API_KEY'],
        'Payeer'       => ['PAYEER_MERCHANT_ID','PAYEER_API_KEY','PAYEER_ADDITIONAL_API_KEY'],
        'PerfectMoney' => ['PERFECT_MONEY_ID','PERFECT_MONEY_PASSPHRASE'],
    ];
    $fields = $fieldsByDriver[$gateway->driver] ?? [];
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">{{ $gateway->name }} <small class="text-muted">({{ $gateway->code }})</small></h1>
    <a href="{{ route('admin.settings.payment-gateways.index') }}" class="btn btn-outline-secondary btn-sm">→ القائمة</a>
</div>

<form method="POST" action="{{ route('admin.settings.payment-gateways.update', $gateway) }}" class="card p-4 shadow-sm">
    @csrf @method('PUT')

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">الاسم</label>
            <input name="name" class="form-control" value="{{ old('name', $gateway->name) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">الوصف</label>
            <input name="description" class="form-control" value="{{ old('description', $gateway->description) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">مسار الشعار (logo)</label>
            <input name="logo" class="form-control" value="{{ old('logo', $gateway->logo) }}" placeholder="/images/payments/{{ $gateway->code }}.svg">
        </div>
        <div class="col-md-3">
            <label class="form-label">رسوم إضافية</label>
            <input name="extra_fees" type="number" step="0.01" min="0" class="form-control" value="{{ old('extra_fees', $gateway->extra_fees) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">الوضع</label>
            <select name="sandbox" class="form-select">
                <option value="1" @selected($gateway->sandbox)>Sandbox</option>
                <option value="0" @selected(!$gateway->sandbox)>Live</option>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">الدول المسموح بها (أكواد ISO مفصولة بفاصلة — اتركه فارغًا للكل)</label>
            <input name="allowed_countries" class="form-control" value="{{ old('allowed_countries', implode(',', (array) $gateway->allowed_countries)) }}" placeholder="EG,SA,AE">
        </div>
    </div>

    @if(!empty($fields))
    <hr class="my-4">
    <h5>إعدادات البوابة</h5>
    <p class="text-muted small">يتم حفظ هذه الإعدادات بشكل مستقل لكل بوابة ولا تشترك مع غيرها.</p>
    <div class="row g-3">
        @foreach($fields as $field)
        <div class="col-md-6">
            <label class="form-label"><code>{{ $field }}</code></label>
            <input name="config[{{ $field }}]" class="form-control" value="{{ old('config.'.$field, $gateway->configValue($field)) }}" autocomplete="off">
        </div>
        @endforeach
    </div>
    @endif

    <div class="mt-4 d-flex justify-content-end gap-2">
        <a href="{{ route('admin.settings.payment-gateways.index') }}" class="btn btn-outline-secondary">إلغاء</a>
        <button class="btn btn-primary">حفظ الإعدادات</button>
    </div>
</form>
@endsection
