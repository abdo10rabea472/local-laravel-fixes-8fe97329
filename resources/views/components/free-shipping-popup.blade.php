@php
$shippingEnabled = site_setting('free_shipping_enabled', '1') === '1';
$popupEnabled = site_setting('free_shipping_popup_enabled', '0') === '1';
$threshold = (float) site_setting('free_shipping_threshold', 2000);
$title = site_setting('free_shipping_popup_title', 'Free Shipping Available!');
$message = site_setting('free_shipping_popup_message', 'Enjoy free shipping on all orders above our minimum threshold.');
$image = site_setting_url('free_shipping_popup_image');
@endphp

@if($shippingEnabled && $popupEnabled)
<div id="free-shipping-popup"
     class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300"
     aria-hidden="true">
    <div class="relative w-full max-w-2xl bg-white rounded-3xl shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300">
        <button type="button" id="free-shipping-popup-close" class="absolute top-4 right-4 z-10 h-9 w-9 flex items-center justify-center rounded-full bg-white/90 hover:bg-slate-100 text-slate-500 transition-colors">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="grid md:grid-cols-5">
            @if($image)
            <div class="md:col-span-2 hidden md:block relative min-h-[220px]">
                <img src="{{ $image }}" alt="" class="absolute inset-0 w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/40 to-transparent"></div>
            </div>
            @endif

            <div class="p-8 md:col-span-{{ $image ? '3' : '5' }} text-center md:text-left">
                <div class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full text-xs font-bold mb-4">
                    <i class="fa-solid fa-truck-fast"></i>
                    <span>Free Shipping</span>
                </div>

                <h3 class="text-2xl sm:text-3xl font-black text-slate-900 mb-3">{{ $title }}</h3>
                <p class="text-slate-500 mb-6 leading-relaxed">{{ $message }}</p>

                <div class="mb-6">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Minimum order value</p>
                    <div class="inline-flex items-center gap-2 h-12 px-5 bg-slate-50 border-2 border-dashed border-slate-300 rounded-xl">
                        <i class="fa-solid fa-tag text-slate-400"></i>
                        <span class="text-slate-800 font-mono font-bold tracking-wider">{{ number_format($threshold, 0) }} EGP</span>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 justify-center md:justify-start">
                    <a href="{{ route('products.index') }}" class="h-12 px-8 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl transition-colors flex items-center justify-center gap-2">
                        Shop Now
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                    <button type="button" id="free-shipping-popup-later" class="h-12 px-6 text-slate-500 hover:text-slate-800 font-bold rounded-xl transition-colors">
                        Maybe later
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const popup = document.getElementById('free-shipping-popup');
    if (!popup) return;
    const panel = popup.querySelector(':scope > div');
    const closeBtn = document.getElementById('free-shipping-popup-close');
    const laterBtn = document.getElementById('free-shipping-popup-later');
    const STORAGE_KEY = 'free_shipping_popup_dismissed_at';
    const COOLDOWN_MS = 24 * 60 * 60 * 1000;

    function shouldAutoShow() {
        try {
            const last = localStorage.getItem(STORAGE_KEY);
            if (!last) return true;
            return (Date.now() - parseInt(last, 10)) >= COOLDOWN_MS;
        } catch (e) { return true; }
    }

    function markDismissed() {
        try { localStorage.setItem(STORAGE_KEY, Date.now().toString()); } catch (e) {}
    }

    function showPopup() {
        popup.classList.remove('opacity-0', 'pointer-events-none');
        popup.setAttribute('aria-hidden', 'false');
        panel.classList.remove('scale-95');
        panel.classList.add('scale-100');
    }

    function closePopup() {
        popup.classList.add('opacity-0', 'pointer-events-none');
        popup.setAttribute('aria-hidden', 'true');
        panel.classList.remove('scale-100');
        panel.classList.add('scale-95');
        markDismissed();
    }

    window.openFreeShippingPopup = showPopup;

    if (closeBtn) closeBtn.addEventListener('click', closePopup);
    if (laterBtn) laterBtn.addEventListener('click', closePopup);
    popup.addEventListener('click', (e) => { if (e.target === popup) closePopup(); });

    if (shouldAutoShow()) {
        setTimeout(showPopup, 1500);
    }
})();
</script>
@endpush
@endif
