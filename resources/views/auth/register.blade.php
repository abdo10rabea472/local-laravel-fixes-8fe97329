<x-guest-layout :authTitle="'إنشاء حساب جديد'">

    <div class="mb-8">
        <h2 class="text-3xl font-extrabold text-slate-900">أنشئ حسابك الآن ✨</h2>
        <p class="text-slate-500 mt-2 text-sm">انضم إلينا واستفد من أفضل العروض وتتبّع طلباتك بسهولة.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" data-ajax data-success-toast="تم إنشاء الحساب بنجاح" class="space-y-5">
        @csrf

        <div>
            <label for="name" class="auth-label">الاسم بالكامل</label>
            <div class="relative">
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                       class="auth-input @error('name') has-error @enderror" placeholder="مثال: أحمد محمد">
                <i class="fa-solid fa-user auth-icon"></i>
            </div>
            @error('name')<p class="auth-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="email" class="auth-label">البريد الإلكتروني</label>
            <div class="relative">
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                       class="auth-input @error('email') has-error @enderror" placeholder="you@example.com">
                <i class="fa-solid fa-envelope auth-icon"></i>
            </div>
            @error('email')<p class="auth-error">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="password" class="auth-label">كلمة المرور</label>
                <div class="relative">
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                           class="auth-input @error('password') has-error @enderror" placeholder="••••••••">
                    <i class="fa-solid fa-lock auth-icon"></i>
                    <button type="button" class="toggle-pwd" data-toggle-password="#password"><i class="fa-solid fa-eye"></i></button>
                </div>
                @error('password')<p class="auth-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password_confirmation" class="auth-label">تأكيد كلمة المرور</label>
                <div class="relative">
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                           class="auth-input" placeholder="••••••••">
                    <i class="fa-solid fa-lock auth-icon"></i>
                    <button type="button" class="toggle-pwd" data-toggle-password="#password_confirmation"><i class="fa-solid fa-eye"></i></button>
                </div>
            </div>
        </div>

        <button type="submit" class="auth-btn-primary">
            <i class="fa-solid fa-user-plus"></i>
            إنشاء الحساب
        </button>

        <div class="relative my-2">
            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
            <div class="relative flex justify-center"><span class="bg-white px-3 text-xs text-slate-400">أو</span></div>
        </div>

        <p class="text-center text-sm text-slate-600">
            لديك حساب بالفعل؟
            <a href="{{ route('login') }}" class="font-bold text-blue-600 hover:text-blue-800">سجّل دخولك</a>
        </p>
    </form>
</x-guest-layout>
