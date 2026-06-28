{{-- ═══ Pre-footer feature strip ═══ --}}
<section class="bg-gradient-to-r from-violet-700 via-indigo-700 to-violet-800 text-white">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6 grid grid-cols-2 lg:grid-cols-4 gap-5">
        @php
            $footerFeatures = [
                ['fa-truck-fast', 'Nationwide delivery', 'To every Egyptian governorate'],
                ['fa-shield-halved', 'Certified equipment', 'Original lab & medical tools'],
                ['fa-rotate-left', '14-day returns', 'No questions asked'],
                ['fa-headset', 'Student support', 'Saturday — Thursday, 9am — 9pm'],
            ];
        @endphp
        @foreach($footerFeatures as [$ic, $t, $s])
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 shrink-0 rounded-xl bg-white/15 backdrop-blur border border-white/20 grid place-items-center text-lg">
                <i class="fa-solid {{ $ic }}"></i>
            </div>
            <div class="min-w-0">
                <div class="font-black text-sm truncate">{{ $t }}</div>
                <div class="text-[11px] text-violet-100 truncate">{{ $s }}</div>
            </div>
        </div>
        @endforeach
    </div>
</section>

<footer class="relative bg-slate-950 text-slate-400 pt-16 pb-6 overflow-hidden">
    {{-- Decorative glows --}}
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-violet-600/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-10">
        {{-- Brand --}}
        <div class="lg:col-span-4 space-y-5">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                @if(site_setting_url('site_logo'))
                    <img src="{{ site_setting_url('site_logo') }}" alt="UNI-LAB MARKET" class="h-11 w-auto object-contain">
                @else
                    <span class="w-11 h-11 rounded-2xl bg-gradient-to-br from-violet-600 to-indigo-600 text-white grid place-items-center text-lg font-black shadow-lg shadow-violet-500/30">
                        <i class="fa-solid fa-flask-vial"></i>
                    </span>
                    <span class="flex flex-col leading-none">
                        <span class="text-lg font-black text-white tracking-tight">UNI-LAB</span>
                        <span class="text-[9px] font-bold text-violet-400 tracking-[0.2em] uppercase">Market</span>
                    </span>
                @endif
            </a>
            <p class="text-sm leading-relaxed max-w-sm">
                Professional lab tools and educational equipment trusted by university students and faculties across Egypt.
            </p>

            <div class="space-y-2.5 text-sm">
                <p class="flex items-start gap-3"><i class="fa-solid fa-envelope text-violet-400 w-4 mt-0.5"></i> <a href="mailto:{{ site_setting('contact_email', 'ahmedkhamis@gmail.com') }}" class="hover:text-white transition">{{ site_setting('contact_email', 'ahmedkhamis@gmail.com') }}</a></p>
                <p class="flex items-start gap-3"><i class="fa-solid fa-phone text-violet-400 w-4 mt-0.5"></i> <a href="tel:{{ site_setting('contact_phone', '01007970340') }}" class="hover:text-white transition">{{ site_setting('contact_phone', '01007970340') }}</a></p>
                <p class="flex items-start gap-3"><i class="fa-solid fa-location-dot text-violet-400 w-4 mt-0.5"></i> {{ site_setting('contact_address', 'El Minya, Egypt') }}</p>
            </div>

            <div class="flex gap-2 pt-2">
                @php $socials = [['facebook-f','#1877f2'],['instagram','#e1306c'],['linkedin-in','#0a66c2'],['whatsapp','#25d366'],['twitter','#1da1f2']]; @endphp
                @foreach($socials as [$ic, $c])
                <a href="#" aria-label="{{ $ic }}" class="h-9 w-9 rounded-xl bg-slate-900 border border-slate-800 grid place-items-center hover:bg-violet-600 hover:border-violet-600 hover:text-white transition text-sm">
                    <i class="fab fa-{{ $ic }}"></i>
                </a>
                @endforeach
            </div>
        </div>

        {{-- Colleges --}}
        <div class="lg:col-span-3">
            <h5 class="text-white font-black text-sm mb-4 uppercase tracking-wider"><span class="inline-block w-1 h-4 bg-violet-500 rounded-full align-middle mr-2"></span>Colleges</h5>
            <ul class="space-y-2 text-sm">
                @foreach(($navCategories ?? collect())->take(8) as $college)
                <li>
                    <a href="{{ route('category.show', $college->slug) }}" class="hover:text-violet-400 transition inline-flex items-center gap-2 group">
                        <i class="fa-solid fa-chevron-right text-[8px] text-slate-600 group-hover:text-violet-400 group-hover:translate-x-0.5 transition"></i>
                        {{ $college->name }}
                    </a>
                </li>
                @endforeach
                @if(($navCategories ?? collect())->isEmpty())
                <li><a href="{{ route('products.index') }}" class="hover:text-violet-400 transition">All categories</a></li>
                @endif
            </ul>
        </div>

        {{-- Quick links --}}
        <div class="lg:col-span-2">
            <h5 class="text-white font-black text-sm mb-4 uppercase tracking-wider"><span class="inline-block w-1 h-4 bg-violet-500 rounded-full align-middle mr-2"></span>Shop</h5>
            <ul class="space-y-2 text-sm">
                @if(($navFooterMenu ?? collect())->isNotEmpty())
                    @foreach($navFooterMenu as $item)
                        @if($item->type === 'coupon')
                            <li><a href="#" onclick="openWelcomePopup('{{ $item->coupon_code }}', {{ $item->coupon_percent ?? 0 }}); return false;" class="hover:text-violet-400 transition">{{ $item->title }}</a></li>
                        @else
                            <li><a href="{{ $item->url }}" target="{{ $item->target }}" class="hover:text-violet-400 transition">{{ $item->title }}</a></li>
                        @endif
                    @endforeach
                @else
                    <li><a href="{{ route('products.index') }}" class="hover:text-violet-400 transition">All Products</a></li>
                    <li><a href="{{ route('products.index', ['featured' => 1]) }}" class="hover:text-violet-400 transition">Featured</a></li>
                    <li><a href="{{ route('products.index', ['sort' => 'newest']) }}" class="hover:text-violet-400 transition">New Arrivals</a></li>
                    <li><a href="{{ url('/') }}#colleges" class="hover:text-violet-400 transition">By College</a></li>
                @endif
            </ul>
        </div>

        {{-- Account / Help --}}
        <div class="lg:col-span-3">
            <h5 class="text-white font-black text-sm mb-4 uppercase tracking-wider"><span class="inline-block w-1 h-4 bg-violet-500 rounded-full align-middle mr-2"></span>Help & Account</h5>
            <ul class="space-y-2 text-sm">
                <li><a href="{{ route('about') }}" class="hover:text-violet-400 transition">About Us</a></li>
                <li><a href="{{ route('contact') }}" class="hover:text-violet-400 transition">Contact Us</a></li>
                <li><a href="{{ route('blog.index') }}" class="hover:text-violet-400 transition">Blog</a></li>
                <li><a href="{{ route('offers') }}" class="hover:text-violet-400 transition">Offers</a></li>
                <li><a href="{{ route('track-order') }}" class="hover:text-violet-400 transition">Track Order</a></li>
                <li><a href="{{ route('compare.index') }}" class="hover:text-violet-400 transition">Compare Products</a></li>
                <li><a href="{{ route('pages.faqs') }}" class="hover:text-violet-400 transition">FAQs</a></li>
                <li><a href="{{ route('pages.returns') }}" class="hover:text-violet-400 transition">Returns & Refunds</a></li>
                <li><a href="{{ route('pages.privacy') }}" class="hover:text-violet-400 transition">Privacy Policy</a></li>
                @auth('web')
                    <li><a href="{{ route('account.dashboard') }}" class="hover:text-violet-400 transition">My Dashboard</a></li>
                    <li><a href="{{ route('wishlist.index') }}" class="hover:text-violet-400 transition">My Wishlist</a></li>
                @else
                    <li><a href="{{ route('login') }}" class="hover:text-violet-400 transition">Sign in</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-violet-400 transition">Create account</a></li>
                @endauth
            </ul>

            {{-- Mini newsletter --}}
            <div class="mt-6">
                <p class="text-xs font-bold text-white mb-2">Get semester deals</p>
                @if(session('success') && url()->previous() === route('newsletter.subscribe'))<p class="text-xs text-emerald-400 mb-2">{{ session('success') }}</p>@endif
                <form action="{{ route('newsletter.subscribe') }}" method="post" class="flex gap-2">
                    @csrf
                    <input type="email" name="email" required placeholder="your@email.com" class="flex-1 h-10 px-3 bg-slate-900 border border-slate-800 rounded-lg text-xs text-white placeholder:text-slate-600 outline-none focus:border-violet-500">
                    <button type="submit" class="h-10 px-4 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white text-xs font-bold rounded-lg transition shadow-md shadow-violet-500/30">
                        Join
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Bottom bar --}}
    <div class="relative max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 mt-12 pt-6 border-t border-slate-800/80">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-xs">
            <p class="text-slate-500">
                © {{ date('Y') }} <span class="text-slate-300 font-bold">UNI-LAB MARKET</span>. All rights reserved.
            </p>
            <div class="flex items-center gap-2 flex-wrap justify-center">
                <span class="text-slate-600 font-bold uppercase tracking-wider text-[10px]">We accept</span>
                @foreach(['fa-cc-visa','fa-cc-mastercard','fa-cc-paypal','fa-money-bill-wave'] as $pm)
                <span class="h-8 px-2.5 rounded-md bg-slate-900 border border-slate-800 grid place-items-center text-slate-400 text-base">
                    <i class="fa-brands {{ $pm }} fa-solid"></i>
                </span>
                @endforeach
            </div>
        </div>
    </div>
</footer>
