@extends('layouts.front')

@section('title', 'Checkout')

@section('content')
<main class="bg-slate-50 min-h-screen py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('products.index') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white border border-slate-200 hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900">Checkout</h1>
        </div>

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

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500">First Name</label>
                            <input type="text" name="first_name" required class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-violet-300 focus:bg-white transition-colors">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500">Last Name</label>
                            <input type="text" name="last_name" required class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-violet-300 focus:bg-white transition-colors">
                        </div>
                        <div class="space-y-1.5 sm:col-span-2">
                            <label class="text-xs font-bold text-slate-500">Email</label>
                            <input type="email" name="email" required class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-violet-300 focus:bg-white transition-colors">
                        </div>
                        <div class="space-y-1.5 sm:col-span-2">
                            <label class="text-xs font-bold text-slate-500">Address</label>
                            <input type="text" name="address" required class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-violet-300 focus:bg-white transition-colors">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500">Phone</label>
                            <input type="tel" name="phone" required class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-violet-300 focus:bg-white transition-colors">
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

                    <div class="border-t border-slate-100 pt-6">
                        <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2 mb-4">
                            <i class="fa-solid fa-credit-card text-violet-600"></i> Payment Method
                        </h2>

                        <div class="space-y-3">
                            <label class="flex items-start gap-3 p-4 rounded-2xl border border-slate-200 hover:border-violet-300 hover:bg-violet-50/30 cursor-pointer transition-colors has-[:checked]:border-violet-600 has-[:checked]:bg-violet-50/40">
                                <input type="radio" name="payment_method" value="cod" checked class="mt-1 text-violet-600 focus:ring-violet-500">
                                <div>
                                    <p class="font-bold text-slate-900">Cash on Delivery</p>
                                    <p class="text-xs text-slate-500">Pay when your order arrives</p>
                                </div>
                            </label>
                            <label class="flex items-start gap-3 p-4 rounded-2xl border border-slate-200 hover:border-violet-300 hover:bg-violet-50/30 cursor-pointer transition-colors has-[:checked]:border-violet-600 has-[:checked]:bg-violet-50/40">
                                <input type="radio" name="payment_method" value="vodafone" class="mt-1 text-violet-600 focus:ring-violet-500">
                                <div>
                                    <p class="font-bold text-slate-900">Vodafone Cash</p>
                                    <p class="text-xs text-slate-500">Pay using your Vodafone Cash wallet</p>
                                </div>
                            </label>
                            <label class="flex items-start gap-3 p-4 rounded-2xl border border-slate-200 hover:border-violet-300 hover:bg-violet-50/30 cursor-pointer transition-colors has-[:checked]:border-violet-600 has-[:checked]:bg-violet-50/40">
                                <input type="radio" name="payment_method" value="card" class="mt-1 text-violet-600 focus:ring-violet-500">
                                <div>
                                    <p class="font-bold text-slate-900">Credit / Debit Card</p>
                                    <p class="text-xs text-slate-500">Visa, Mastercard, or Meeza</p>
                                </div>
                            </label>
                        </div>
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
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
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
        localStorage.setItem('cart', JSON.stringify(cart));
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


    itemsEl.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;
        const id = btn.getAttribute('data-id');
        const action = btn.getAttribute('data-action');
        const idx = cart.findIndex(i => String(i.id) === String(id));
        if (idx === -1) return;

        if (action === 'inc') {
            const current = cart[idx].quantity || 1;
            const max = Number(stockMap[cart[idx].id] ?? Infinity);
            if (Number.isFinite(max) && current >= max) {
                return; // silently cap at stock
            }
            cart[idx].quantity = current + 1;
        } else if (action === 'dec') {


            const newQty = (cart[idx].quantity || 1) - 1;
            if (newQty <= 0) {
                cart.splice(idx, 1);
            } else {
                cart[idx].quantity = newQty;
            }
        } else if (action === 'remove') {
            cart.splice(idx, 1);
        }

        saveCart();
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
            const res = await fetch(placeOrderUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({
                    code: appliedCouponCode,
                    cart: cart.map(i => ({ id: i.id, price: i.price, quantity: i.quantity || 1 })),
                    email: form.email.value,
                    phone: form.phone.value,
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
            window.UL?.toast('✅ تم تأكيد الطلب بنجاح', 'success');
            localStorage.removeItem('cart');
            window.location.href = json.redirect;
        } catch (e) {
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Confirm Order';
            (window.UL ? window.UL.toast('تعذّر إتمام الطلب. حاول لاحقاً.', 'error') : alert('تعذّر إتمام الطلب. حاول لاحقاً.'));
        }
    });
})();
</script>
@endpush
