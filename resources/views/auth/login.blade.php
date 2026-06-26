<x-guest-layout :authTitle="'تسجيل الدخول'">

    <div class="mb-7">
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">تسجيل الدخول</h2>
        <p class="text-slate-500 mt-1.5 text-sm">أدخل بياناتك للوصول إلى حسابك</p>
    </div>

    @if(session('status'))
        <div class="mb-5 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">
            <i class="fa-solid fa-circle-check ml-1.5"></i>{{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" data-ajax data-success-toast="تم تسجيل الدخول بنجاح" class="space-y-4">
        @csrf

        <div class="field">
            <label for="email" class="field-label">البريد الإلكتروني</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="field-input @error('email') has-error @enderror" placeholder="you@example.com">
            @error('email')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <div class="field">
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="field-label !mb-0">كلمة المرور</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs link">نسيت كلمة المرور؟</a>
                @endif
            </div>
            <div class="relative">
                <input id="password" type="password" name="password" required autocomplete="current-password"
                       class="field-input has-toggle @error('password') has-error @enderror" placeholder="••••••••">
                <button type="button" class="field-toggle" data-toggle-password="#password" aria-label="إظهار كلمة المرور">
                    <i class="fa-solid fa-eye text-sm"></i>
                </button>
            </div>
            @error('password')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <label class="inline-flex items-center gap-2 cursor-pointer select-none pt-1">
            <input type="checkbox" name="remember" class="checkbox">
            <span class="text-sm text-slate-600">تذكّرني على هذا الجهاز</span>
        </label>

        <button type="submit" class="btn-primary !mt-6">
            تسجيل الدخول
            <i class="fa-solid fa-arrow-left text-xs"></i>
        </button>

        <p class="text-center text-sm text-slate-600 !mt-6">
            ليس لديك حساب؟
            <a href="{{ route('register') }}" class="link">أنشئ حسابًا جديدًا</a>
        </p>
    </form>
</x-guest-layout>
