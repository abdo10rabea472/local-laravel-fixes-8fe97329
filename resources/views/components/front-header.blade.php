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
    $primaryColor = site_setting('primary_color', '#6366f1');
@endphp

<header class="sticky top-0 z-50 w-full bg-white shadow-sm">
    {{-- Top utility bar --}}
    <div class="bg-slate-900 text-white text-[11px] sm:text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex flex-col sm:flex-row items-center justify-between gap-1">
            <div class="flex items-center gap-4">
                @if(site_setting('contact_phone'))
                <a href="tel:{{ site_setting('contact_phone') }}" class="hover:text-amber-400 transition-colors flex items-center gap-1.5">
                    <i class="fa-solid fa-phone text-[10px]"></i>
                    <span>{{ site_setting('contact_phone') }}</span>
                </a>
                @endif
                @if(site_setting('contact_email'))
                <a href="mailto:{{ site_setting('contact_email') }}" class="hover:text-amber-400 transition-colors hidden sm:flex items-center gap-1.5">
                    <i class="fa-solid fa-envelope text-[10px]"></i>
                    <span>{{ site_setting('contact_email') }}</span>
                </a>
                @endif
            </div>
            @if(site_setting('free_shipping_enabled', '1') === '1' && site_setting('free_shipping_show_in_header', '1') === '1')
            <p class="font-semibold text-amber-400 text-center">
                Free shipping on orders over {{ number_format((float) site_setting('free_shipping_threshold', 2000), 0) }} EGP
            </p>
            @endif

            <div class="flex items-center gap-4">
                @if(($navTopMenu ?? collect())->isNotEmpty())
                    @foreach($navTopMenu as $item)
                        @if($item->type === 'coupon')
                            <a href="#" onclick="openWelcomePopup('{{ $item->coupon_code }}', {{ $item->coupon_percent ?? 0 }}); return false;" class="hover:text-amber-400 transition-colors">{{ $item->title }}</a>
                        @else
                            <a href="{{ $item->url }}" target="{{ $item->target }}" class="hover:text-amber-400 transition-colors">{{ $item->title }}</a>
                        @endif
                    @endforeach
                @else
                    @guest('web')
                        @if(!auth()->guard('admin')->check())
                        <a href="{{ route('login') }}" class="hover:text-amber-400 transition-colors">Login</a>
                        <span class="text-slate-600">|</span>
                        <a href="{{ route('register') }}" class="hover:text-amber-400 transition-colors">Register</a>
                        @endif
                    @endguest
                    @auth('web')
                        <a href="{{ route('profile.edit') }}" class="hover:text-amber-400 transition-colors">My Account</a>
                    @endauth
                @endif
            </div>
        </div>
    </div>

    {{-- Main header --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex h-20 items-center justify-between gap-4 lg:gap-8">
            {{-- Logo --}}
            <a href="{{ route('home') }}" class="shrink-0 flex items-center gap-2">
                @if(site_setting_url('site_logo'))
                    <img src="{{ site_setting_url('site_logo') }}" alt="UNI-LAB MARKET" class="h-10 w-auto object-contain">
                @else
                    <span class="text-xl font-black" style="color: {{ $primaryColor }}">UNI-LAB</span>
                @endif
            </a>

            {{-- Search --}}
            <div class="hidden md:flex flex-1 max-w-2xl">
                <form action="{{ route('products.index') }}" method="get" class="relative w-full group">
                    <input type="search" name="search" value="{{ request('search') }}"
                           class="w-full h-11 pl-12 pr-4 bg-slate-100 border border-transparent focus:border-violet-300 focus:bg-white rounded-full text-sm outline-none transition-all"
                           placeholder="Search for lab tools, medical equipment, college supplies...">
                    <button type="submit" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-violet-600 transition-colors">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </form>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2 sm:gap-4 shrink-0">
                @if(auth()->guard('admin')->check())
                    <a href="{{ route('admin.dashboard') }}" class="hidden lg:inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-bold text-white" style="background: {{ $primaryColor }}">
                        <i class="fa-solid fa-user-shield"></i> Admin
                    </a>
                @endif

                <button type="button" onclick="open_close_cart()" class="relative flex items-center gap-2 h-11 px-3 sm:px-4 rounded-full bg-slate-50 hover:bg-slate-100 text-slate-700 transition-colors">
                    <i class="fa-solid fa-cart-shopping text-lg"></i>
                    <span class="hidden sm:inline text-sm font-bold">Cart</span>
                    <span id="cart-count" class="absolute -top-1 -right-1 h-5 w-5 flex items-center justify-center rounded-full text-[10px] font-bold text-white" style="background: {{ $primaryColor }}">0</span>
                </button>

                <button type="button" id="mobile-menu-btn" class="lg:hidden h-11 w-11 flex items-center justify-center rounded-full bg-slate-50 hover:bg-slate-100 text-slate-700 transition-colors">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Navigation bar --}}
    <div class="hidden lg:block border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-1 h-12">
                {{-- Categories mega menu trigger --}}
                @if(($navCategories ?? collect())->isNotEmpty())
                <div class="relative group" id="colleges-dropdown">
                    <button type="button" id="colleges-dropdown-btn" aria-expanded="false" aria-haspopup="true"
                            class="flex items-center gap-2 px-4 h-12 text-sm font-bold text-slate-700 hover:text-violet-700 transition-colors border-b-2 border-transparent group-hover:border-violet-600">
                        <i class="fa-solid fa-bars-staggered"></i>
                        <span>All Categories</span>
                        <i id="colleges-dropdown-chevron" class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200"></i>
                    </button>

                    <div id="colleges-dropdown-panel"
                         class="absolute left-0 top-full pt-2 z-50 hidden opacity-0 translate-y-1 pointer-events-none transition-all duration-200 lg:group-hover:block lg:group-hover:opacity-100 lg:group-hover:translate-y-0 lg:group-hover:pointer-events-auto"
                         style="width: min(720px, calc(100vw - 2rem)); max-width: calc(100vw - 1rem);">
                        <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden">
                            <div class="max-h-[min(70vh,520px)] overflow-y-auto overscroll-contain p-5">
                                <div class="grid grid-cols-1 xl:grid-cols-2 gap-x-4 gap-y-3">
                                    @foreach($navCategories ?? [] as $college)
                                    <div class="rounded-xl border border-slate-100 p-3 hover:border-violet-100 hover:bg-slate-50/50 transition-colors {{ $currentCategorySlug === $college->slug ? 'border-violet-200 bg-violet-50/40' : '' }}">
                                        <a href="{{ route('category.show', $college->slug) }}" class="flex items-center gap-2.5 group/college">
                                            <div class="h-10 w-10 rounded-xl flex items-center justify-center shrink-0 shadow-sm"
                                                 style="background: linear-gradient(135deg, {{ $college->primary_color ?? '#6366f1' }}, {{ $college->secondary_color ?? '#8b5cf6' }});">
                                                @if($college->icon_url)
                                                    <img src="{{ $college->icon_url }}" alt="" class="h-7 w-7 object-contain bg-white/90 rounded-lg p-0.5">
                                                @else
                                                    <span class="text-white text-[10px] font-black">{{ strtoupper(substr($college->name, 0, 2)) }}</span>
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-sm font-bold text-slate-800 group-hover/college:text-violet-700 truncate">{{ $college->name }}</div>
                                                <div class="text-[10px] text-slate-400">{{ $college->children_count }} departments</div>
                                            </div>
                                            <i class="fa-solid fa-arrow-right text-[10px] text-slate-300 group-hover/college:text-violet-500 shrink-0"></i>
                                        </a>

                                        @if($college->children->isNotEmpty())
                                        <div class="flex flex-wrap gap-1 mt-2 pl-12">
                                            @foreach($college->children->take(5) as $child)
                                            <a href="{{ route('category.show', $child->slug) }}"
                                               class="inline-flex items-center text-[10px] font-semibold px-2 py-0.5 rounded-md border transition-colors max-w-full truncate
                                                      {{ $currentCategorySlug === $child->slug ? 'bg-violet-600 text-white border-violet-600' : 'bg-white text-slate-600 border-slate-200 hover:border-violet-300 hover:text-violet-700' }}">
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

                {{-- Dynamic menu items --}}
                @foreach($navHeaderMenu ?? collect() as $item)
                    @if($item->children->isNotEmpty())
                        <div class="relative group">
                            <button type="button" class="flex items-center gap-1.5 px-4 h-12 text-sm font-semibold text-slate-600 hover:text-violet-700 transition-colors border-b-2 border-transparent group-hover:border-violet-600">
                                @if($item->icon)<i class="fa-solid {{ $item->icon }} text-xs"></i>@endif
                                <span>{{ $item->title }}</span>
                                <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200 group-hover:rotate-180"></i>
                            </button>
                            <div class="absolute left-0 top-full pt-2 z-50 hidden opacity-0 translate-y-1 pointer-events-none transition-all duration-200 lg:group-hover:block lg:group-hover:opacity-100 lg:group-hover:translate-y-0 lg:group-hover:pointer-events-auto min-w-[200px]">
                                <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden p-2">
                                    @foreach($item->children as $child)
                                        @if($child->type === 'coupon')
                                            <a href="#" onclick="openWelcomePopup('{{ $child->coupon_code }}', {{ $child->coupon_percent ?? 0 }}); return false;"
                                               class="block px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:text-violet-700 hover:bg-violet-50 transition-colors">
                                                <i class="fa-solid fa-gift ml-2 text-rose-400"></i>
                                                {{ $child->title }}
                                            </a>
                                        @else
                                            <a href="{{ $child->url }}" target="{{ $child->target }}"
                                               class="block px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:text-violet-700 hover:bg-violet-50 transition-colors">
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
                               class="flex items-center gap-1.5 px-4 h-12 text-sm font-semibold text-slate-600 hover:text-violet-700 transition-colors border-b-2 border-transparent hover:border-violet-600">
                                <i class="fa-solid fa-gift text-xs text-rose-500"></i>
                                <span>{{ $item->title }}</span>
                            </a>
                        @else
                            <a href="{{ $item->url }}" target="{{ $item->target }}"
                               class="flex items-center gap-1.5 px-4 h-12 text-sm font-semibold text-slate-600 hover:text-violet-700 transition-colors border-b-2 border-transparent hover:border-violet-600">
                                @if($item->icon)<i class="fa-solid {{ $item->icon }} text-xs"></i>@endif
                                <span>{{ $item->title }}</span>
                            </a>
                        @endif
                    @endif
                @endforeach
            </nav>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div id="mobile-menu" class="lg:hidden hidden border-t border-slate-100 bg-white max-h-[85vh] overflow-y-auto">
        <div class="px-4 py-4 space-y-4">
            <form action="{{ route('products.index') }}" method="get" class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Search products..."
                       class="w-full h-11 pl-10 pr-4 bg-slate-100 rounded-full text-sm outline-none">
            </form>

            <nav class="space-y-1">
                <a href="{{ route('home') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-50 font-semibold text-slate-700">
                    <i class="fa-solid fa-house w-5 text-violet-600"></i> Home
                </a>
                <a href="{{ route('products.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-50 font-semibold text-slate-700">
                    <i class="fa-solid fa-box w-5 text-violet-600"></i> All Products
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
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider px-4 mb-2">Categories</p>
                    @foreach($navCategories ?? [] as $college)
                    <a href="{{ route('category.show', $college->slug) }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-50 font-semibold text-slate-700">
                        <div class="h-8 w-8 rounded-lg flex items-center justify-center text-white text-[10px] font-black" style="background: linear-gradient(135deg, {{ $college->primary_color ?? '#6366f1' }}, {{ $college->secondary_color ?? '#8b5cf6' }});">
                            {{ strtoupper(substr($college->name, 0, 2)) }}
                        </div>
                        {{ $college->name }}
                    </a>
                    @endforeach
                </div>
                @endif
            </nav>
        </div>
    </div>
</header>
