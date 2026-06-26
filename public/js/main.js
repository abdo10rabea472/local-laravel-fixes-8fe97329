// Cart state is initialized via server fetch in the cart system below.
window.cart = window.cart || [];


// Mobile menu controls
const mobileMenu = document.getElementById("mobile-menu");
const menuBtn = document.getElementById("mobile-menu-btn");

if (menuBtn && mobileMenu) {
    menuBtn.addEventListener("click", () => {
        mobileMenu.classList.toggle("hidden");
    });
}

// Shop by College dropdown (desktop)
const collegesDropdown = document.getElementById("colleges-dropdown");
const collegesDropdownBtn = document.getElementById("colleges-dropdown-btn");
const collegesDropdownPanel = document.getElementById("colleges-dropdown-panel");
const collegesDropdownChevron = document.getElementById("colleges-dropdown-chevron");

function setCollegesDropdownOpen(open) {
    if (!collegesDropdownPanel || !collegesDropdownBtn) return;

    collegesDropdownBtn.setAttribute("aria-expanded", open ? "true" : "false");

    if (open) {
        collegesDropdownPanel.classList.remove("hidden", "opacity-0", "translate-y-1", "pointer-events-none");
        collegesDropdownPanel.classList.add("opacity-100", "translate-y-0", "pointer-events-auto");
        if (collegesDropdownChevron) collegesDropdownChevron.classList.add("rotate-180");
    } else {
        collegesDropdownPanel.classList.add("opacity-0", "translate-y-1", "pointer-events-none");
        collegesDropdownPanel.classList.remove("opacity-100", "translate-y-0", "pointer-events-auto");
        if (collegesDropdownChevron) collegesDropdownChevron.classList.remove("rotate-180");
        setTimeout(() => {
            if (collegesDropdownBtn.getAttribute("aria-expanded") === "false") {
                collegesDropdownPanel.classList.add("hidden");
            }
        }, 200);
    }
}

if (collegesDropdownBtn && collegesDropdownPanel) {
    collegesDropdownBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        const isOpen = collegesDropdownBtn.getAttribute("aria-expanded") === "true";
        setCollegesDropdownOpen(!isOpen);
    });

    document.addEventListener("click", (e) => {
        if (collegesDropdown && !collegesDropdown.contains(e.target)) {
            setCollegesDropdownOpen(false);
        }
    });

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") setCollegesDropdownOpen(false);
    });

    // Hover support on large screens
    if (collegesDropdown && window.matchMedia("(min-width: 1024px)").matches) {
        let hoverTimer;
        collegesDropdown.addEventListener("mouseenter", () => {
            clearTimeout(hoverTimer);
            setCollegesDropdownOpen(true);
        });
        collegesDropdown.addEventListener("mouseleave", () => {
            hoverTimer = setTimeout(() => setCollegesDropdownOpen(false), 120);
        });
    }
}

// ====================== CART SYSTEM (Server-backed, realtime) ======================
// السلة مخزّنة في قاعدة البيانات (جدول cart_items) عبر AJAX.
// تزامن لحظي بين السلة الجانبية وصفحة Checkout عبر CustomEvent + BroadcastChannel.

window.cart = window.cart || [];

const CART_URLS = {
    index: '/cart',
    add: '/cart/add',
    update: '/cart/update',
    remove: '/cart/remove',
    clear: '/cart/clear',
    merge: '/cart/merge',
};

const cartChannel = ('BroadcastChannel' in window) ? new BroadcastChannel('ul-cart') : null;

function csrfHeader() {
    const t = document.querySelector('meta[name="csrf-token"]')?.content || '';
    return { 'X-CSRF-TOKEN': t, 'Accept': 'application/json' };
}

async function cartFetch(url, options = {}) {
    const res = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            ...csrfHeader(),
            ...(options.headers || {}),
        },
        ...options,
    });
    if (!res.ok) throw new Error('Cart request failed: ' + res.status);
    return res.json();
}

function setCartState(data, { broadcast = true } = {}) {
    window.cart = (data.items || []).map(i => ({
        id: String(i.id),
        name: i.name,
        price: Number(i.price) || 0,
        quantity: Number(i.quantity) || 1,
        image: i.image || '',
        stock: Number(i.stock) || 0,
    }));
    updateCartCount();
    // أخبر السلة الجانبية وصفحة Checkout بأن السلة تحدّثت
    document.dispatchEvent(new CustomEvent('cart:updated', { detail: { items: window.cart } }));
    // التزامن بين التبويبات/الصفحات المفتوحة
    if (broadcast && cartChannel) {
        try { cartChannel.postMessage({ type: 'sync', items: window.cart }); } catch (_) {}
    }
    // أعد رسم السلة الجانبية لو كانت مفتوحة
    const cartEl = document.querySelector('.cart');
    if (cartEl && cartEl.classList.contains('active')) renderCart();
}

function updateCartCount() {
    const totalItems = window.cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
    document.querySelectorAll('#cart-count, .cart-count').forEach(el => {
        el.textContent = el.classList?.contains('cart-count') ? `(${totalItems})` : totalItems;
    });
}

// ====================== API السلة ======================
async function loadCart() {
    try {
        const data = await cartFetch(CART_URLS.index, { method: 'GET' });
        setCartState(data, { broadcast: false });
    } catch (e) { console.warn('loadCart failed', e); }
}

async function addToCart(element) {
    const productCard = element.closest('[data-id]');
    if (!productCard) { console.error('Product card not found'); return; }
    const productId = parseInt(productCard.dataset.id);
    if (!productId) return;
    const nameEl = productCard.querySelector('h2, h3');
    const name = nameEl ? nameEl.textContent.trim() : 'Product';

    try {
        const data = await cartFetch(CART_URLS.add, {
            method: 'POST',
            body: JSON.stringify({ product_id: productId, quantity: 1 }),
        });
        setCartState(data);
        showToast(`✅ ${name} تم إضافته بنجاح`);
    } catch (e) {
        showToast('تعذّر إضافة المنتج');
    }
}

async function removeFromCart(productId) {
    try {
        const data = await cartFetch(CART_URLS.remove, {
            method: 'DELETE',
            body: JSON.stringify({ product_id: parseInt(productId) }),
        });
        setCartState(data);
    } catch (e) { showToast('تعذّر حذف المنتج'); }
}

async function changeQuantity(productId, change) {
    const item = window.cart.find(i => String(i.id) === String(productId));
    if (!item) return;
    const newQty = Math.max(0, (item.quantity || 1) + change);
    try {
        const data = await cartFetch(CART_URLS.update, {
            method: 'PATCH',
            body: JSON.stringify({ product_id: parseInt(productId), quantity: newQty }),
        });
        setCartState(data);
    } catch (e) { showToast('تعذّر تحديث الكمية'); }
}

async function clearCart() {
    try {
        const data = await cartFetch(CART_URLS.clear, { method: 'DELETE' });
        setCartState(data);
    } catch (e) {}
}

// ====================== عرض السلة الجانبية ======================
function renderCart() {
    const container = document.querySelector('.items_in_cart');
    if (!container) return;

    if (window.cart.length === 0) {
        container.innerHTML = `
            <div class="text-center py-20 text-gray-400">
                <i class="fa-solid fa-shopping-bag text-6xl mb-4 opacity-50"></i>
                <p class="text-xl">The basket is empty</p>
                <p class="text-sm mt-2">Start shopping now</p>
            </div>`;
        updateTotal();
        return;
    }

    let html = '';
    window.cart.forEach(item => {
        html += `
            <div class="cart-item flex gap-4 py-5 border-2 border-solid border-indigo-500 rounded-md px-4 mb-4 gap-12" data-cart-id="${item.id}">
                <img src="${item.image}" alt="${item.name}" class="w-24 h-20 object-contain bg-slate-50 rounded-lg border">
                <div class="flex-1">
                    <h4 class="font-semibold text-sm leading-tight mb-1">${item.name}</h4>
                    <p class="text-emerald-600 font-bold">${item.price.toLocaleString()} EGP</p>
                    <div class="flex items-center justify-between mt-4">
                        <div class="flex items-center border border-gray-300 rounded-xl overflow-hidden">
                            <button onclick="changeQuantity('${item.id}', -1)" class="px-3 py-1 text-lg hover:bg-amber-400">-</button>
                            <span class="px-5 font-semibold">${item.quantity}</span>
                            <button onclick="changeQuantity('${item.id}', 1)" class="px-3 py-1 text-lg hover:bg-amber-400">+</button>
                        </div>
                        <button onclick="removeFromCart('${item.id}')" class="text-red-500 hover:text-red-700 p-2 w-10 h-10 flex items-center justify-center rounded-full transition-colors duration-300 hover:bg-red-100">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>`;
    });
    container.innerHTML = html;
    updateTotal();
}

function updateTotal() {
    const totalEl = document.querySelector('.price_cart_toral');
    if (!totalEl) return;
    const total = window.cart.reduce((sum, item) => sum + (item.price * (item.quantity || 1)), 0);
    totalEl.textContent = `${total.toLocaleString()} EGP`;
}

function showToast(msg) {
    if (window.UL && typeof window.UL.toast === 'function') {
        window.UL.toast(msg, msg.startsWith('✅') ? 'success' : 'error');
        return;
    }
    let toast = document.getElementById('toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toast';
        document.body.appendChild(toast);
    }
    toast.style.cssText = `position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:#1f2937;color:white;padding:14px 24px;border-radius:9999px;z-index:99999;box-shadow:0 10px 15px -3px rgb(0 0 0 / 0.3);`;
    toast.textContent = msg;
    toast.style.opacity = 1;
    setTimeout(() => { toast.style.opacity = 0; }, 2800);
}

function open_close_cart() {
    const cartEl = document.querySelector('.cart');
    if (!cartEl) return;
    const isOpening = !cartEl.classList.contains('active');
    cartEl.classList.toggle('active');
    if (isOpening) setTimeout(renderCart, 10);
}

// ====================== تزامن بين التبويبات/الصفحات ======================
if (cartChannel) {
    cartChannel.addEventListener('message', (e) => {
        if (e.data?.type === 'sync' && Array.isArray(e.data.items)) {
            window.cart = e.data.items;
            updateCartCount();
            document.dispatchEvent(new CustomEvent('cart:updated', { detail: { items: window.cart, external: true } }));
            const cartEl = document.querySelector('.cart');
            if (cartEl && cartEl.classList.contains('active')) renderCart();
        }
    });
}

// ====================== التهيئة ======================
document.addEventListener('DOMContentLoaded', async () => {
    // ترحيل سلة الـ localStorage القديمة (مرة واحدة)
    try {
        const legacy = JSON.parse(localStorage.getItem('cart') || '[]');
        if (Array.isArray(legacy) && legacy.length > 0) {
            const items = legacy
                .map(i => ({ id: parseInt(i.id), quantity: Math.max(1, parseInt(i.quantity) || 1) }))
                .filter(i => i.id > 0);
            if (items.length) {
                const data = await cartFetch(CART_URLS.merge, { method: 'POST', body: JSON.stringify({ items }) });
                setCartState(data);
            }
            localStorage.removeItem('cart');
        } else {
            await loadCart();
        }
    } catch (_) {
        await loadCart();
    }
    console.log('%c✅ Cart System Loaded (server-backed)', 'color:#10b981; font-weight:bold; font-size:14px');
});

// كشف الدوال للاستخدام من الـ inline handlers
window.addToCart = addToCart;
window.removeFromCart = removeFromCart;
window.changeQuantity = changeQuantity;
window.clearCart = clearCart;
window.open_close_cart = open_close_cart;
window.renderCart = renderCart;
window.loadCart = loadCart;

