<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $authTitle = $authTitle ?? ($pageTitle ?? config('app.name', 'UNI-LAB MARKET'));
        $siteName  = site_setting('site_name', config('app.name', 'UNI-LAB MARKET'));
        $siteLogo  = site_setting_url('site_logo');
    @endphp

    <title>{{ $authTitle }} — {{ $siteName }}</title>
    <meta name="description" content="{{ $authTitle }} — {{ $siteName }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        html, body { font-family: 'Inter', system-ui, -apple-system, "Segoe UI", sans-serif; }
        body { background: #f8fafc; color: #0f172a; }
        .auth-input {
            width: 100%;
            height: 48px;
            padding: 0 44px 0 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            font-size: 14px;
            color: #0f172a;
            transition: all .2s ease;
        }
        .auth-input:focus {
            outline: none;
            border-color: #2563eb;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(37,99,235,.10);
        }
        .auth-input.has-error { border-color: #f43f5e; background: #fff1f2; }
        .auth-label { display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:8px; }
        .auth-icon { position:absolute; top:50%; right:16px; transform:translateY(-50%); color:#94a3b8; pointer-events:none; }
        .auth-btn-primary {
            display:inline-flex; align-items:center; justify-content:center; gap:10px;
            width:100%; height:48px; border-radius:14px; font-weight:700; font-size:14px;
            color:#fff; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            box-shadow: 0 10px 25px -10px rgba(37,99,235,.55);
            transition: transform .15s ease, box-shadow .2s ease, opacity .2s ease;
        }
        .auth-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 14px 28px -10px rgba(37,99,235,.65); }
        .auth-btn-primary:disabled { opacity:.6; cursor:not-allowed; }
        .auth-error { color:#e11d48; font-size:12px; font-weight:600; margin-top:6px; }
        .toggle-pwd { position:absolute; top:50%; left:14px; transform:translateY(-50%); color:#94a3b8; cursor:pointer; padding:6px; }
        .toggle-pwd:hover { color:#2563eb; }

        /* Brand side decorative */
        .brand-side {
            background:
              radial-gradient(1200px 600px at 80% -10%, rgba(255,255,255,.18), transparent 60%),
              radial-gradient(900px 500px at -10% 110%, rgba(255,255,255,.12), transparent 60%),
              linear-gradient(135deg, #1e40af 0%, #2563eb 45%, #1d4ed8 100%);
        }
        .brand-blob {
            position:absolute; border-radius:9999px; filter: blur(60px); opacity:.45;
            background: radial-gradient(circle, #60a5fa 0%, transparent 70%);
        }
        .brand-grid {
            background-image:
              linear-gradient(rgba(255,255,255,.06) 1px, transparent 1px),
              linear-gradient(90deg, rgba(255,255,255,.06) 1px, transparent 1px);
            background-size: 32px 32px;
        }
        @keyframes floatY { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }
        .float-y { animation: floatY 6s ease-in-out infinite; }
    </style>

    @stack('styles')
</head>
<body class="antialiased">

<div class="min-h-screen grid lg:grid-cols-2">

    {{-- ─────────────── Brand Side ─────────────── --}}
    <aside class="brand-side relative hidden lg:flex flex-col justify-between p-12 text-white overflow-hidden">
        <div class="absolute inset-0 brand-grid opacity-50"></div>
        <div class="brand-blob" style="width:380px; height:380px; top:-80px; right:-80px;"></div>
        <div class="brand-blob" style="width:320px; height:320px; bottom:-60px; left:-60px; background: radial-gradient(circle,#a78bfa 0%, transparent 70%);"></div>

        <a href="{{ url('/') }}" class="relative z-10 flex items-center gap-3">
            @if($siteLogo)
                <img src="{{ $siteLogo }}" alt="{{ $siteName }}" class="h-12 w-auto object-contain bg-white/10 backdrop-blur rounded-2xl p-2 border border-white/20">
            @else
                <div class="h-12 w-12 rounded-2xl bg-white/15 border border-white/20 backdrop-blur flex items-center justify-center">
                    <i class="fa-solid fa-flask text-xl"></i>
                </div>
            @endif
            <div>
                <p class="font-extrabold text-lg leading-tight">{{ $siteName }}</p>
                <p class="text-xs text-white/70 mt-0.5">منصة المعدات والمستلزمات العلمية</p>
            </div>
        </a>

        <div class="relative z-10 max-w-md float-y">
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/20 text-[11px] font-bold backdrop-blur">
                <i class="fa-solid fa-sparkles text-amber-300"></i> أهلاً بك من جديد
            </span>
            <h1 class="text-4xl xl:text-5xl font-extrabold leading-tight mt-5">
                كل ما تحتاجه <br>
                <span class="text-amber-300">لمعملك العلمي</span>
                <br> في مكان واحد
            </h1>
            <p class="text-white/80 mt-5 leading-relaxed">
                سجّل دخولك للوصول إلى طلباتك، عناوين الشحن، قوائم المفضلة، ومتابعة حالة شحناتك لحظة بلحظة.
            </p>

            <ul class="mt-8 space-y-3 text-sm">
                <li class="flex items-center gap-3">
                    <span class="h-9 w-9 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-amber-300"><i class="fa-solid fa-truck-fast"></i></span>
                    شحن سريع لجميع المحافظات
                </li>
                <li class="flex items-center gap-3">
                    <span class="h-9 w-9 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-emerald-300"><i class="fa-solid fa-shield-halved"></i></span>
                    دفع آمن عبر بوابات معتمدة
                </li>
                <li class="flex items-center gap-3">
                    <span class="h-9 w-9 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-sky-300"><i class="fa-solid fa-headset"></i></span>
                    دعم فني على مدار الساعة
                </li>
            </ul>
        </div>

        <p class="relative z-10 text-xs text-white/60">© {{ date('Y') }} {{ $siteName }} — جميع الحقوق محفوظة.</p>
    </aside>

    {{-- ─────────────── Form Side ─────────────── --}}
    <main class="flex flex-col justify-center px-5 sm:px-10 lg:px-16 py-10">
        <div class="w-full max-w-md mx-auto">

            {{-- Mobile logo --}}
            <a href="{{ url('/') }}" class="lg:hidden flex items-center gap-3 mb-8">
                @if($siteLogo)
                    <img src="{{ $siteLogo }}" alt="{{ $siteName }}" class="h-11 w-auto object-contain">
                @else
                    <div class="h-11 w-11 rounded-2xl bg-blue-600 text-white flex items-center justify-center"><i class="fa-solid fa-flask"></i></div>
                @endif
                <span class="font-extrabold text-slate-900">{{ $siteName }}</span>
            </a>

            {{ $slot }}

            <p class="text-center text-xs text-slate-400 mt-10">
                بالمتابعة فأنت توافق على
                <a href="{{ url('/') }}" class="text-blue-600 hover:underline">الشروط والأحكام</a>
                و
                <a href="{{ url('/') }}" class="text-blue-600 hover:underline">سياسة الخصوصية</a>.
            </p>
        </div>
    </main>
</div>

<script src="{{ asset('js/ajax.js') }}?v={{ @filemtime(public_path('js/ajax.js')) ?: time() }}"></script>
<script>
    // Password visibility toggle
    document.querySelectorAll('[data-toggle-password]').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = document.querySelector(btn.dataset.togglePassword);
            if (!target) return;
            const isPwd = target.type === 'password';
            target.type = isPwd ? 'text' : 'password';
            btn.querySelector('i').className = isPwd ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
        });
    });
</script>
@stack('scripts')
</body>
</html>
