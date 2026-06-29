<!DOCTYPE html>
<html lang="en" dir="ltr" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') | UNI-LAB MARKET</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        // Apply theme ASAP to avoid flash
        (function(){
            try{
                var t = localStorage.getItem('theme');
                if(t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)){
                    document.documentElement.classList.add('dark');
                    document.documentElement.classList.remove('light');
                }
            }catch(e){}
        })();
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    <style>
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 20px; }
        .dark ::-webkit-scrollbar-thumb { background: #334155; }
        .dir-ltr { direction: ltr !important; }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50 dark:bg-dark-950 text-gray-800 dark:text-gray-100 antialiased transition-colors duration-200">

@php
    $admin = auth('admin')->user();
    $adminName = $admin->name ?? 'Admin';
    $r = fn($name) => \Illuminate\Support\Facades\Route::has($name) ? route($name) : '#';

    $menu = [
        ['title' => 'Dashboard', 'icon' => 'fa-chart-pie', 'route' => 'admin.dashboard'],
        ['title' => 'Product Management', 'icon' => 'fa-boxes', 'children' => [
            ['title' => 'All Products',         'route' => 'admin.products.index'],
            ['title' => 'Add New Product',      'route' => 'admin.products.create'],
            ['title' => 'Main Categories',      'route' => 'admin.colleges.index'],
            ['title' => 'Subcategories',        'route' => 'admin.subcategories.index'],
            ['title' => 'Product Discounts',    'route' => 'admin.product-discounts.index'],
        ]],
        ['title' => 'Orders & Sales', 'icon' => 'fa-shopping-cart', 'route' => 'admin.orders.index'],
        ['title' => 'Customers',      'icon' => 'fa-users',         'route' => 'admin.customers.index'],
        ['title' => 'Customer Groups','icon' => 'fa-layer-group',   'route' => 'admin.customer-groups.index'],
        ['title' => 'Stock & Supply', 'icon' => 'fa-warehouse', 'children' => [
            ['title' => 'Stock Management', 'route' => 'admin.stock.index'],
            ['title' => 'Stock History',    'route' => 'admin.stock.history'],
            ['title' => 'Shipping Carriers','route' => 'admin.shipping-carriers.index'],
            ['title' => 'Returns',          'route' => 'admin.returns.index'],
        ]],
        ['title' => 'Reviews & Ratings', 'icon' => 'fa-star',          'route' => 'admin.reviews.index'],
        ['title' => 'Coupons & Discounts','icon' => 'fa-ticket-alt',   'route' => 'admin.coupons.index'],
        ['title' => 'Advanced Reports', 'icon' => 'fa-chart-line', 'children' => [
            ['title' => 'Sales Analytics',  'route' => 'admin.reports.analytics'],
            ['title' => 'Sales Report',     'route' => 'admin.reports.sales'],
            ['title' => 'Inventory Reports','route' => 'admin.reports.inventory'],
            ['title' => 'Coupon Reports',   'route' => 'admin.reports.coupons'],
        ]],
        ['title' => 'Content Management', 'icon' => 'fa-newspaper', 'children' => [
            ['title' => 'Blog Posts',       'route' => 'admin.blog.index'],

            ['title' => 'FAQs',             'route' => 'admin.faqs.index'],
            ['title' => 'Contact Messages', 'route' => 'admin.messages.index'],
            ['title' => 'Newsletter Subscribers', 'route' => 'admin.subscribers.index'],
        ]],
        ['title' => 'General Settings', 'icon' => 'fa-sliders-h', 'route' => 'admin.settings.index'],
    ];


    $isActiveRoute = function($name){
        if(!$name) return false;
        return request()->routeIs($name) || request()->routeIs(\Illuminate\Support\Str::beforeLast($name, '.').'.*');
    };
@endphp

<div class="flex h-screen overflow-hidden">

    {{-- Mobile overlay --}}
    <div id="sidebar-overlay" class="hidden fixed inset-0 bg-gray-900/40 z-40 lg:hidden" onclick="toggleSidebar()"></div>

    {{-- Sidebar --}}
    <aside id="sidebar"
           class="fixed inset-y-0 left-0 z-50 flex flex-col w-64 bg-white dark:bg-dark-900 border-r border-gray-200 dark:border-gray-800 transition-transform duration-300 transform -translate-x-full lg:translate-x-0 lg:static lg:inset-auto">

        {{-- Logo --}}
        <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200 dark:border-gray-800 shrink-0">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-primary-600 text-white rounded-lg shadow-md shadow-primary-500/20">
                    <i class="fas fa-microscope text-xl"></i>
                </div>
                <span class="text-lg font-bold tracking-wider text-gray-900 dark:text-white">UNI-LAB <span class="text-primary-600">MARKET</span></span>
            </div>
            <button onclick="toggleSidebar()" class="text-gray-500 lg:hidden hover:text-gray-700 dark:hover:text-gray-300">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto" id="sidebar-menu">
            @foreach($menu as $idx => $item)
                @if(isset($item['children']))
                    @php
                        $anyChildActive = collect($item['children'])->contains(fn($c) => $isActiveRoute($c['route']));
                    @endphp
                    <div class="space-y-1">
                        <button type="button" onclick="toggleSubmenu('sub-{{ $idx }}')"
                                class="w-full flex items-center justify-between py-2.5 px-4 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-800 rounded-lg transition-colors">
                            <div class="flex items-center gap-3">
                                <i class="fas {{ $item['icon'] }} text-gray-400 w-5"></i>
                                <span>{{ $item['title'] }}</span>
                            </div>
                            <i id="arrow-sub-{{ $idx }}" class="fas fa-chevron-right text-xs transition-transform duration-200 {{ $anyChildActive ? 'rotate-90' : '' }}"></i>
                        </button>
                        <div id="sub-{{ $idx }}" class="{{ $anyChildActive ? 'flex' : 'hidden' }} flex-col space-y-1 pl-2 border-l border-gray-100 dark:border-gray-800">
                            @foreach($item['children'] as $child)
                                <a href="{{ $r($child['route']) }}"
                                   class="flex items-center gap-2 py-2 pl-9 pr-4 text-sm rounded-lg transition-colors
                                          {{ $isActiveRoute($child['route']) ? 'bg-primary-50 text-primary-600 dark:bg-dark-800 dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-white' }}">
                                    <i class="fas fa-circle text-[6px]"></i> {{ $child['title'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <a href="{{ $r($item['route']) }}"
                       class="flex items-center gap-3 py-2.5 px-4 text-sm font-medium rounded-lg transition-colors
                              {{ $isActiveRoute($item['route']) ? 'bg-primary-50 text-primary-600 dark:bg-dark-800 dark:text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-800' }}">
                        <i class="fas {{ $item['icon'] }} {{ $isActiveRoute($item['route']) ? 'text-primary-600' : 'text-gray-400' }} w-5"></i>
                        <span>{{ $item['title'] }}</span>
                    </a>
                @endif
            @endforeach

            <div class="pt-4 mt-4 border-t border-gray-100 dark:border-gray-800 space-y-1">
                <a href="{{ url('/') }}" target="_blank"
                   class="flex items-center gap-3 py-2.5 px-4 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-800 rounded-lg transition-colors">
                    <i class="fas fa-globe text-gray-400 w-5"></i><span>Visit Store</span>
                </a>
                <form method="POST" action="{{ $r('admin.logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center gap-3 py-2.5 px-4 text-sm font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition-colors">
                        <i class="fas fa-sign-out-alt text-red-500 w-5"></i><span>Logout</span>
                    </button>
                </form>
            </div>
        </nav>
    </aside>

    {{-- Main column --}}
    <div class="flex flex-col flex-1 min-w-0 overflow-hidden">

        {{-- Header --}}
        <header class="flex items-center justify-between h-16 px-4 sm:px-6 bg-white dark:bg-dark-900 border-b border-gray-200 dark:border-gray-800 z-40 shrink-0">
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 lg:hidden">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <div class="relative hidden md:block w-72">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" placeholder="Quick search..."
                           class="w-full py-2 pr-4 pl-10 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:outline-none focus:border-primary-500 transition-colors">
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-4">
                {{-- Language switcher --}}
                @if(!empty($availableLanguages) && count($availableLanguages) > 0)
                <div class="relative">
                    <button onclick="toggleDropdown('admin-lang-menu', event)" class="flex items-center gap-2 px-2 sm:px-3 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-800 rounded-lg transition-colors" aria-label="Switch language">
                        <i class="fas fa-language text-lg"></i>
                        <span class="hidden sm:inline text-xs font-bold uppercase">{{ $currentLocale ?? app()->getLocale() }}</span>
                    </button>
                    <div id="admin-lang-menu" class="hidden absolute right-0 mt-2 w-48 max-h-72 overflow-y-auto bg-white dark:bg-dark-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-xl z-50 py-1">
                        @foreach($availableLanguages as $lang)
                            <a href="{{ url('/locale/'.$lang->code) }}"
                               class="flex items-center justify-between gap-2 px-4 py-2 text-sm {{ ($currentLocale ?? null) === $lang->code ? 'bg-primary-50 dark:bg-dark-800 text-primary-600 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-800' }}">
                                <span class="flex items-center gap-2">
                                    <span class="text-xs font-mono uppercase text-gray-400">{{ $lang->code }}</span>
                                    <span>{{ $lang->name }}</span>
                                </span>
                                @if(($currentLocale ?? null) === $lang->code)
                                    <i class="fas fa-check text-xs"></i>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Currency switcher --}}
                @if(!empty($availableCurrencies) && count($availableCurrencies) > 0)
                <div class="relative">
                    <button onclick="toggleDropdown('admin-currency-menu', event)" class="flex items-center gap-2 px-2 sm:px-3 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-800 rounded-lg transition-colors" aria-label="Switch currency">
                        <i class="fas fa-money-bill-wave text-lg"></i>
                        <span class="hidden sm:inline text-xs font-bold uppercase">{{ optional($currentCurrency)->code ?? '—' }}</span>
                    </button>
                    <div id="admin-currency-menu" class="hidden absolute right-0 mt-2 w-48 max-h-72 overflow-y-auto bg-white dark:bg-dark-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-xl z-50 py-1">
                        @foreach($availableCurrencies as $cur)
                            <a href="{{ request()->fullUrlWithQuery(['currency' => $cur->code]) }}"
                               class="flex items-center justify-between gap-2 px-4 py-2 text-sm {{ optional($currentCurrency)->code === $cur->code ? 'bg-primary-50 dark:bg-dark-800 text-primary-600 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-800' }}">
                                <span class="flex items-center gap-2">
                                    <span class="text-xs font-mono uppercase text-gray-400">{{ $cur->code }}</span>
                                    <span>{{ $cur->name }}</span>
                                </span>
                                @if(optional($currentCurrency)->code === $cur->code)
                                    <i class="fas fa-check text-xs"></i>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif

                <button onclick="toggleDarkMode()" class="p-2 text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-500 rounded-lg transition-colors" aria-label="Toggle theme">
                    <i id="theme-icon" class="fas fa-moon text-lg"></i>
                </button>


                <div class="relative">
                    <button onclick="toggleDropdown('notifications-menu', event); loadAdminNotifications();" class="relative p-2 text-gray-500 hover:text-primary-600 dark:text-gray-400 rounded-lg transition-colors">
                        <i class="fas fa-bell text-lg"></i>
                        <span id="notif-badge" class="hidden absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-[10px] font-bold rounded-full grid place-items-center">0</span>
                    </button>
                    <div id="notifications-menu" class="hidden absolute right-0 mt-2 w-80 bg-white dark:bg-dark-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-xl z-50 py-2">
                        <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-800 font-bold text-sm flex justify-between items-center">
                            <span>Recent Notifications</span>
                            <span class="text-xs text-primary-600 cursor-pointer" onclick="loadAdminNotifications()"><i class="fas fa-rotate"></i> Refresh</span>
                        </div>
                        <div class="max-h-72 overflow-y-auto" id="notification-items">
                            <div class="p-6 text-center text-xs text-gray-400"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
                        </div>
                    </div>
                </div>
                <script>
                    async function loadAdminNotifications() {
                        try {
                            const res = await fetch('{{ route('admin.notifications.feed') }}', { headers: { 'Accept': 'application/json' }});
                            const data = await res.json();
                            const badge = document.getElementById('notif-badge');
                            const box   = document.getElementById('notification-items');
                            if (!badge || !box) return;
                            if (data.count > 0) { badge.textContent = data.count; badge.classList.remove('hidden'); }
                            else { badge.classList.add('hidden'); }
                            if (!data.items.length) {
                                box.innerHTML = '<div class="p-6 text-center text-xs text-gray-400">No new notifications</div>';
                                return;
                            }
                            box.innerHTML = data.items.map(i => `
                                <a href="${i.url}" class="flex gap-3 p-3 hover:bg-gray-50 dark:hover:bg-dark-800 border-b border-gray-100 dark:border-gray-800 transition-colors">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 text-${i.color}-500 bg-${i.color}-50 dark:bg-${i.color}-950/30"><i class="fas ${i.icon} text-xs"></i></div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs text-gray-700 dark:text-gray-200 font-semibold truncate">${i.title}</p>
                                        <div class="flex gap-2 items-center mt-0.5">
                                            ${i.meta ? `<span class="text-[10px] text-gray-500">${i.meta}</span>` : ''}
                                            ${i.time ? `<span class="text-[10px] text-gray-400">· ${i.time}</span>` : ''}
                                        </div>
                                    </div>
                                </a>`).join('');
                        } catch (e) { console.error(e); }
                    }
                    // Initial load + refresh every 60 seconds
                    loadAdminNotifications();
                    setInterval(loadAdminNotifications, 60000);
                </script>

                <div class="relative">
                    <button onclick="toggleDropdown('user-profile-menu', event)" class="flex items-center gap-3 focus:outline-none">
                        <div class="w-9 h-9 rounded-lg bg-primary-600 text-white flex items-center justify-center font-bold ring-2 ring-primary-500/20">
                            {{ mb_substr($adminName, 0, 1) }}
                        </div>
                        <div class="hidden md:block text-left">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white leading-tight">{{ $adminName }}</p>
                            <span class="text-xs text-gray-400">System Administrator</span>
                        </div>
                    </button>
                    <div id="user-profile-menu" class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-dark-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-xl z-50 py-1">
                        <a href="{{ $r('admin.settings.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-800"><i class="fas fa-cog w-5"></i>Store Settings</a>
                        <hr class="border-gray-100 dark:border-gray-800 my-1">
                        <form method="POST" action="{{ $r('admin.logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 text-left">
                                <i class="fas fa-sign-out-alt w-5"></i>Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Main content --}}
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 bg-gray-50 dark:bg-dark-950" id="main-content">
            @if(session('success'))
                <div class="mb-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900 text-red-700 dark:text-red-400 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<div id="toast-container" class="fixed bottom-5 right-5 z-50 space-y-2"></div>

<script>
    function toggleSidebar(){
        const sb = document.getElementById('sidebar');
        const ov = document.getElementById('sidebar-overlay');
        sb.classList.toggle('-translate-x-full');
        sb.classList.toggle('translate-x-0');
        ov.classList.toggle('hidden');
    }
    function toggleSubmenu(id){
        const sub = document.getElementById(id);
        const arrow = document.getElementById('arrow-' + id);
        const opening = sub.classList.contains('hidden');
        sub.classList.toggle('hidden', !opening);
        sub.classList.toggle('flex', opening);
        if(arrow) arrow.classList.toggle('rotate-90', opening);
    }
    function toggleDropdown(id, evt){
        if(evt) evt.stopPropagation();
        const menu = document.getElementById(id);
        document.querySelectorAll('[id$="-menu"]').forEach(m => { if(m.id !== id) m.classList.add('hidden'); });
        menu.classList.toggle('hidden');
        document.addEventListener('click', function close(e){
            if(!menu.parentElement.contains(e.target)){
                menu.classList.add('hidden');
                document.removeEventListener('click', close);
            }
        });
    }
    function applyThemeIcon(){
        const isDark = document.documentElement.classList.contains('dark');
        const i = document.getElementById('theme-icon');
        if(i) i.className = isDark ? 'fas fa-sun text-lg' : 'fas fa-moon text-lg';
    }
    function toggleDarkMode(){
        const isDark = document.documentElement.classList.toggle('dark');
        document.documentElement.classList.toggle('light', !isDark);
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        applyThemeIcon();
        window.dispatchEvent(new CustomEvent('themechange', { detail: { dark: isDark } }));
    }
    function showToast(message, type='success'){
        const c = document.getElementById('toast-container');
        const t = document.createElement('div');
        const bg = type === 'success' ? 'bg-emerald-500' : (type === 'error' ? 'bg-red-500' : 'bg-gray-700');
        const icon = type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle');
        t.className = `${bg} text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3 transform transition-all duration-300 opacity-0 translate-y-2`;
        t.innerHTML = `<i class="fas ${icon}"></i><span class="text-sm font-medium">${message}</span>`;
        c.appendChild(t);
        setTimeout(() => t.classList.remove('opacity-0','translate-y-2'), 10);
        setTimeout(() => { t.classList.add('opacity-0','translate-y-2'); setTimeout(() => t.remove(), 300); }, 4000);
    }
    document.addEventListener('DOMContentLoaded', applyThemeIcon);
</script>

<script src="{{ asset('js/ajax.js') }}"></script>
@stack('scripts')
</body>
</html>
