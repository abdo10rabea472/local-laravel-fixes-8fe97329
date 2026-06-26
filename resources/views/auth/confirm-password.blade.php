<x-guest-layout :authTitle="'تأكيد كلمة المرور'">

    <div class="mb-8">
        <div class="inline-flex h-14 w-14 rounded-2xl bg-amber-50 items-center justify-center text-amber-600 mb-4">
            <i class="fa-solid fa-shield-halved text-xl"></i>
        </div>
        <h2 class="text-2xl font-extrabold text-slate-900">منطقة آمنة</h2>
        <p class="text-slate-500 mt-2 text-sm">من فضلك أكّد كلمة المرور قبل المتابعة.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" data-ajax class="space-y-5">
        @csrf

        <div>
            <label for="password" class="auth-label">كلمة المرور</label>
            <div class="relative">
                <input id="password" type="password" name="password" required autocomplete="current-password" autofocus
                       class="auth-input @error('password') has-error @enderror" placeholder="••••••••">
                <i class="fa-solid fa-lock auth-icon"></i>
                <button type="button" class="toggle-pwd" data-toggle-password="#password"><i class="fa-solid fa-eye"></i></button>
            </div>
            @error('password')<p class="auth-error">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="auth-btn-primary">
            <i class="fa-solid fa-check"></i>
            تأكيد
        </button>
    </form>
</x-guest-layout>
