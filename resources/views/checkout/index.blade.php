@extends('layouts.front')

@section('title', 'Checkout')

@section('content')
<main class="bg-slate-50 min-h-screen">
    {{-- Page hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-violet-700 via-indigo-700 to-violet-800 text-white">
        <div class="absolute -top-24 -right-24 w-80 h-80 bg-amber-400/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-fuchsia-500/20 rounded-full blur-3xl"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-4 min-w-0">
                <a href="{{ route('products.index') }}" class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-white/15 backdrop-blur border border-white/20 hover:bg-white/25 transition">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div class="min-w-0">
                    <nav class="text-[11px] font-bold text-violet-100/80 mb-1 flex items-center gap-2">
                        <a href="{{ route('home') }}" class="hover:text-white">Home</a>
                        <i class="fa-solid fa-chevron-right text-[8px]"></i>
                        <span class="text-white">Checkout</span>
                    </nav>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight truncate">Secure checkout</h1>
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs font-bold">
                <span class="inline-flex items-center gap-1.5 bg-white/15 backdrop-blur border border-white/20 px-3 py-1.5 rounded-full"><i class="fa-solid fa-lock"></i> Encrypted</span>
                <span class="inline-flex items-center gap-1.5 bg-emerald-400/90 text-emerald-950 px-3 py-1.5 rounded-full"><i class="fa-solid fa-shield-halved"></i> SSL</span>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div id="checkout-empty" class="hidden text-center py-20 bg-white rounded-3xl border border-slate-200">
            <i class="fa-solid fa-cart-shopping text-5xl text-slate-300 mb-4"></i>
            <h2 class="text-xl font-bold text-slate-800">Your cart is empty</h2>
            <p class="text-slate-500 mt-2 mb-6">Add some products before checking out.</p>
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-violet-600 hover:bg-violet-700 text-white font-bold rounded-2xl transition-colors">
                Browse Products <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div id="checkout-content" class="grid gap-8 lg:grid-cols-5">
            {{-- Form --}}
            <div class="lg:col-span-3 space-y-6">
                <form id="checkout-form" class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-truck text-violet-600"></i> Shipping Information
                    </h2>

                    @php
                        $nameParts = preg_split('/\s+/', trim((string)($profile['customer_name'] ?? '')), 2);
                        $fn = $nameParts[0] ?? '';
                        $ln = $nameParts[1] ?? '';
                    @endphp
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500">First Name</label>
                            <input type="text" name="first_name" value="{{ $fn }}" required class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-violet-300 focus:bg-white transition-colors">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500">Last Name</label>
                            <input type="text" name="last_name" value="{{ $ln }}" required class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-violet-300 focus:bg-white transition-colors">
                        </div>
                        <div class="space-y-1.5 sm:col-span-2">
                            <label class="text-xs font-bold text-slate-500">Email</label>
                            <input type="email" name="email" value="{{ $profile['email'] ?? '' }}" required class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-violet-300 focus:bg-white transition-colors">
                        </div>
                        <div class="space-y-1.5 sm:col-span-2">
                            <label class="text-xs font-bold text-slate-500">Address</label>
                            <input type="text" name="address" value="{{ $profile['shipping_address'] ?? '' }}" required class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-violet-300 focus:bg-white transition-colors">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500">Phone</label>
                            <input type="tel" name="phone" value="{{ $profile['phone'] ?? '' }}" required class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-violet-300 focus:bg-white transition-colors">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500">Country</label>
                            <select id="shipping-country" name="country" required class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-violet-300 focus:bg-white transition-colors">
                                <option value="">Select country</option>
                            </select>
                        </div>
                        <div class="space-y-1.5 sm:col-span-2">
                            <label class="text-xs font-bold text-slate-500">State / City / Region</label>
                            <select id="shipping-region" name="region" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-violet-300 focus:bg-white transition-colors">
                                <option value="">Select region</option>
                            </select>
                            <p id="unsupported-country" class="hidden text-xs text-rose-600 font-bold mt-1">
                                <i class="fa-solid fa-circle-info ml-1"></i> We do not support your country yet. It will be available soon.
                            </p>
                        </div>
                    </div>

                    {{-- Shipping carrier --}}
                    @php $carriers = \App\Models\ShippingCarrier::active()->orderBy('sort_order')->get(['id','name','code','default_cost']); @endphp
                    @if($carriers->count())
                    <div class="border-t border-slate-100 pt-5">
                        <label class="block text-xs font-bold text-slate-600 mb-2">شركة الشحن</label>
                        <select id="shipping-carrier" name="shipping_carrier_id" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-violet-300 focus:bg-white transition-colors">
                            <option value="">— اختر شركة الشحن —</option>
                            @foreach($carriers as $c)
                                <option value="{{ $c->id }}" data-code="{{ $c->code }}" data-cost="{{ $c->default_cost }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <p id="carrier-rate-status" class="hidden mt-2 text-xs font-semibold"></p>
                    </div>
                    @endif



                    <div class="border-t border-slate-100 pt-6">
                        <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2 mb-4">
                            <i class="fa-solid fa-credit-card text-violet-600"></i> Payment Method
                        </h2>

                        @if($paymentGateways->isEmpty())
                            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-sm">
                                لا توجد بوابات دفع مفعّلة حاليًا. يرجى التواصل مع الإدارة.
                            </div>
                        @else
                        <div class="space-y-3">
                            @foreach($paymentGateways as $g)
                            <label class="flex items-start gap-3 p-4 rounded-2xl border border-slate-200 hover:border-violet-300 hover:bg-violet-50/30 cursor-pointer transition-colors has-[:checked]:border-violet-600 has-[:checked]:bg-violet-50/40">
                                <input type="radio" name="payment_gateway" value="{{ $g->code }}"
                                       data-fees="{{ (float) $g->extra_fees }}"
                                       @checked($loop->first)
                                       class="mt-1 text-violet-600 focus:ring-violet-500">
                                @if($g->logo)
                                    <img src="{{ $g->logo }}" alt="{{ $g->name }}" class="h-8 w-auto">
                                @endif
                                <div class="flex-1">
                                    <p class="font-bold text-slate-900">{{ $g->name }}</p>
                                    @if($g->description)<p class="text-xs text-slate-500">{{ $g->description }}</p>@endif
                                    @if((float)$g->extra_fees > 0)
                                        <p class="text-xs text-amber-600 font-semibold mt-1">+ رسوم {{ number_format((float)$g->extra_fees, 2) }} {{ config('app.currency','EGP') }}</p>
                                    @endif
                                </div>
                            </label>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Summary --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm lg:sticky lg:top-28">
                    <h2 class="text-lg font-bold text-slate-900 mb-5">Order Summary</h2>
                    <div id="checkout-items" class="space-y-4 mb-5 max-h-80 overflow-y-auto pr-1"></div>

                    <div class="border-t border-dashed border-slate-200 pt-4 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Subtotal</span>
                            <span id="subtotal-display" class="font-bold text-slate-900">0 EGP</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Shipping</span>
                            <span id="shipping-display" class="font-bold text-slate-900">0 EGP</span>
                        </div>
                        <div id="discount-row" class="flex justify-between hidden">
                            <span class="text-slate-500">Discount</span>
                            <span id="discount-amount" class="font-bold text-rose-600">-0 EGP</span>
                        </div>
                    </div>

                    <div class="mt-5 pt-5 border-t border-slate-200 flex justify-between items-center">
                        <span class="text-slate-500 font-bold">Total</span>
                        <span id="total-price-display" class="text-2xl font-black text-slate-900">0 EGP</span>
                    </div>

                    {{-- Coupon --}}
                    <div class="mt-5">
                        <label class="text-xs font-bold text-slate-500 mb-1.5 block">Promo code</label>
                        <div class="flex gap-2">
                            <input type="text" id="coupon-code" class="flex-1 h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl text-sm uppercase focus:border-violet-300 focus:bg-white outline-none transition-colors disabled:bg-slate-100 disabled:cursor-not-allowed" placeholder="Enter code">
                            <button type="button" id="apply-coupon-btn" class="h-11 px-5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl transition-colors text-sm disabled:bg-slate-300 disabled:cursor-not-allowed">Apply</button>
                        </div>
                        <p id="coupon-message" class="text-xs mt-2 hidden"></p>
                        <div id="coupon-discount-notice" class="hidden mt-2 flex items-start gap-2 p-3 bg-amber-50 border border-amber-200 rounded-xl">
                            <i class="fa-solid fa-circle-info text-amber-600 text-sm mt-0.5"></i>
                            <p class="text-xs text-amber-800 leading-relaxed">
                                لديك بالفعل منتجات عليها خصم في السلة، لذلك لا يمكن استخدام كود الخصم مع هذه الطلبية.
                            </p>
                        </div>
                    </div>

                    <button type="button" id="confirm-btn" class="w-full mt-6 h-12 bg-violet-600 hover:bg-violet-700 text-white font-bold rounded-xl transition-colors shadow-lg shadow-violet-500/20">
                        Confirm Order
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
(function () {
    // السلة تأتي من الخادم عبر main.js (window.cart). نحافظ على مرجع محلي للقراءة فقط.
    let cart = Array.isArray(window.cart) ? window.cart : [];

    const emptyEl = document.getElementById('checkout-empty');
    const contentEl = document.getElementById('checkout-content');
    const itemsEl = document.getElementById('checkout-items');
    const subtotalEl = document.getElementById('subtotal-display');
    const totalEl = document.getElementById('total-price-display');
    const discountRow = document.getElementById('discount-row');
    const discountAmountEl = document.getElementById('discount-amount');
    const couponInput = document.getElementById('coupon-code');
    const applyCouponBtn = document.getElementById('apply-coupon-btn');
    const couponMsg = document.getElementById('coupon-message');
    const confirmBtn = document.getElementById('confirm-btn');

    const welcomeCode = @json(site_setting('welcome_popup_discount_code', 'WELCOME10'));
    const welcomePercent = parseInt(@json(site_setting('welcome_popup_discount_percent', '10')));
    const freeShippingEnabled = @json(site_setting('free_shipping_enabled', '1')) === '1';
    const freeThreshold = parseFloat(@json(site_setting('free_shipping_threshold', '2000')));
    const shippingCountries = @json($shippingCountries);
    const applyCouponUrl = @json(route('checkout.apply-coupon'));
    const placeOrderUrl = @json(route('checkout.place-order'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const discountedProductIds = @json($discountedProductIds).map(String);
    const discountNoticeEl = document.getElementById('coupon-discount-notice');

    let discountAmount = 0;
    let appliedCouponCode = null;
    let shippingCost = 0;

    function cartHasDiscountedItem() {
        return cart.some(i => discountedProductIds.includes(String(i.id)));
    }

    function syncCouponAvailability() {
        const blocked = cartHasDiscountedItem();
        discountNoticeEl.classList.toggle('hidden', !blocked);
        applyCouponBtn.disabled = blocked;
        couponInput.disabled = blocked;
        if (blocked && appliedCouponCode) {
            // Auto-remove any previously applied coupon
            discountAmount = 0;
            appliedCouponCode = null;
            couponMsg.classList.add('hidden');
            updateTotals();
        }
    }

    function saveCart() {
        // الكتابة تتم على الخادم عبر دوال main.js (addToCart/changeQuantity/removeFromCart).
        // هنا نُذكِّر فقط بتحديث الواجهة.
        document.dispatchEvent(new CustomEvent('cart:updated'));
    }


    // Map of productId -> stock fetched from server
    const stockMap = {};
    let stockFetchKey = '';

    async function syncStocks() {
        const ids = cart.map(i => i.id).filter(Boolean);
        const key = ids.slice().sort((a, b) => a - b).join(',');
        if (!key || key === stockFetchKey) return;
        stockFetchKey = key;
        try {
            const res = await fetch(`{{ route('checkout.stocks') }}?ids=${encodeURIComponent(key)}`, {
                headers: { 'Accept': 'application/json' },
            });
            if (!res.ok) return;
            const data = await res.json();
            Object.assign(stockMap, data.stocks || {});
            const prices = data.prices || {};
            // Clamp over-stock quantities AND heal stale/zero prices from DB
            let changed = false;
            cart.forEach(item => {
                const max = Number(stockMap[item.id] ?? Infinity);
                if (Number.isFinite(max) && (item.quantity || 1) > max) {
                    item.quantity = Math.max(1, max);
                    changed = true;
                }
                const livePrice = Number(prices[item.id]);
                if (Number.isFinite(livePrice) && livePrice > 0 && (!item.price || item.price !== livePrice)) {
                    item.price = livePrice;
                    changed = true;
                }
            });
            if (changed) {
                saveCart();
                renderItems();
            }
        } catch (_) { /* ignore */ }
    }

    function refreshView() {
        if (cart.length === 0) {
            emptyEl.classList.remove('hidden');
            contentEl.classList.add('hidden');
        } else {
            emptyEl.classList.add('hidden');
            contentEl.classList.remove('hidden');
            renderItems();
            syncStocks();
        }
        syncCouponAvailability();
    }

    refreshView();


    function renderItems() {
        itemsEl.innerHTML = '';
        cart.forEach(item => {
            const qty = item.quantity || 1;
            const lineTotal = ((item.price || 0) * qty).toLocaleString();
            const max = Number(stockMap[item.id] ?? Infinity);
            const atMax = Number.isFinite(max) && qty >= max;
            const incClasses = atMax
                ? 'opacity-40 cursor-not-allowed'
                : 'text-slate-600 hover:bg-slate-100';
            const incTitle = atMax ? `الحد الأقصى المتاح: ${max}` : '';
            itemsEl.innerHTML += `
                <div class="flex gap-4" data-cart-id="${item.id}">
                    <img src="${item.image}" alt="${item.name}" class="w-16 h-16 object-contain bg-slate-50 rounded-xl border border-slate-100 p-1">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <h4 class="font-bold text-sm text-slate-900 truncate">${item.name}</h4>
                            <button type="button" data-action="remove" data-id="${item.id}" class="text-slate-400 hover:text-rose-600 transition-colors" title="Remove">
                                <i class="fa-solid fa-trash-can text-sm pointer-events-none"></i>
                            </button>
                        </div>
                        <div class="flex items-center justify-between gap-3 mt-2">
                            <div class="inline-flex items-center border border-slate-200 rounded-xl overflow-hidden">
                                <button type="button" data-action="dec" data-id="${item.id}" class="h-8 w-8 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition-colors">
                                    <i class="fa-solid fa-minus text-xs pointer-events-none"></i>
                                </button>
                                <span class="h-8 min-w-[2rem] px-2 flex items-center justify-center text-sm font-bold text-slate-800 border-x border-slate-200">${qty}</span>
                                <button type="button" data-action="inc" data-id="${item.id}" ${atMax ? 'disabled aria-disabled="true"' : ''} title="${incTitle}" class="h-8 w-8 flex items-center justify-center transition-colors ${incClasses}">
                                    <i class="fa-solid fa-plus text-xs pointer-events-none"></i>
                                </button>
                            </div>
                            <p class="text-sm font-bold text-violet-600">${lineTotal} EGP</p>
                        </div>
                    </div>
                </div>
            `;
        });
        updateTotals();
    }


    itemsEl.addEventListener('click', async (e) => {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;
        const id = btn.getAttribute('data-id');
        const action = btn.getAttribute('data-action');
        const idx = cart.findIndex(i => String(i.id) === String(id));
        if (idx === -1) return;

        btn.disabled = true;
        try {
            if (action === 'inc') {
                const current = cart[idx].quantity || 1;
                const max = Number(stockMap[cart[idx].id] ?? Infinity);
                if (Number.isFinite(max) && current >= max) return;
                await window.changeQuantity(id, +1);
            } else if (action === 'dec') {
                await window.changeQuantity(id, -1);
            } else if (action === 'remove') {
                await window.removeFromCart(id);
            }
        } finally {
            btn.disabled = false;
        }
        // window.cart مُحدّث الآن من السيرفر؛ زامن المرجع المحلي ثم أعد الرسم.
        cart = window.cart;
        refreshView();
    });

    // التزامن اللحظي: لو السلة الجانبية أو تبويب آخر عدّل السلة، أعد رسم Checkout مباشرة.
    document.addEventListener('cart:updated', () => {
        cart = window.cart || [];
        refreshView();
    });




    function subtotal() {
        return cart.reduce((sum, item) => sum + ((item.price || 0) * (item.quantity || 1)), 0);
    }

    function findCountry(id) {
        return shippingCountries.find(c => String(c.id) === String(id));
    }

    function findRegion(country, id) {
        if (!country || !id) return null;
        return (country.regions || []).find(r => String(r.id) === String(id));
    }

    function populateCountries() {
        const select = document.getElementById('shipping-country');
        if (!select) return;
        select.innerHTML = '<option value="">Select country</option>';
        shippingCountries.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.name;
            select.appendChild(opt);
        });
        const other = document.createElement('option');
        other.value = '__other__';
        other.textContent = "My country is not listed";
        select.appendChild(other);
    }

    function populateRegions(country) {
        const select = document.getElementById('shipping-region');
        if (!select) return;
        select.innerHTML = '<option value="">Select region</option>';
        if (!country || !country.regions) return;
        country.regions.forEach(r => {
            const opt = document.createElement('option');
            opt.value = r.id;
            const priceLabel = r.cost !== null && r.cost !== undefined ? ` (+${parseFloat(r.cost).toLocaleString()} EGP)` : '';
            opt.textContent = r.name + priceLabel;
            select.appendChild(opt);
        });
    }

    function calculateShipping() {
        const st = subtotal();

        if (freeShippingEnabled && st >= freeThreshold) {
            shippingCost = 0;
            return;
        }

        const countrySelect = document.getElementById('shipping-country');
        const regionSelect = document.getElementById('shipping-region');
        const countryId = countrySelect ? countrySelect.value : '';

        if (!countryId || countryId === '__other__') {
            shippingCost = 0;
            return;
        }

        const country = findCountry(countryId);
        if (!country) { shippingCost = 0; return; }

        let cost = country.cost !== null && country.cost !== undefined ? parseFloat(country.cost) : 0;
        const region = findRegion(country, regionSelect ? regionSelect.value : '');
        if (region && region.cost !== null && region.cost !== undefined) {
            cost += parseFloat(region.cost);
        }
        shippingCost = cost;
    }

    function updateTotals() {
        calculateShipping();
        const st = subtotal();
        // Re-cap discount in case cart shrank
        const discount = Math.min(discountAmount, st);
        const total = Math.max(0, st + shippingCost - discount);
        subtotalEl.textContent = st.toLocaleString() + ' EGP';
        totalEl.textContent = total.toLocaleString() + ' EGP';

        const shippingEl = document.getElementById('shipping-display');
        if (shippingCost === 0 && freeShippingEnabled && st >= freeThreshold) {
            shippingEl.textContent = 'Free';
            shippingEl.className = 'font-bold text-emerald-600';
        } else {
            shippingEl.textContent = shippingCost.toLocaleString() + ' EGP';
            shippingEl.className = 'font-bold text-slate-900';
        }

        if (discount > 0) {
            discountRow.classList.remove('hidden');
            discountAmountEl.textContent = '-' + discount.toLocaleString() + ' EGP';
        } else {
            discountRow.classList.add('hidden');
        }
    }

    async function applyCoupon() {
        const code = couponInput.value.trim().toUpperCase();
        if (!code) return;
        const emailInput = document.querySelector('input[name="email"]');
        const phoneInput = document.querySelector('input[name="phone"]');

        applyCouponBtn.disabled = true;
        applyCouponBtn.textContent = '...';
        try {
            const res = await fetch(applyCouponUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({
                    code,
                    cart: cart.map(i => ({ id: i.id, price: i.price, quantity: i.quantity || 1 })),
                    email: emailInput?.value || null,
                    phone: phoneInput?.value || null,
                }),
            });
            const json = await res.json();
            if (json.ok) {
                discountAmount = parseFloat(json.discount) || 0;
                appliedCouponCode = code;
                couponMsg.textContent = `تم تطبيق الكود ${code} — خصم ${discountAmount.toLocaleString()} EGP`;
                couponMsg.className = 'text-xs mt-2 text-emerald-600 font-bold';
                window.UL?.toast(`✅ تم تطبيق الكوبون`, 'success');
            } else {
                discountAmount = 0;
                appliedCouponCode = null;
                couponMsg.textContent = json.message || 'كود غير صالح.';
                couponMsg.className = 'text-xs mt-2 text-rose-600 font-bold';
                window.UL?.toast(json.message || 'كود غير صالح.', 'error');
            }
            couponMsg.classList.remove('hidden');
            updateTotals();
        } catch (e) {
            couponMsg.textContent = 'تعذّر التحقق من الكود. حاول مرة أخرى.';
            couponMsg.className = 'text-xs mt-2 text-rose-600 font-bold';
            couponMsg.classList.remove('hidden');
        } finally {
            applyCouponBtn.disabled = false;
            applyCouponBtn.textContent = 'Apply';
        }
    }

    applyCouponBtn.addEventListener('click', applyCoupon);
    couponInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); applyCoupon(); } });

    const countrySelect = document.getElementById('shipping-country');
    const regionSelect = document.getElementById('shipping-region');
    const unsupportedMsg = document.getElementById('unsupported-country');

    populateCountries();

    countrySelect?.addEventListener('change', () => {
        const value = countrySelect.value;
        if (value === '__other__' || !value) {
            regionSelect.innerHTML = '<option value="">Select region</option>';
            regionSelect.disabled = true;
            if (unsupportedMsg && value === '__other__') unsupportedMsg.classList.remove('hidden');
            else if (unsupportedMsg) unsupportedMsg.classList.add('hidden');
            updateTotals();
            return;
        }
        if (unsupportedMsg) unsupportedMsg.classList.add('hidden');
        regionSelect.disabled = false;
        populateRegions(findCountry(value));
        updateTotals();
    });

    regionSelect?.addEventListener('change', updateTotals);

    if (shippingCountries.length === 0) {
        regionSelect.disabled = true;
        if (unsupportedMsg) unsupportedMsg.classList.remove('hidden');
    }

    confirmBtn.addEventListener('click', async () => {
        const form = document.getElementById('checkout-form');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        confirmBtn.disabled = true;
        confirmBtn.textContent = '...';
        try {
            const carrierSel = document.getElementById('shipping-carrier');
            const countrySel = document.getElementById('shipping-country');
            const countryOpt = countrySel?.options[countrySel.selectedIndex];
            const regionSel  = document.getElementById('shipping-region');
            const regionOpt  = regionSel?.options[regionSel.selectedIndex];
            const cityEl     = document.querySelector('[name="city"], #shipping-city');
            const addrEl     = document.querySelector('[name="address"], #shipping-address');
            const zipEl      = document.querySelector('[name="postcode"], [name="postal_code"], #shipping-postcode');

            const res = await fetch(placeOrderUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({
                    code: appliedCouponCode,
                    cart: cart.map(i => ({ id: i.id, price: i.price, quantity: i.quantity || 1 })),
                    email: form.email.value,
                    phone: form.phone.value,
                    customer_name: form.full_name?.value || form.name?.value || null,
                    shipping_country: countryOpt?.textContent?.trim() || null,
                    shipping_region: regionOpt?.textContent?.trim() || null,
                    shipping_address: addrEl?.value || null,
                    shipping_city: cityEl?.value || null,
                    shipping_postcode: zipEl?.value || null,
                    shipping_cost: shippingCost,
                    shipping_carrier_id: carrierSel?.value ? parseInt(carrierSel.value, 10) : null,
                    payment_gateway: (form.querySelector('input[name="payment_gateway"]:checked')?.value) || null,
                }),
            });

            const json = await res.json();
            if (!json.ok) {
                couponMsg.textContent = json.message || 'تعذّر إتمام الطلب.';
                couponMsg.className = 'text-xs mt-2 text-rose-600 font-bold';
                couponMsg.classList.remove('hidden');
                window.UL?.toast(json.message || 'تعذّر إتمام الطلب.', 'error');
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Confirm Order';
                return;
            }
            window.UL?.toast('جاري تحويلك لبوابة الدفع...', 'success');
            if (typeof window.clearCart === 'function') { try { await window.clearCart(); } catch (_) {} }
            window.location.href = json.redirect;
        } catch (e) {
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Confirm Order';
            (window.UL ? window.UL.toast('تعذّر إتمام الطلب. حاول لاحقاً.', 'error') : alert('تعذّر إتمام الطلب. حاول لاحقاً.'));
        }
    });

    // ===== Auto live shipping rate (no button) =====
    const carrierSel = document.getElementById('shipping-carrier');
    const rateStatus = document.getElementById('carrier-rate-status');

    function setRateStatus(text, kind) {
        if (!rateStatus) return;
        if (!text) { rateStatus.classList.add('hidden'); return; }
        rateStatus.classList.remove('hidden');
        rateStatus.textContent = text;
        rateStatus.className = 'mt-2 text-xs font-semibold ' + ({
            loading: 'text-slate-500',
            ok: 'text-emerald-700 font-bold',
            error: 'text-rose-600',
        }[kind] || 'text-slate-500');
    }

    const codeMap = { 'Egypt':'EG','مصر':'EG','Saudi Arabia':'SA','UAE':'AE','United Arab Emirates':'AE','Kuwait':'KW','Qatar':'QA','Jordan':'JO','Bahrain':'BH','Oman':'OM' };
    let rateTimer = null;
    let lastSig = '';

    async function autoFetchRate() {
        if (!carrierSel) return;
        const opt = carrierSel.options[carrierSel.selectedIndex];
        const code = (opt?.dataset?.code || '').toLowerCase();
        const cid = carrierSel.value;
        if (!cid) { setRateStatus('', null); return; }

        // Address completeness
        const countryEl = document.getElementById('shipping-country');
        const countryOpt = countryEl?.options[countryEl.selectedIndex];
        const countryName = countryOpt?.textContent?.trim() || '';
        const city = (document.querySelector('[name="city"], #shipping-city')?.value || '').trim();
        const addr = (document.querySelector('[name="address"], #shipping-address')?.value || '').trim();
        const zip  = (document.querySelector('[name="postcode"], [name="postal_code"], #shipping-postcode')?.value || '').trim();

        if (!countryName || !city || !addr || !cart || cart.length === 0) {
            // Fallback to default carrier cost so total reflects the choice
            const fallback = parseFloat(opt?.dataset?.cost || '0') || 0;
            shippingCost = fallback;
            updateTotalsDisplay();
            setRateStatus('أكمل بيانات العنوان لحساب السعر الفعلي', null);
            return;
        }

        const sig = [cid, countryName, city, addr, zip, cart.length].join('|');
        if (sig === lastSig) return;
        lastSig = sig;

        // For non-aramex carriers, just use the default cost.
        if (code !== 'aramex') {
            shippingCost = parseFloat(opt?.dataset?.cost || '0') || 0;
            updateTotalsDisplay();
            setRateStatus('✓ تم تطبيق سعر الشحن الافتراضي', 'ok');
            return;
        }

        const cc = codeMap[countryName] || 'EG';
        setRateStatus('جاري حساب سعر الشحن…', 'loading');
        try {
            const res = await fetch('{{ route('checkout.aramex-rate') }}', {
                method: 'POST',
                headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({
                    country_code: cc, city, line1: addr, postal_code: zip,
                    cart: cart.map(i => ({ id: i.id, quantity: i.quantity || 1 })),
                }),
            });
            const json = await res.json();
            if (json.ok) {
                shippingCost = parseFloat(json.data?.amount || 0);
                updateTotalsDisplay();
                setRateStatus(`✓ سعر الشحن: ${shippingCost.toFixed(2)} ${json.data?.currency || 'EGP'}`, 'ok');
            } else {
                setRateStatus(json.message || 'تعذّر حساب سعر الشحن.', 'error');
            }
        } catch (e) {
            setRateStatus('تعذّر الاتصال بالخادم.', 'error');
        }
    }

    function updateTotalsDisplay() {
        const st = subtotal();
        const discount = Math.min(discountAmount, st);
        const total = Math.max(0, st + shippingCost - discount);
        const shippingEl = document.getElementById('shipping-display');
        if (shippingEl) {
            shippingEl.textContent = shippingCost.toLocaleString() + ' EGP';
            shippingEl.className = 'font-bold text-slate-900';
        }
        if (subtotalEl) subtotalEl.textContent = st.toLocaleString() + ' EGP';
        if (totalEl)    totalEl.textContent    = total.toLocaleString() + ' EGP';
    }

    function debouncedRate() {
        clearTimeout(rateTimer);
        rateTimer = setTimeout(autoFetchRate, 400);
    }

    // Bind change listeners
    carrierSel?.addEventListener('change', () => { lastSig = ''; debouncedRate(); });
    ['shipping-country','shipping-region','shipping-city','shipping-address','shipping-postcode']
        .forEach(id => {
            const el = document.getElementById(id);
            el?.addEventListener('change', debouncedRate);
            el?.addEventListener('input', debouncedRate);
        });
    document.querySelectorAll('[name="city"],[name="address"],[name="postcode"],[name="postal_code"]')
        .forEach(el => { el.addEventListener('input', debouncedRate); });
})();

</script>
@endpush
