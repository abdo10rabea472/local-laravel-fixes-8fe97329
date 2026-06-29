{{--
    Payment gateways selector for the checkout page.
    Inject in checkout.index where the user picks payment method.
    Requires $paymentGateways collection from CheckoutController@index.
--}}
<div class="payment-gateways">
    <h4 class="mb-3">اختر طريقة الدفع</h4>
    @if($paymentGateways->isEmpty())
        <div class="alert alert-warning">لا توجد بوابات دفع مفعّلة حاليًا. تواصل مع الإدارة.</div>
    @else
        <div class="row g-3">
            @foreach($paymentGateways as $g)
                <div class="col-md-6 col-lg-4">
                    <label class="pg-card d-flex align-items-center p-3 border rounded h-100" style="cursor:pointer">
                        <input type="radio" name="payment_gateway" value="{{ $g->code }}"
                               class="form-check-input me-3"
                               @checked($loop->first)
                               data-fees="{{ (float) $g->extra_fees }}">
                        @if($g->logo)
                            <img src="{{ $g->logo }}" alt="{{ $g->name }}" style="height:32px" class="me-3">
                        @endif
                        <div>
                            <div class="fw-bold">{{ $g->name }}</div>
                            @if($g->description)<small class="text-muted d-block">{{ $g->description }}</small>@endif
                            @if((float) $g->extra_fees > 0)
                                <small class="text-warning">+ رسوم {{ money((float)$g->extra_fees) }}</small>
                            @endif
                        </div>
                    </label>
                </div>
            @endforeach
        </div>
    @endif
</div>
<style>
.pg-card:has(input:checked){border-color:var(--bs-primary)!important;background:#f0f7ff}
.pg-card:hover{border-color:var(--bs-primary)!important}
</style>
