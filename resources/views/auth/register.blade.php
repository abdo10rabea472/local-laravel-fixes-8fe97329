<x-guest-layout :authTitle="'إنشاء حساب جديد'">

    <div class="mb-7">
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">إنشاء حساب</h2>
        <p class="text-slate-500 mt-1.5 text-sm">انضم إلينا في أقل من دقيقة</p>
    </div>

    <form method="POST" action="{{ route('register') }}" data-ajax data-success-toast="تم إنشاء الحساب بنجاح" class="space-y-4">
        @csrf

        <div class="field">
            <label for="name" class="field-label">الاسم بالكامل</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                   class="field-input @error('name') has-error @enderror" placeholder="مثال: أحمد محمد">
            @error('name')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <div class="field">
            <label for="email" class="field-label">البريد الإلكتروني</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                   class="field-input @error('email') has-error @enderror" placeholder="you@example.com">
            @error('email')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <div class="field">
            <label for="password" class="field-label">كلمة المرور</label>
            <div class="relative">
                <input id="password" type="password" name="password" required autocomplete="new-password"
                       class="field-input has-toggle @error('password') has-error @enderror" placeholder="8 أحرف على الأقل">
                <button type="button" class="field-toggle" data-toggle-password="#password" aria-label="إظهار كلمة المرور">
                    <i class="fa-solid fa-eye text-sm"></i>
                </button>
            </div>
            @error('password')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <div class="field">
            <label for="password_confirmation" class="field-label">تأكيد كلمة المرور</label>
            <div class="relative">
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="field-input has-toggle" placeholder="أعد إدخال كلمة المرور">
                <button type="button" class="field-toggle" data-toggle-password="#password_confirmation" aria-label="إظهار كلمة المرور">
                    <i class="fa-solid fa-eye text-sm"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn-primary !mt-6">
            إنشاء الحساب
            <i class="fa-solid fa-arrow-left text-xs"></i>
        </button>

        <p class="text-center text-sm text-slate-600 !mt-6">
            لديك حساب بالفعل؟
            <a href="{{ route('login') }}" class="link">سجّل دخولك</a>
        </p>
    </form>
</x-guest-layout>
