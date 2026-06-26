<x-guest-layout :authTitle="'إعادة تعيين كلمة المرور'">

    <div class="mb-8">
        <h2 class="text-3xl font-extrabold text-slate-900">كلمة مرور جديدة 🛡️</h2>
        <p class="text-slate-500 mt-2 text-sm">اختر كلمة مرور قوية لحماية حسابك.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" data-ajax data-success-toast="تم تحديث كلمة المرور" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="auth-label">البريد الإلكتروني</label>
            <div class="relative">
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                       class="auth-input @error('email') has-error @enderror" placeholder="you@example.com">
                <i class="fa-solid fa-envelope auth-icon"></i>
            </div>
            @error('email')<p class="auth-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password" class="auth-label">كلمة المرور الجديدة</label>
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

        <button type="submit" class="auth-btn-primary">
            <i class="fa-solid fa-rotate-right"></i>
            إعادة تعيين كلمة المرور
        </button>
    </form>
</x-guest-layout>
