@php
use App\Models\Coupon;
use Illuminate\Support\Facades\Cache;

$enabled = site_setting('welcome_popup_enabled', '1') === '1';
$title = site_setting('welcome_popup_title', 'Welcome to UNI-LAB MARKET');
$message = site_setting('welcome_popup_message', 'Get an exclusive discount on your first order. Use the code below at checkout.');
$buttonText = site_setting('welcome_popup_button_text', 'Shop Now');
$image = site_setting_url('welcome_popup_image');

// Avoid hitting the DB at all when the popup is disabled.
$coupon = null;
if ($enabled) {
    // Cache the small list of active coupons for 5 minutes and randomise in PHP.
    // This eliminates a per-request `ORDER BY RAND()` against the coupons table.
    $activeCoupons = Cache::remember('active_coupons_list', 300, function () {
        return Coupon::active()
            ->select(['id', 'code', 'type', 'value'])
            ->get()
            ->all();
    });
    if (! empty($activeCoupons)) {
        $coupon = $activeCoupons[array_rand($activeCoupons)];
    }
}

$code = $coupon?->code ?? site_setting('welcome_popup_discount_code', '');
$percent = $coupon
    ? ($coupon->type === 'percent' ? (int) $coupon->value : 0)
    : (int) site_setting('welcome_popup_discount_percent', 10);
$fixedAmount = $coupon && $coupon->type === 'fixed' ? (float) $coupon->value : 0;
$discountLabel = $coupon
    ? ($coupon->type === 'percent'
        ? $percent . '% OFF your order'
        : money($fixedAmount) . ' OFF your order')
    : ($percent > 0 ? $percent . '% OFF your first order' : '');
@endphp



@if($enabled)
<div id="welcome-popup"
     class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300"
     aria-hidden="true">
    <div class="relative w-full max-w-2xl bg-white rounded-3xl shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300">
        <button type="button" id="welcome-popup-close" class="absolute top-4 right-4 z-10 h-9 w-9 flex items-center justify-center rounded-full bg-white/90 hover:bg-slate-100 text-slate-500 transition-colors">
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
                <div class="inline-flex items-center gap-2 bg-rose-50 text-rose-600 px-3 py-1 rounded-full text-xs font-bold mb-4">
                    <i class="fa-solid fa-gift"></i>
                    <span>Exclusive Offer</span>
                </div>

                <h3 class="text-2xl sm:text-3xl font-black text-slate-900 mb-3">{{ $title }}</h3>
                <p class="text-slate-500 mb-6 leading-relaxed">{{ $message }}</p>

                @if($code)
                <div class="mb-6">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Your discount code</p>
                    <div class="flex items-center justify-center md:justify-start gap-3">
                        <div class="relative">
                            <input type="text" id="welcome-popup-code" value="{{ $code }}" readonly
                                   class="h-12 pl-10 pr-4 bg-slate-50 border-2 border-dashed border-slate-300 rounded-xl text-slate-800 font-mono font-bold tracking-wider focus:outline-none">
                            <i class="fa-solid fa-tag absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        </div>
                        <button type="button" onclick="copyWelcomeCode()"
                                class="h-12 px-5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl transition-colors">
                            Copy
                        </button>
                    </div>
                    @if($discountLabel)
                    <p class="text-sm text-emerald-600 font-bold mt-2">{{ $discountLabel }} — انسخ الكود وادخله في صفحة الدفع لاستخدامه</p>
                    @endif
                </div>
                @endif

                <div class="flex flex-col sm:flex-row gap-3 justify-center md:justify-start">
                    <a href="{{ route('products.index') }}" class="h-12 px-8 bg-amber-400 hover:bg-amber-300 text-slate-900 font-bold rounded-xl transition-colors flex items-center justify-center gap-2">
                        {{ $buttonText }}
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                    <button type="button" id="welcome-popup-later" class="h-12 px-6 text-slate-500 hover:text-slate-800 font-bold rounded-xl transition-colors">
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
    const popup = document.getElementById('welcome-popup');
    const panel = popup.querySelector(':scope > div');
    const closeBtn = document.getElementById('welcome-popup-close');
    const laterBtn = document.getElementById('welcome-popup-later');
    const STORAGE_KEY = 'welcome_popup_dismissed_at';
    const COOLDOWN_MS = 24 * 60 * 60 * 1000; // 24 hours

    function shouldAutoShow() {
        try {
            const last = localStorage.getItem(STORAGE_KEY);
            if (!last) return true; // first-time visitor
            return (Date.now() - parseInt(last, 10)) >= COOLDOWN_MS;
        } catch (e) {
            return true;
        }
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

    // Header button (or any external trigger) — always show, ignore cooldown
    window.openWelcomePopup = function (code, percent) {
        if (code) {
            const input = document.getElementById('welcome-popup-code');
            if (input) input.value = code;
            const percentText = document.querySelector('#welcome-popup .text-emerald-600.font-bold');
            if (percentText) percentText.textContent = (percent || 0) + '% OFF your first order';
        }
        showPopup();
    };

    function closePopup() {
        popup.classList.add('opacity-0', 'pointer-events-none');
        popup.setAttribute('aria-hidden', 'true');
        panel.classList.remove('scale-100');
        panel.classList.add('scale-95');
        markDismissed();
    }

    if (closeBtn) closeBtn.addEventListener('click', closePopup);
    if (laterBtn) laterBtn.addEventListener('click', closePopup);
    popup.addEventListener('click', (e) => {
        if (e.target === popup) closePopup();
    });

    if (shouldAutoShow()) {
        setTimeout(showPopup, 1200);
    }
})();


function copyWelcomeCode() {
    const input = document.getElementById('welcome-popup-code');
    if (!input) return;
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(() => {
        showToast('Discount code copied: ' + input.value);
    }).catch(() => {
        showToast('Discount code: ' + input.value);
    });
}
</script>
@endpush
@endif
