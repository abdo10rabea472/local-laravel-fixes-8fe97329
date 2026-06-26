// Initialize cart state from localStorage
if (typeof window.cart === 'undefined') {
    window.cart = JSON.parse(localStorage.getItem('cart')) || [];
}

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

// ====================== CART SYSTEM - FINAL & COMPLETE ======================

// تعريف السلة بشكل آمن
if (typeof window.cart === 'undefined') {
    window.cart = JSON.parse(localStorage.getItem('cart')) || [];
}

// ====================== تحديث عداد السلة ======================
function updateCartCount() {
    const countElements = document.querySelectorAll('#cart-count, .cart-count');
    const totalItems = window.cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
    countElements.forEach(el => el.textContent = totalItems);
}

// ====================== حفظ السلة ======================
function saveCart() {
    localStorage.setItem('cart', JSON.stringify(window.cart));
    updateCartCount();
}

// ====================== إضافة منتج للسلة ======================
function addToCart(element) {
    const productCard = element.closest('[data-id]');
    if (!productCard) {
        console.error("❌ Product card not found");
        return;
    }

    const productId = productCard.dataset.id;
    const nameEl = productCard.querySelector('h2, h3');
    const name = nameEl ? nameEl.textContent.trim() : 'Product';

    // استخراج السعر — يفضل قراءة data-price (أكثر دقة من تحليل النص)
    let price = parseFloat(productCard.dataset.price);
    if (!Number.isFinite(price) || price <= 0) {
        const priceElement = productCard.querySelector('[data-price-display], .text-lg.font-black, .text-xl.font-black, .text-sm.font-black, .text-4xl.font-black, .price');
        if (priceElement) {
            price = parseFloat(priceElement.textContent.replace(/[^0-9.]/g, '')) || 0;
        }
    }

    // استخراج الصورة
    const imgElement = productCard.querySelector('img');
    const image = imgElement ? imgElement.src : '';

    // البحث عن المنتج
    const existing = window.cart.find(item => item.id === productId);

    if (existing) {
        existing.quantity += 1;
    } else {
        window.cart.push({
            id: productId,
            name: name,
            price: price,
            quantity: 1,
            image: image
        });
    }

    saveCart();
    showToast(`✅ ${name} تم إضافته بنجاح`);

    // تحديث فوري إذا كانت السلة مفتوحة
    const cartEl = document.querySelector('.cart');
    if (cartEl && cartEl.classList.contains('active')) {
        renderCart();
    }
}

// ====================== إزالة منتج ======================
function removeFromCart(productId) {
    window.cart = window.cart.filter(item => item.id !== productId);
    saveCart();
    renderCart();
}

// ====================== تغيير الكمية ======================
function changeQuantity(productId, change) {
    const item = window.cart.find(i => i.id === productId);
    if (item) {
        item.quantity = Math.max(1, (item.quantity || 1) + change);
        saveCart();
        renderCart();
    }
}

// ====================== عرض السلة ======================
function renderCart() {
    const container = document.querySelector('.items_in_cart');
    if (!container) {
        console.error("❌ .items_in_cart container not found!");
        return;
    }

    container.innerHTML = '';

    if (window.cart.length === 0) {
        container.innerHTML = `
            <div class="text-center py-20 text-gray-400">
                <i class="fa-solid fa-shopping-bag text-6xl mb-4 opacity-50"></i>
                <p class="text-xl">The basket is empty</p>
                <p class="text-sm mt-2">Start shopping now</p>
            </div>
        `;
        updateTotal();
        return;
    }

    let html = '';
    window.cart.forEach(item => {
        html += `
            <div class="cart-item flex gap-4 py-5 border-2 border-solid border-indigo-500 rounded-md px-4 mb-4 gap-12">
                <img src="${item.image}" alt="${item.name}" 
                     class="w-24 h-20 object-contain bg-slate-50 rounded-lg border">
                <div class="flex-1">
                    <h4 class="font-semibold text-sm leading-tight mb-1">${item.name}</h4>
                    <p class="text-emerald-600 font-bold">${item.price.toLocaleString()} EGP</p>
                    
                    <div class="flex items-center justify-between mt-4">
                        <div class="flex items-center border border-gray-300 rounded-xl overflow-hidden">
                            <button onclick="changeQuantity('${item.id}', -1)" 
                                    class="px-3 py-1 text-lg hover:bg-amber-400">-</button>
                            <span class="px-5 font-semibold">${item.quantity}</span>
                            <button onclick="changeQuantity('${item.id}', 1)" 
                                    class="px-3 py-1 text-lg hover:bg-amber-400">+</button>
                        </div>
                        <button onclick="removeFromCart('${item.id}')" 
                                class="text-red-500 hover:text-red-700 p-2 w-10 h-10 flex items-center justify-center rounded-full transition-colors duration-300 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-400 ">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
    updateTotal();
}

// ====================== تحديث الإجمالي ======================
function updateTotal() {
    const totalEl = document.querySelector('.price_cart_toral');
    if (!totalEl) return;

    const total = window.cart.reduce((sum, item) => {
        return sum + (item.price * (item.quantity || 1));
    }, 0);

    totalEl.textContent = `${total.toLocaleString()} EGP`;
}

// ====================== Toast Notification ======================
function showToast(msg) {
    let toast = document.getElementById('toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toast';
        document.body.appendChild(toast);
    }
    toast.style.cssText = `
        position:fixed; 
        bottom:20px; 
        left:50%; 
        transform:translateX(-50%); 
        background:#1f2937; 
        color:white; 
        padding:14px 24px; 
        border-radius:9999px; 
        z-index:99999; 
        box-shadow:0 10px 15px -3px rgb(0 0 0 / 0.3);
    `;
    toast.textContent = msg;
    toast.style.opacity = 1;

    setTimeout(() => {
        toast.style.opacity = 0;
    }, 2800);
}

// ====================== فتح وإغلاق السلة ======================
function open_close_cart() {
    const cartEl = document.querySelector('.cart');
    if (!cartEl) return;

    const isOpening = !cartEl.classList.contains('active');
    
    cartEl.classList.toggle('active');

    if (isOpening) {
        setTimeout(() => {
            renderCart();
        }, 10);
    }
}

// ====================== تهيئة عند تحميل الصفحة ======================
document.addEventListener('DOMContentLoaded', () => {
    updateCartCount();
    console.log('%c✅ Cart System Loaded Successfully', 'color:#10b981; font-weight:bold; font-size:14px');
});