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

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --brand: #2563eb;
            --brand-dark: #1d4ed8;
            --ink: #0f172a;
            --muted: #64748b;
            --line: #e5e7eb;
            --bg: #ffffff;
            --soft: #f8fafc;
        }
        * { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
        html, body {
            font-family: 'IBM Plex Sans Arabic', 'Inter', system-ui, -apple-system, "Segoe UI", sans-serif;
            background: var(--bg);
            color: var(--ink);
        }

        .field { position: relative; }
        .field-input {
            width: 100%;
            height: 46px;
            padding: 0 14px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 10px;
            font-size: 14px;
            color: var(--ink);
            transition: border-color .15s ease, box-shadow .15s ease;
            font-family: inherit;
        }
        .field-input::placeholder { color: #cbd5e1; }
        .field-input:focus {
            outline: none;
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
        }
        .field-input.has-error { border-color: #ef4444; }
        .field-input.has-error:focus { box-shadow: 0 0 0 3px rgba(239, 68, 68, .12); }
        .field-input.has-toggle { padding-left: 44px; }
        .field-label {
            display:block;
            font-size:13px;
            font-weight:600;
            color:#334155;
            margin-bottom:6px;
        }
        .field-error { color:#dc2626; font-size:12px; margin-top:6px; font-weight:500; }
        .field-toggle {
            position:absolute; top:50%; left:10px; transform:translateY(-50%);
            color:#94a3b8; cursor:pointer;
            width:30px; height:30px; display:flex; align-items:center; justify-content:center;
            border-radius:8px; transition: color .15s ease, background .15s ease;
            background: transparent; border: 0;
        }
        .field-toggle:hover { color: var(--brand); background: #f1f5f9; }

        .btn-primary {
            display:inline-flex; align-items:center; justify-content:center; gap:8px;
            width:100%; height:46px;
            background: var(--brand);
            color:#fff; font-weight:600; font-size:14px;
            border-radius: 10px;
            border: 0;
            cursor: pointer;
            transition: background .15s ease;
            font-family: inherit;
        }
        .btn-primary:hover { background: var(--brand-dark); }
        .btn-primary:disabled { opacity:.6; cursor:not-allowed; }

        .btn-ghost {
            display:inline-flex; align-items:center; justify-content:center; gap:8px;
            width:100%; height:46px;
            background: #fff;
            color: var(--ink); font-weight:600; font-size:14px;
            border-radius: 10px;
            border: 1px solid var(--line);
            cursor: pointer;
            transition: all .15s ease;
            font-family: inherit;
        }
        .btn-ghost:hover { border-color: #cbd5e1; background: var(--soft); }

        .link { color: var(--brand); font-weight:600; }
        .link:hover { color: var(--brand-dark); text-decoration: underline; text-underline-offset: 3px; }

        .checkbox {
            appearance: none; -webkit-appearance:none;
            width:18px; height:18px;
            border: 1.5px solid #cbd5e1;
            border-radius: 5px;
            background: #fff;
            cursor: pointer;
            display:inline-grid; place-content:center;
            transition: all .15s ease;
        }
        .checkbox:checked { background: var(--brand); border-color: var(--brand); }
        .checkbox:checked::after {
            content: '';
            width: 10px; height: 10px;
            background: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='white'><path d='M13.485 4.515a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L6.778 9.808l5.293-5.293a1 1 0 011.414 0z'/></svg>") center/contain no-repeat;
        }

        .divider {
            display:flex; align-items:center; gap:12px;
            color: #94a3b8; font-size: 12px; font-weight: 500;
            margin: 4px 0;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1; height: 1px; background: var(--line);
        }

        /* Brand side */
        .brand-panel {
            background: linear-gradient(165deg, #1e3a8a 0%, #1e40af 50%, #2563eb 100%);
            position: relative;
            overflow: hidden;
        }
        .brand-panel::before {
            content: '';
            position: absolute; inset: 0;
            background-image:
              radial-gradient(circle at 20% 20%, rgba(96, 165, 250, .25), transparent 50%),
              radial-gradient(circle at 80% 80%, rgba(167, 139, 250, .20), transparent 50%);
            pointer-events: none;
        }
        .brand-panel::after {
            content: '';
            position: absolute; inset: 0;
            background-image:
              linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px),
              linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px);
            background-size: 40px 40px;
            mask-image: radial-gradient(ellipse at center, black 30%, transparent 75%);
            pointer-events: none;
        }
        .brand-feature {
            display:flex; align-items:flex-start; gap:14px;
            padding: 14px 16px;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.10);
            border-radius: 14px;
            backdrop-filter: blur(8px);
            transition: background .2s ease, border-color .2s ease;
        }
        .brand-feature:hover { background: rgba(255,255,255,.10); border-color: rgba(255,255,255,.18); }
        .brand-feature-icon {
            flex-shrink: 0;
            width: 38px; height: 38px;
            border-radius: 10px;
            background: rgba(255,255,255,.12);
            display:flex; align-items:center; justify-content:center;
            color: #fbbf24;
        }
    </style>

    @stack('styles')
</head>
<body class="antialiased">

<div class="min-h-screen grid lg:grid-cols-[1fr_1.1fr]">

    {{-- ─────────────── Brand Side ─────────────── --}}
    <aside class="brand-panel hidden lg:flex flex-col justify-between p-12 text-white relative">
        <a href="{{ url('/') }}" class="relative z-10 inline-flex items-center gap-3 w-fit">
            @if($siteLogo)
                <img src="{{ $siteLogo }}" alt="{{ $siteName }}" class="h-10 w-auto object-contain">
            @else
                <div class="h-10 w-10 rounded-lg bg-white/15 border border-white/20 flex items-center justify-center">
                    <i class="fa-solid fa-flask text-sm"></i>
                </div>
            @endif
            <span class="font-bold text-base">{{ $siteName }}</span>
        </a>

        <div class="relative z-10 max-w-md">
            <h1 class="text-4xl xl:text-[2.6rem] font-bold leading-[1.2] tracking-tight">
                منصة متكاملة <br>
                لمستلزماتك <span class="text-amber-300">العلمية</span>
            </h1>
            <p class="text-white/75 mt-5 leading-relaxed text-[15px]">
                إدارة طلباتك، عناوين الشحن، ومتابعة شحناتك في مكان واحد بسيط وآمن.
            </p>

            <div class="mt-8 space-y-3">
                <div class="brand-feature">
                    <div class="brand-feature-icon"><i class="fa-solid fa-truck-fast text-sm"></i></div>
                    <div>
                        <p class="font-semibold text-sm">شحن سريع</p>
                        <p class="text-xs text-white/65 mt-0.5">توصيل لجميع المحافظات بأسعار تنافسية</p>
                    </div>
                </div>
                <div class="brand-feature">
                    <div class="brand-feature-icon"><i class="fa-solid fa-shield-halved text-sm"></i></div>
                    <div>
                        <p class="font-semibold text-sm">دفع آمن</p>
                        <p class="text-xs text-white/65 mt-0.5">بوابات دفع معتمدة وحماية كاملة لبياناتك</p>
                    </div>
                </div>
                <div class="brand-feature">
                    <div class="brand-feature-icon"><i class="fa-solid fa-headset text-sm"></i></div>
                    <div>
                        <p class="font-semibold text-sm">دعم متواصل</p>
                        <p class="text-xs text-white/65 mt-0.5">فريق دعم فني جاهز للرد على استفساراتك</p>
                    </div>
                </div>
            </div>
        </div>

        <p class="relative z-10 text-xs text-white/50">© {{ date('Y') }} {{ $siteName }}</p>
    </aside>

    {{-- ─────────────── Form Side ─────────────── --}}
    <main class="flex flex-col px-6 sm:px-10 lg:px-16 py-8 lg:py-12 min-h-screen">
        {{-- Mobile header --}}
        <div class="lg:hidden mb-8">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2.5">
                @if($siteLogo)
                    <img src="{{ $siteLogo }}" alt="{{ $siteName }}" class="h-9 w-auto object-contain">
                @else
                    <div class="h-9 w-9 rounded-lg bg-blue-600 text-white flex items-center justify-center text-sm"><i class="fa-solid fa-flask"></i></div>
                @endif
                <span class="font-bold text-slate-900 text-sm">{{ $siteName }}</span>
            </a>
        </div>

        <div class="flex-1 flex flex-col justify-center">
            <div class="w-full max-w-[400px] mx-auto">
                {{ $slot }}
            </div>
        </div>

        <p class="text-center text-xs text-slate-400 mt-10">
            بالمتابعة فأنت توافق على
            <a href="{{ url('/') }}" class="link">الشروط والأحكام</a>
            و
            <a href="{{ url('/') }}" class="link">سياسة الخصوصية</a>
        </p>
    </main>
</div>

<script src="{{ asset('js/ajax.js') }}?v={{ @filemtime(public_path('js/ajax.js')) ?: time() }}"></script>
<script>
    document.querySelectorAll('[data-toggle-password]').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = document.querySelector(btn.dataset.togglePassword);
            if (!target) return;
            const isPwd = target.type === 'password';
            target.type = isPwd ? 'text' : 'password';
            btn.querySelector('i').className = isPwd ? 'fa-solid fa-eye-slash text-sm' : 'fa-solid fa-eye text-sm';
        });
    });
</script>
@stack('scripts')
</body>
</html>
