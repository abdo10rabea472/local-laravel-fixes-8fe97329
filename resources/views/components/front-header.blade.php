{{-- Shopping Cart Sidebar --}}
<div class="cart">
    <div class="top_cart">
        <h2 class="text flex items-center gap-2">
            <i class="fa-solid fa-bag-shopping"></i>
            Shopping Cart
            <span class="cart-count">(0)</span>
        </h2>
        <span onclick="open_close_cart()" class="close_cart">
            <i class="fa-regular fa-circle-xmark"></i>
        </span>
    </div>
    <div class="items_in_cart"></div>
    <div class="bottom_cart">
        <div class="total">
            <p>TOTAL</p>
            <p class="price_cart_toral">0 EGP</p>
        </div>
        <div class="button_cart">
            <a href="{{ route('checkout') }}" class="btn_cart btn">Checkout</a>
            <span onclick="open_close_cart()" class="btn_cart trans_bg btn">Shop More</span>
        </div>
    </div>
</div>

@php
    $currentCategorySlug = request()->routeIs('category.show') ? request()->route('slug') : null;
    $primaryColor = site_setting('primary_color', '#7c3aed');
@endphp

<header class="sticky top-0 z-50 w-full">
    {{-- ═══ Announcement / utility bar ═══ --}}
    <div class="bg-gradient-to-r from-violet-700 via-indigo-700 to-violet-800 text-white text-[11px] sm:text-xs">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-2 flex items-center justify-between gap-3">
            <div class="flex items-center gap-4 min-w-0">
                @if(site_setting('contact_phone'))
                <a href="tel:{{ site_setting('contact_phone') }}" class="hover:text-amber-300 transition-colors inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-phone text-[10px]"></i>
                    <span class="truncate">{{ site_setting('contact_phone') }}</span>
                </a>
                @endif
                @if(site_setting('contact_email'))
                <a href="mailto:{{ site_setting('contact_email') }}" class="hover:text-amber-300 transition-colors hidden sm:inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-envelope text-[10px]"></i>
                    <span class="truncate">{{ site_setting('contact_email') }}</span>
                </a>
                @endif
            </div>

            @if(site_setting('free_shipping_enabled', '1') === '1' && site_setting('free_shipping_show_in_header', '1') === '1')
            <button type="button"
                    onclick="if (typeof openFreeShippingPopup === 'function') { openFreeShippingPopup(); }"
                    class="hidden md:inline-flex items-center gap-1.5 font-bold text-amber-300 hover:text-amber-200 transition-colors bg-transparent border-0 p-0 cursor-pointer">
                <i class="fa-solid fa-truck-fast text-[10px]"></i>
                Free shipping over {{ number_format((float) site_setting('free_shipping_threshold', 2000), 0) }} EGP
            </button>
            @endif

            <div class="flex items-center gap-3 shrink-0">
                @if(($navTopMenu ?? collect())->isNotEmpty())
                    @foreach($navTopMenu as $item)
                        @if($item->type === 'coupon')
                            <a href="#" onclick="openWelcomePopup('{{ $item->coupon_code }}', {{ $item->coupon_percent ?? 0 }}); return false;" class="hover:text-amber-300 transition-colors">{{ $item->title }}</a>
                        @else
                            <a href="{{ $item->url }}" target="{{ $item->target }}" class="hover:text-amber-300 transition-colors">{{ $item->title }}</a>
                        @endif
                    @endforeach
                @else
                    @guest('web')
                        @if(!auth()->guard('admin')->check())
                        <a href="{{ route('login') }}" class="hover:text-amber-300 transition-colors">Sign in</a>
                        <span class="text-white/40">|</span>
                        <a href="{{ route('register') }}" class="hover:text-amber-300 transition-colors">Register</a>
                        @endif
                    @endguest
                    @auth('web')
                        <a href="{{ route('account.dashboard') }}" class="hover:text-amber-300 transition-colors inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-user-circle text-[10px]"></i> My Account
                        </a>
                    @endauth
                @endif
            </div>
        </div>
    </div>

    {{-- ═══ Main header ═══ --}}
    <div class="bg-white/95 backdrop-blur-md border-b border-slate-200/70 shadow-sm">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex h-20 items-center justify-between gap-4 lg:gap-8">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="shrink-0 flex items-center gap-2.5 group">
                    @if(site_setting_url('site_logo'))
                        <img src="{{ site_setting_url('site_logo') }}" alt="UNI-LAB MARKET" class="h-11 w-auto object-contain">
                    @else
                        <span class="w-11 h-11 rounded-2xl bg-gradient-to-br from-violet-600 to-indigo-700 text-white grid place-items-center text-lg font-black shadow-lg shadow-violet-500/30 group-hover:scale-105 transition">
                            <i class="fa-solid fa-flask-vial"></i>
                        </span>
                        <span class="flex flex-col leading-none">
                            <span class="text-lg font-black text-slate-900 tracking-tight">UNI-LAB</span>
                            <span class="text-[9px] font-bold text-violet-600 tracking-[0.2em] uppercase">Market</span>
                        </span>
                    @endif
                </a>

                {{-- Search --}}
                <div class="hidden md:flex flex-1 max-w-2xl">
                    <form action="{{ route('products.index') }}" method="get" class="relative w-full group">
                        <input type="search" name="search" value="{{ request('search') }}"
                               class="w-full h-12 pl-12 pr-4 bg-slate-100 border border-transparent focus:border-violet-400 focus:bg-white rounded-full text-sm outline-none transition-all focus:ring-3 focus:ring-violet-100"
                               placeholder="Search microscopes, dissection kits, lab glassware...">
                        <button type="submit" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-violet-600 transition-colors">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </form>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                    @if(auth()->guard('admin')->check())
                        <a href="{{ route('admin.dashboard') }}" class="hidden lg:inline-flex items-center gap-2 h-11 px-4 rounded-full text-xs font-bold text-white bg-gradient-to-r from-violet-600 to-indigo-600 hover:opacity-90 transition shadow-md shadow-violet-500/30">
                            <i class="fa-solid fa-user-shield"></i> Admin
                        </a>
                    @endif

                    @guest('web')
                        @if(!auth()->guard('admin')->check())
                        <a href="{{ route('login') }}"
                           class="hidden sm:inline-flex items-center gap-2 h-11 px-4 rounded-full text-sm font-bold text-slate-700 border border-slate-200 hover:border-violet-400 hover:text-violet-700 hover:bg-violet-50/50 transition">
                            <i class="fa-solid fa-right-to-bracket text-xs"></i>
                            <span>Sign in</span>
                        </a>
                        <a href="{{ route('register') }}"
                           class="hidden md:inline-flex items-center gap-2 h-11 px-4 rounded-full text-sm font-bold text-white bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 transition shadow-md shadow-violet-500/30">
                            <i class="fa-solid fa-user-plus text-xs"></i>
                            <span>Register</span>
                        </a>
                        @endif
                    @endguest

                    @auth('web')
                        <div class="relative group hidden sm:block">
                            <button type="button" class="flex items-center gap-2 h-11 pl-1.5 pr-3 rounded-full bg-slate-50 hover:bg-violet-50 text-slate-700 transition-colors border border-slate-200">
                                <span class="w-8 h-8 rounded-full bg-gradient-to-br from-violet-600 to-indigo-600 text-white flex items-center justify-center text-xs font-black">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                                <span class="text-sm font-bold hidden lg:inline max-w-[120px] truncate">{{ auth()->user()->name }}</span>
                                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                            </button>
                            <div class="absolute right-0 top-full pt-2 z-50 hidden group-hover:block min-w-[240px]">
                                <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden p-2">
                                    <div class="px-3 py-2 mb-1 border-b border-slate-100">
                                        <div class="text-sm font-black text-slate-900 truncate">{{ auth()->user()->name }}</div>
                                        <div class="text-[11px] text-slate-500 truncate">{{ auth()->user()->email }}</div>
                                    </div>
                                    <a href="{{ route('account.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-violet-50 hover:text-violet-700"><i class="fa-solid fa-gauge-high w-5 text-violet-500"></i> Dashboard</a>
                                    <a href="{{ route('account.orders') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-violet-50 hover:text-violet-700"><i class="fa-solid fa-receipt w-5 text-violet-500"></i> My Orders</a>
                                    <a href="{{ route('account.returns.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-violet-50 hover:text-violet-700"><i class="fa-solid fa-rotate-left w-5 text-violet-500"></i> Returns</a>
                                    <a href="{{ route('account.reviews') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-violet-50 hover:text-violet-700"><i class="fa-solid fa-star w-5 text-violet-500"></i> Reviews</a>
                                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-violet-50 hover:text-violet-700"><i class="fa-solid fa-user-pen w-5 text-violet-500"></i> Profile</a>
                                    <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100 mt-1 pt-1">@csrf
                                        <button class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-semibold text-rose-600 hover:bg-rose-50 text-right"><i class="fa-solid fa-arrow-right-from-bracket w-5"></i> Logout</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endauth

                    {{-- Cart --}}
                    <button type="button" onclick="open_close_cart()" class="relative flex items-center gap-2 h-11 px-3 sm:px-4 rounded-full bg-gradient-to-r from-violet-600 to-indigo-600 text-white hover:from-violet-700 hover:to-indigo-700 transition shadow-md shadow-violet-500/30">
                        <i class="fa-solid fa-cart-shopping text-base"></i>
                        <span class="hidden sm:inline text-sm font-bold">Cart</span>
                        <span id="cart-count" class="absolute -top-1 -right-1 h-5 min-w-[20px] px-1 flex items-center justify-center rounded-full text-[10px] font-bold text-violet-700 bg-amber-300 ring-2 ring-white">0</span>
                    </button>

                    <button type="button" data-mobile-menu-toggle aria-controls="site-mobile-menu" aria-expanded="false" aria-label="Open menu" class="lg:hidden h-11 w-11 flex items-center justify-center rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                        <i data-mobile-menu-icon class="fa-solid fa-bars text-lg"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- ═══ Navigation bar ═══ --}}
        <div class="hidden lg:block border-t border-slate-100 bg-white">
            <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
                <nav class="flex items-center gap-1 h-12">
                    {{-- Categories mega menu --}}
                    @if(($navCategories ?? collect())->isNotEmpty())
                    <div class="relative group" id="colleges-dropdown">
                        <button type="button" id="colleges-dropdown-btn" aria-expanded="false" aria-haspopup="true"
                                class="flex items-center gap-2 pl-3 pr-4 h-9 my-1.5 rounded-full text-sm font-bold text-white bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 transition shadow-sm">
                            <i class="fa-solid fa-bars-staggered text-xs"></i>
                            <span>All Colleges</span>
                            <i id="colleges-dropdown-chevron" class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200"></i>
                        </button>

                        <div id="colleges-dropdown-panel"
                             class="absolute left-0 top-full pt-2 z-50 hidden opacity-0 translate-y-1 pointer-events-none transition-all duration-200 lg:group-hover:block lg:group-hover:opacity-100 lg:group-hover:translate-y-0 lg:group-hover:pointer-events-auto"
                             style="width: min(760px, calc(100vw - 2rem));">
                            <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden">
                                <div class="bg-gradient-to-r from-violet-50 to-indigo-50 px-5 py-3 border-b border-slate-100">
                                    <div class="text-xs font-black uppercase tracking-wider text-violet-700">Browse by college</div>
                                </div>
                                <div class="max-h-[min(70vh,520px)] overflow-y-auto overscroll-contain p-4">
                                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-3">
                                        @foreach($navCategories ?? [] as $college)
                                        <div class="rounded-xl border border-slate-100 p-3 hover:border-violet-300 hover:shadow-md transition {{ $currentCategorySlug === $college->slug ? 'border-violet-300 bg-violet-50/40' : '' }}">
                                            <a href="{{ route('category.show', $college->slug) }}" class="flex items-center gap-3 group/college">
                                                <div class="h-11 w-11 rounded-xl flex items-center justify-center shrink-0 shadow-sm text-white"
                                                     style="background: linear-gradient(135deg, {{ $college->primary_color ?? '#7c3aed' }}, {{ $college->secondary_color ?? '#6366f1' }});">
                                                    @if($college->icon_url)
                                                        <img src="{{ $college->icon_url }}" alt="" class="h-7 w-7 object-contain bg-white/90 rounded-lg p-0.5">
                                                    @else
                                                        <i class="fa-solid fa-graduation-cap"></i>
                                                    @endif
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="text-sm font-black text-slate-800 group-hover/college:text-violet-700 truncate">{{ $college->name }}</div>
                                                    <div class="text-[10px] text-slate-400 font-semibold">{{ $college->children_count }} departments</div>
                                                </div>
                                                <i class="fa-solid fa-arrow-right text-[10px] text-slate-300 group-hover/college:text-violet-600 group-hover/college:translate-x-0.5 transition shrink-0"></i>
                                            </a>

                                            @if($college->children->isNotEmpty())
                                            <div class="flex flex-wrap gap-1 mt-2.5 pl-14">
                                                @foreach($college->children->take(5) as $child)
                                                <a href="{{ route('category.show', $child->slug) }}"
                                                   class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-md border transition max-w-full truncate
                                                          {{ $currentCategorySlug === $child->slug ? 'bg-violet-600 text-white border-violet-600' : 'bg-white text-slate-600 border-slate-200 hover:border-violet-400 hover:text-violet-700' }}">
                                                    {{ $child->name }}
                                                </a>
                                                @endforeach
                                            </div>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <a href="{{ route('home') }}" class="flex items-center gap-1.5 px-4 h-12 text-sm font-bold {{ request()->routeIs('home') ? 'text-violet-700 border-b-2 border-violet-600' : 'text-slate-600 hover:text-violet-700 border-b-2 border-transparent hover:border-violet-300' }} transition">
                        <i class="fa-solid fa-house text-xs"></i> Home
                    </a>
                    <a href="{{ route('products.index') }}" class="flex items-center gap-1.5 px-4 h-12 text-sm font-bold {{ request()->routeIs('products.*') ? 'text-violet-700 border-b-2 border-violet-600' : 'text-slate-600 hover:text-violet-700 border-b-2 border-transparent hover:border-violet-300' }} transition">
                        <i class="fa-solid fa-box-open text-xs"></i> All Products
                    </a>
                    <a href="{{ route('products.index', ['featured' => 1]) }}" class="flex items-center gap-1.5 px-4 h-12 text-sm font-bold text-slate-600 hover:text-violet-700 border-b-2 border-transparent hover:border-violet-300 transition">
                        <i class="fa-solid fa-star text-xs text-amber-500"></i> Featured
                    </a>

                    {{-- Dynamic menu items --}}
                    @foreach($navHeaderMenu ?? collect() as $item)
                        @if($item->children->isNotEmpty())
                            <div class="relative group">
                                <button type="button" class="flex items-center gap-1.5 px-4 h-12 text-sm font-bold text-slate-600 hover:text-violet-700 border-b-2 border-transparent hover:border-violet-300 transition">
                                    @if($item->icon)<i class="fa-solid {{ $item->icon }} text-xs"></i>@endif
                                    <span>{{ $item->title }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200 group-hover:rotate-180"></i>
                                </button>
                                <div class="absolute left-0 top-full pt-2 z-50 hidden opacity-0 translate-y-1 pointer-events-none transition-all duration-200 lg:group-hover:block lg:group-hover:opacity-100 lg:group-hover:translate-y-0 lg:group-hover:pointer-events-auto min-w-[220px]">
                                    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden p-2">
                                        @foreach($item->children as $child)
                                            @if($child->type === 'coupon')
                                                <a href="#" onclick="openWelcomePopup('{{ $child->coupon_code }}', {{ $child->coupon_percent ?? 0 }}); return false;"
                                                   class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:text-violet-700 hover:bg-violet-50 transition">
                                                    <i class="fa-solid fa-gift ml-2 text-rose-400"></i>
                                                    {{ $child->title }}
                                                </a>
                                            @else
                                                <a href="{{ $child->url }}" target="{{ $child->target }}"
                                                   class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:text-violet-700 hover:bg-violet-50 transition">
                                                    @if($child->icon)<i class="fa-solid {{ $child->icon }} ml-2 text-slate-400"></i>@endif
                                                    {{ $child->title }}
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @else
                            @if($item->type === 'coupon')
                                <a href="#" onclick="openWelcomePopup('{{ $item->coupon_code }}', {{ $item->coupon_percent ?? 0 }}); return false;"
                                   class="flex items-center gap-1.5 px-4 h-12 text-sm font-bold text-rose-600 hover:text-rose-700 border-b-2 border-transparent hover:border-rose-400 transition">
                                    <i class="fa-solid fa-gift text-xs"></i>
                                    <span>{{ $item->title }}</span>
                                </a>
                            @else
                                <a href="{{ $item->url }}" target="{{ $item->target }}"
                                   class="flex items-center gap-1.5 px-4 h-12 text-sm font-bold text-slate-600 hover:text-violet-700 border-b-2 border-transparent hover:border-violet-300 transition">
                                    @if($item->icon)<i class="fa-solid {{ $item->icon }} text-xs"></i>@endif
                                    <span>{{ $item->title }}</span>
                                </a>
                            @endif
                        @endif
                    @endforeach

                    <div class="ml-auto flex items-center gap-4 text-xs text-slate-500 font-bold">
                        <span class="inline-flex items-center gap-1.5"><i class="fa-solid fa-truck-fast text-violet-500"></i> Fast delivery</span>
                        <span class="hidden xl:inline-flex items-center gap-1.5"><i class="fa-solid fa-shield-halved text-emerald-500"></i> Original products</span>
                        <span class="hidden xl:inline-flex items-center gap-1.5"><i class="fa-solid fa-rotate-left text-amber-500"></i> Easy returns</span>
                    </div>
                </nav>
            </div>
        </div>
    </div>

    {{-- ═══ Mobile Menu ═══ --}}
    <div id="site-mobile-menu" data-mobile-menu-panel class="lg:hidden hidden bg-white border-b border-slate-200 max-h-[85vh] overflow-y-auto shadow-lg">
        <div class="px-4 py-4 space-y-4">
            <form action="{{ route('products.index') }}" method="get" class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Search products..."
                       class="w-full h-12 pl-10 pr-4 bg-slate-100 rounded-full text-sm outline-none focus:ring-3 focus:ring-violet-100 focus:bg-white">
            </form>

            @if(auth()->guard('admin')->check())
                <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-center gap-2 h-11 px-4 rounded-full text-xs font-bold text-white bg-gradient-to-r from-violet-600 to-indigo-600 hover:opacity-90 transition shadow-md shadow-violet-500/30">
                    <i class="fa-solid fa-user-shield"></i> Admin
                </a>
            @endif

            @guest('web')
                @if(!auth()->guard('admin')->check())
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('login') }}" class="h-11 flex items-center justify-center gap-2 rounded-xl border border-slate-200 text-sm font-bold text-slate-700">
                        <i class="fa-solid fa-right-to-bracket text-xs"></i> Sign in
                    </a>
                    <a href="{{ route('register') }}" class="h-11 flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 text-white text-sm font-bold shadow-md">
                        <i class="fa-solid fa-user-plus text-xs"></i> Register
                    </a>
                </div>
                @endif
            @endguest

            @auth('web')
                <details class="group rounded-2xl border border-slate-100 bg-white overflow-hidden">
                    <summary class="flex items-center gap-3 px-3 py-3 cursor-pointer list-none hover:bg-slate-50">
                        <span class="w-10 h-10 rounded-full bg-gradient-to-br from-violet-600 to-indigo-600 text-white flex items-center justify-center text-sm font-black shrink-0">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-black text-slate-900 truncate">{{ auth()->user()->name }}</div>
                            <div class="text-[11px] text-slate-500 truncate">{{ auth()->user()->email }}</div>
                        </div>
                        <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform group-open:rotate-180"></i>
                    </summary>
                    <div class="p-2 space-y-1 border-t border-slate-100 bg-slate-50/50">
                        <a href="{{ route('account.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-white hover:text-violet-700"><i class="fa-solid fa-gauge-high w-5 text-violet-500"></i> Dashboard</a>
                        <a href="{{ route('account.orders') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-white hover:text-violet-700"><i class="fa-solid fa-receipt w-5 text-violet-500"></i> My Orders</a>
                        <a href="{{ route('account.returns.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-white hover:text-violet-700"><i class="fa-solid fa-rotate-left w-5 text-violet-500"></i> Returns</a>
                        <a href="{{ route('account.reviews') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-white hover:text-violet-700"><i class="fa-solid fa-star w-5 text-violet-500"></i> Reviews</a>
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-white hover:text-violet-700"><i class="fa-solid fa-user-pen w-5 text-violet-500"></i> Profile</a>
                        <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100 mt-1 pt-1">@csrf
                            <button class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-semibold text-rose-600 hover:bg-rose-50 text-right"><i class="fa-solid fa-arrow-right-from-bracket w-5"></i> Logout</button>
                        </form>
                    </div>
                </details>
            @endauth

            <nav class="space-y-1">
                <a href="{{ route('home') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-50 font-semibold text-slate-700">
                    <i class="fa-solid fa-house w-5 text-violet-600"></i> Home
                </a>
                <a href="{{ route('products.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-50 font-semibold text-slate-700">
                    <i class="fa-solid fa-box-open w-5 text-violet-600"></i> All Products
                </a>
                <a href="{{ route('products.index', ['featured' => 1]) }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-50 font-semibold text-slate-700">
                    <i class="fa-solid fa-star w-5 text-amber-500"></i> Featured
                </a>

                @foreach($navHeaderMenu ?? collect() as $item)
                    @if($item->children->isNotEmpty())
                        <details class="group rounded-xl border border-slate-100 overflow-hidden">
                            <summary class="flex items-center gap-3 px-4 py-3 cursor-pointer list-none hover:bg-slate-50 font-semibold text-slate-700">
                                @if($item->icon)<i class="fa-solid {{ $item->icon }} w-5 text-violet-600"></i>@endif
                                <span class="flex-1">{{ $item->title }}</span>
                                <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform group-open:rotate-180"></i>
                            </summary>
                            <div class="px-4 pb-3 space-y-1 border-t border-slate-100 bg-slate-50/50">
                                @foreach($item->children as $child)
                                    @if($child->type === 'coupon')
                                    <a href="#" onclick="openWelcomePopup('{{ $child->coupon_code }}', {{ $child->coupon_percent ?? 0 }}); return false;" class="block px-3 py-2 text-sm rounded-lg hover:bg-white text-slate-600">
                                        <i class="fa-solid fa-gift ml-1 text-rose-400"></i> {{ $child->title }}
                                    </a>
                                    @else
                                    <a href="{{ $child->url }}" target="{{ $child->target }}" class="block px-3 py-2 text-sm rounded-lg hover:bg-white text-slate-600">
                                        {{ $child->title }}
                                    </a>
                                    @endif
                                @endforeach
                            </div>
                        </details>
                    @else
                        @if($item->type === 'coupon')
                            <a href="#" onclick="openWelcomePopup('{{ $item->coupon_code }}', {{ $item->coupon_percent ?? 0 }}); return false;" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-50 font-semibold text-slate-700">
                                <i class="fa-solid fa-gift w-5 text-rose-500"></i>
                                {{ $item->title }}
                            </a>
                        @else
                            <a href="{{ $item->url }}" target="{{ $item->target }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-50 font-semibold text-slate-700">
                                @if($item->icon)<i class="fa-solid {{ $item->icon }} w-5 text-violet-600"></i>@endif
                                {{ $item->title }}
                            </a>
                        @endif
                    @endif
                @endforeach

                @if(($navCategories ?? collect())->isNotEmpty())
                <div class="pt-2 border-t border-slate-100">
                    <p class="text-xs font-black text-slate-400 uppercase tracking-wider px-4 mb-2">Colleges</p>
                    @foreach($navCategories ?? [] as $college)
                    <a href="{{ route('category.show', $college->slug) }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-50 font-semibold text-slate-700">
                        <div class="h-9 w-9 rounded-xl flex items-center justify-center text-white text-[10px] font-black" style="background: linear-gradient(135deg, {{ $college->primary_color ?? '#7c3aed' }}, {{ $college->secondary_color ?? '#6366f1' }});">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                        <span class="flex-1">{{ $college->name }}</span>
                        <span class="text-[10px] text-slate-400">{{ $college->children_count }}</span>
                    </a>
                    @endforeach
                </div>
                @endif
            </nav>
        </div>
    </div>
</header>
