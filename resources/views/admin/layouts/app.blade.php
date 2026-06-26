<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>لوحة التحكم | UNI-LAB MARKET</title>

    <!-- Google Fonts: Cairo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- AlpineJS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')

    <!-- Tailwind Play CDN for layout flexibility & styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        cairo: ['Cairo', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(241, 245, 249, 1);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(203, 213, 225, 1);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, 1);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex" x-data="{ sidebarOpen: false, isDesktop: window.innerWidth >= 1024 }" x-init="if (isDesktop) sidebarOpen = true; window.addEventListener('resize', () => { isDesktop = window.innerWidth >= 1024; if (!isDesktop) sidebarOpen = false; });">

    <!-- Mobile overlay -->
    <div x-show="sidebarOpen && !isDesktop" @click="sidebarOpen = false"
         class="fixed inset-0 bg-slate-900/50 z-20 lg:hidden"
         x-transition.opacity.duration.300ms></div>

    <!-- Sidebar الشريط الجانبي -->
    <aside
        class="bg-slate-900 text-white w-64 min-h-screen flex flex-col transition-all duration-300 z-30 shrink-0 shadow-xl fixed lg:static inset-y-0 right-0 lg:right-auto"
        :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0 lg:-ml-64'"
    >
        <!-- Logo -->
        <div class="h-16 flex items-center justify-between px-6 border-b border-slate-800">
            <span class="text-lg font-black tracking-wider bg-gradient-to-r from-violet-400 to-indigo-400 bg-clip-text text-transparent flex items-center gap-2">
                <i class="fa-solid fa-store"></i>
                UNI-LAB MARKET
            </span>
            <button @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-white">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <!-- User profile summary -->
        <div class="p-6 border-b border-slate-800 bg-slate-950/40">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-violet-500 to-indigo-600 flex items-center justify-center font-bold text-white shadow-lg">
                    {{ substr(Auth::guard('admin')->user()->name ?? 'أدمن', 0, 2) }}
                </div>
                <div>
                    <h4 class="text-sm font-bold text-slate-100">{{ Auth::guard('admin')->user()->name ?? 'المدير العام' }}</h4>
                    <span class="text-[11px] text-violet-400 font-bold bg-violet-950/60 px-2 py-0.5 rounded-full border border-violet-800/30">مسؤول النظام</span>
                </div>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-4 py-6 space-y-2">
            <a 
                href="{{ route('admin.dashboard') }}" 
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all group {{ request()->routeIs('admin.dashboard') ? 'bg-violet-600 text-white shadow-lg shadow-violet-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
            >
                <i class="fa-solid fa-chart-line text-lg group-hover:scale-110 transition-transform"></i>
                <span>لوحة الإحصائيات</span>
            </a>

            <a 
                href="{{ route('admin.colleges.index') }}" 
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all group {{ request()->routeIs('admin.colleges.*') ? 'bg-violet-600 text-white shadow-lg shadow-violet-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
            >
                <i class="fa-solid fa-building-columns text-lg group-hover:scale-110 transition-transform"></i>
                <span>تصنيفات الكليات</span>
            </a>

            <a 
                href="{{ route('admin.subcategories.index') }}" 
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all group {{ request()->routeIs('admin.subcategories.*') ? 'bg-violet-600 text-white shadow-lg shadow-violet-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
            >
                <i class="fa-solid fa-sitemap text-lg group-hover:scale-110 transition-transform"></i>
                <span>التصنيفات الفرعية</span>
            </a>

            <a 
                href="{{ route('admin.products.index') }}" 
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all group {{ request()->routeIs('admin.products.index') || request()->routeIs('admin.products.edit') ? 'bg-violet-600 text-white shadow-lg shadow-violet-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
            >
                <i class="fa-solid fa-box text-lg group-hover:scale-110 transition-transform"></i>
                <span>قائمة المنتجات</span>
            </a>

            <a 
                href="{{ route('admin.products.create') }}" 
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all group {{ request()->routeIs('admin.products.create') ? 'bg-violet-600 text-white shadow-lg shadow-violet-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
            >
                <i class="fa-solid fa-circle-plus text-lg group-hover:scale-110 transition-transform"></i>
                <span>إضافة منتج</span>
            </a>

            <a 
                href="{{ route('admin.product-discounts.index') }}" 
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all group {{ request()->routeIs('admin.product-discounts.*') ? 'bg-violet-600 text-white shadow-lg shadow-violet-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
            >
                <i class="fa-solid fa-percent text-lg group-hover:scale-110 transition-transform"></i>
                <span>خصومات المنتجات</span>
            </a>

            <a 
                href="{{ route('admin.coupons.index') }}" 
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all group {{ request()->routeIs('admin.coupons.*') ? 'bg-violet-600 text-white shadow-lg shadow-violet-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
            >
                <i class="fa-solid fa-ticket text-lg group-hover:scale-110 transition-transform"></i>
                <span>أكواد الخصم</span>
            </a>

            <a 
                href="{{ route('admin.settings.index') }}" 
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all group {{ request()->routeIs('admin.settings.*') ? 'bg-violet-600 text-white shadow-lg shadow-violet-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
            >
                <i class="fa-solid fa-sliders text-lg group-hover:scale-110 transition-transform"></i>
                <span>إعدادات الموقع</span>
            </a>

            <div class="pt-6 border-t border-slate-800 my-4"></div>

            <a 
                href="{{ url('/') }}" 
                target="_blank"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-slate-400 hover:bg-slate-800 hover:text-white transition-all group"
            >
                <i class="fa-solid fa-globe text-lg group-hover:rotate-45 transition-transform"></i>
                <span>زيارة المتجر</span>
            </a>

            <form method="POST" action="{{ route('admin.logout') }}" class="block">
                @csrf
                <button 
                    type="submit" 
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-rose-400 hover:bg-rose-950/20 hover:text-rose-300 transition-all text-right group"
                >
                    <i class="fa-solid fa-right-from-bracket text-lg group-hover:-translate-x-1 transition-transform"></i>
                    <span>تسجيل الخروج</span>
                </button>
            </form>
        </nav>
    </aside>

    <!-- Content Area المنطقة الرئيسية -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        
        <!-- Header القائمة العلوية -->
        <header class="bg-white border-b border-slate-200 h-16 shrink-0 flex items-center justify-between px-6 sticky top-0 z-20 shadow-sm backdrop-blur-md bg-white/95">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 focus:outline-none">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <h2 class="text-lg font-bold text-slate-800">
                    @yield('title', 'لوحة التحكم')
                </h2>
            </div>

            <!-- Profile Summary & Date -->
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-slate-500 hidden sm:inline">
                    {{ now()->translatedFormat('l, d F Y') }}
                </span>
                <div class="h-8 w-px bg-slate-200 hidden sm:block"></div>
                <div class="flex items-center gap-3">
                    <span class="text-sm font-bold text-slate-700">{{ Auth::guard('admin')->user()->name ?? 'أدمن' }}</span>
                    <div class="h-8 w-8 rounded-full bg-violet-100 text-violet-700 flex items-center justify-center font-bold">
                        A
                    </div>
                </div>
            </div>
        </header>

        <!-- Main View محتوى الصفحة -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 min-w-0 overflow-x-auto">
            <!-- Toast notification messages (إشعارات النجاح أو الفشل) -->
            @if(session('success'))
            <div 
                x-data="{ show: true }" 
                x-init="setTimeout(() => show = false, 5000)" 
                x-show="show" 
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-2"
                class="fixed bottom-6 left-6 z-50 bg-emerald-50 border border-emerald-200 text-emerald-800 px-6 py-4 rounded-2xl shadow-xl flex items-center gap-3"
            >
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                <div class="text-sm font-bold">{{ session('success') }}</div>
                <button @click="show = false" class="text-emerald-500 hover:text-emerald-800 ml-auto">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            @endif

            @if(session('error'))
            <div 
                x-data="{ show: true }" 
                x-init="setTimeout(() => show = false, 5000)" 
                x-show="show" 
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-2"
                class="fixed bottom-6 left-6 z-50 bg-rose-50 border border-rose-200 text-rose-800 px-6 py-4 rounded-2xl shadow-xl flex items-center gap-3"
            >
                <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg"></i>
                <div class="text-sm font-bold">{{ session('error') }}</div>
                <button @click="show = false" class="text-rose-500 hover:text-rose-800 ml-auto">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script src="{{ asset('js/ajax.js') }}"></script>
    <script>
        /**
         * Admin AJAX wiring with optimistic UI + rollback.
         * Attributes:
         *   data-ajax-confirm="msg"   → confirm() before submit
         *   data-ajax-remove          → optimistic remove closest [data-row]/tr; restore on failure
         *   data-ajax-toggle          → toggle button label/style optimistically; restore on failure
         *   data-toggle-on="نشط"      → label when active
         *   data-toggle-off="معطل"    → label when inactive
         *   data-ajax                 → generic AJAX submit (uses window.UL.submitForm if available)
         */
        (function () {
            const csrf = () => document.querySelector('meta[name=csrf-token]')?.content || '';
            const toast = (m, t = 'info') => window.UL ? window.UL.toast(m, t) : console.log(m);

            function setLoading(btn, on) {
                if (!btn) return;
                if (on) {
                    btn.dataset._origHtml = btn.innerHTML;
                    btn.dataset._origDisabled = btn.disabled ? '1' : '';
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
                } else {
                    btn.disabled = btn.dataset._origDisabled === '1';
                    if (btn.dataset._origHtml) btn.innerHTML = btn.dataset._origHtml;
                    delete btn.dataset._origHtml;
                    delete btn.dataset._origDisabled;
                }
            }

            async function sendForm(form) {
                const fd = new FormData(form);
                const method = (fd.get('_method') || form.method || 'POST').toString().toUpperCase();
                const headers = {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                };
                let url = form.action;
                let body = fd;
                if (method === 'GET') {
                    url += (url.includes('?') ? '&' : '?') + new URLSearchParams(fd).toString();
                    body = undefined;
                }
                const res = await fetch(url, {
                    method: method === 'GET' ? 'GET' : 'POST',
                    headers,
                    body,
                    credentials: 'same-origin',
                });
                let data = {};
                try { data = await res.json(); } catch (_) {}
                return { res, data };
            }

            document.addEventListener('submit', async function (e) {
                const form = e.target.closest(
                    'form[data-ajax-confirm], form[data-ajax-remove], form[data-ajax-toggle], form[data-ajax-simple]'
                );
                if (!form) return;
                // Forms using data-ajax (UL.submitForm) are handled in ajax.js — don't intercept.
                if (form.hasAttribute('data-ajax')) return;
                e.preventDefault();

                const msg = form.getAttribute('data-ajax-confirm');
                if (msg && !confirm(msg)) return;

                const btn = form.querySelector('button[type=submit], button:not([type])');
                const isRemove = form.hasAttribute('data-ajax-remove');
                const isToggle = form.hasAttribute('data-ajax-toggle');

                const row = isRemove ? form.closest('tr, [data-row]') : null;
                let removedAnchor = null;
                let removedRow = null;

                // --- Optimistic UI ---
                if (isRemove && row) {
                    removedAnchor = row.nextSibling;
                    removedRow = row.parentNode;
                    row.style.transition = 'opacity .2s';
                    row.style.opacity = '0.4';
                }

                let prevToggleState = null;
                if (isToggle && btn) {
                    const isOn = btn.dataset.toggleState === 'on';
                    prevToggleState = {
                        state: btn.dataset.toggleState,
                        html: btn.innerHTML,
                        cls: btn.className,
                    };
                    const onLabel = btn.dataset.toggleOn || 'تعطيل';
                    const offLabel = btn.dataset.toggleOff || 'تفعيل';
                    btn.dataset.toggleState = isOn ? 'off' : 'on';
                    btn.innerHTML = isOn ? offLabel : onLabel;
                }

                setLoading(btn, true);

                try {
                    const { res, data } = await sendForm(form);

                    if (!res.ok) {
                        // Rollback
                        if (isRemove && row) row.style.opacity = '1';
                        if (isToggle && btn && prevToggleState) {
                            btn.dataset.toggleState = prevToggleState.state || '';
                            btn.innerHTML = prevToggleState.html;
                            btn.className = prevToggleState.cls;
                        }
                        if (res.status === 422 && data.errors) {
                            const first = Object.values(data.errors)[0];
                            toast(Array.isArray(first) ? first[0] : (data.message || 'خطأ في البيانات'), 'error');
                        } else {
                            toast(data.message || `حدث خطأ (${res.status})`, 'error');
                        }
                        return;
                    }

                    // Success
                    if (isRemove && row) {
                        row.style.height = row.offsetHeight + 'px';
                        requestAnimationFrame(() => {
                            row.style.height = '0px';
                            row.style.overflow = 'hidden';
                            setTimeout(() => row.remove(), 200);
                        });
                    }
                    toast(data.message || 'تم بنجاح', 'success');
                } catch (err) {
                    // Network/parse failure → rollback
                    if (isRemove && row) row.style.opacity = '1';
                    if (isToggle && btn && prevToggleState) {
                        btn.dataset.toggleState = prevToggleState.state || '';
                        btn.innerHTML = prevToggleState.html;
                        btn.className = prevToggleState.cls;
                    }
                    toast('فشل الاتصال بالخادم. تمت استعادة الحالة.', 'error');
                } finally {
                    setLoading(btn, false);
                }
            });
        })();
    </script>
    @stack('scripts')
    <script src="https://instant.page/5.2.0" type="module" integrity="sha384-jnZyxPjiipYXnSU0ygqeac2q7CVYMbh84q0uHVRRxEtvFPiQYbXWUorga2aqZJ0z"></script>
</body>
</html>
