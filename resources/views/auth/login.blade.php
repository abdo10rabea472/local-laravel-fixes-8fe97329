<x-guest-layout :authTitle="'تسجيل الدخول'">

    <div class="mb-8">
        <h2 class="text-3xl font-extrabold text-slate-900">مرحبًا بعودتك 👋</h2>
        <p class="text-slate-500 mt-2 text-sm">سجّل دخولك للوصول إلى حسابك ومتابعة طلباتك.</p>
    </div>

    @if(session('status'))
        <div class="mb-5 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold">
            <i class="fa-solid fa-circle-check ml-1"></i> {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" data-ajax data-success-toast="تم تسجيل الدخول بنجاح" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="auth-label">البريد الإلكتروني</label>
            <div class="relative">
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       class="auth-input @error('email') has-error @enderror" placeholder="you@example.com">
                <i class="fa-solid fa-envelope auth-icon"></i>
            </div>
            @error('email')<p class="auth-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password" class="auth-label">كلمة المرور</label>
            <div class="relative">
                <input id="password" type="password" name="password" required autocomplete="current-password"
                       class="auth-input @error('password') has-error @enderror" placeholder="••••••••">
                <i class="fa-solid fa-lock auth-icon"></i>
                <button type="button" class="toggle-pwd" data-toggle-password="#password"><i class="fa-solid fa-eye"></i></button>
            </div>
            @error('password')<p class="auth-error">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center justify-between">
            <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <span class="text-sm text-slate-600">تذكّرني</span>
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">
                    نسيت كلمة المرور؟
                </a>
            @endif
        </div>

        <button type="submit" class="auth-btn-primary">
            <i class="fa-solid fa-right-to-bracket"></i>
            تسجيل الدخول
        </button>

        <div class="relative my-2">
            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
            <div class="relative flex justify-center"><span class="bg-white px-3 text-xs text-slate-400">أو</span></div>
        </div>

        <p class="text-center text-sm text-slate-600">
            ليس لديك حساب؟
            <a href="{{ route('register') }}" class="font-bold text-blue-600 hover:text-blue-800">أنشئ حسابًا جديدًا</a>
        </p>
    </form>
</x-guest-layout>
