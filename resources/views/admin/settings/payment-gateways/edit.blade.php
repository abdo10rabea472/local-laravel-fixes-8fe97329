@extends('admin.settings.layout')

@php
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

@section('settings-content')
<div class="space-y-6">

    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="h-10 w-10 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 shrink-0">
                    @if($gateway->logo)
                        <img src="{{ $gateway->logo }}" alt="" class="h-6 w-6 object-contain">
                    @else
                        <i class="fa-solid fa-wallet"></i>
                    @endif
                </div>
                <div class="min-w-0">
                    <h3 class="text-base font-bold text-slate-800 truncate">{{ $gateway->name }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5 font-mono">{{ $gateway->code }} · {{ $gateway->driver }}</p>
                </div>
            </div>
            <a href="{{ route('admin.settings.payment-gateways.index') }}" class="text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl transition-colors shrink-0">
                <i class="fa-solid fa-arrow-right ml-1"></i> القائمة
            </a>
        </div>

        @if($errors->any())
            <div class="mx-6 mt-4 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.payment-gateways.update', $gateway) }}" class="p-6 space-y-6">
            @csrf @method('PUT')

            <div>
                <h4 class="text-sm font-bold text-slate-800 mb-4">المعلومات الأساسية</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500">الاسم</label>
                        <input name="name" value="{{ old('name', $gateway->name) }}" required
                               class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:border-indigo-400 focus:bg-white focus:outline-none transition">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500">الوصف</label>
                        <input name="description" value="{{ old('description', $gateway->description) }}"
                               class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:border-indigo-400 focus:bg-white focus:outline-none transition">
                    </div>
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-xs font-bold text-slate-500">مسار الشعار (Logo URL)</label>
                        <input name="logo" value="{{ old('logo', $gateway->logo) }}" placeholder="/images/payments/{{ $gateway->code }}.svg"
                               class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-mono focus:border-indigo-400 focus:bg-white focus:outline-none transition">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500">رسوم إضافية (EGP)</label>
                        <input type="number" step="0.01" min="0" name="extra_fees" value="{{ old('extra_fees', $gateway->extra_fees) }}"
                               class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:border-indigo-400 focus:bg-white focus:outline-none transition">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500">الوضع</label>
                        <select name="sandbox" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:border-indigo-400 focus:bg-white focus:outline-none transition">
                            <option value="1" @selected(old('sandbox', $gateway->sandbox))>Sandbox (تجريبي)</option>
                            <option value="0" @selected(!old('sandbox', $gateway->sandbox))>Live (مباشر)</option>
                        </select>
                    </div>
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-xs font-bold text-slate-500">الدول المسموح بها</label>
                        <input name="allowed_countries" value="{{ old('allowed_countries', implode(',', (array) $gateway->allowed_countries)) }}" placeholder="EG,SA,AE"
                               class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-mono focus:border-indigo-400 focus:bg-white focus:outline-none transition">
                        <p class="text-[11px] text-slate-400">أكواد ISO مفصولة بفاصلة — اتركه فارغًا للسماح لجميع الدول.</p>
                    </div>
                </div>
            </div>

            @if(!empty($fields))
                <div class="border-t border-slate-100 pt-6">
                    <h4 class="text-sm font-bold text-slate-800 mb-1">إعدادات البوابة</h4>
                    <p class="text-xs text-slate-500 mb-4">تُحفظ هذه الإعدادات بشكل مستقل لكل بوابة ولا تشترك مع غيرها.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($fields as $field)
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-500 font-mono">{{ $field }}</label>
                                <input name="config[{{ $field }}]" value="{{ old('config.'.$field, $gateway->configValue($field)) }}" autocomplete="off"
                                       class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-mono focus:border-indigo-400 focus:bg-white focus:outline-none transition">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.settings.payment-gateways.index') }}" class="h-11 px-5 inline-flex items-center bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-sm transition-colors">
                    إلغاء
                </a>
                <button type="submit" class="h-11 px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm transition-colors shadow-md shadow-indigo-500/20">
                    <i class="fa-solid fa-floppy-disk ml-1"></i> حفظ الإعدادات
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
