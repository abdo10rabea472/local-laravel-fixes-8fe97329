<x-guest-layout :authTitle="'استعادة كلمة المرور'">

    <div class="mb-8">
        <h2 class="text-3xl font-extrabold text-slate-900">نسيت كلمة المرور؟ 🔑</h2>
        <p class="text-slate-500 mt-2 text-sm leading-relaxed">
            لا تقلق! أدخل بريدك الإلكتروني وسنرسل لك رابطًا لإعادة تعيين كلمة المرور.
        </p>
    </div>

    @if(session('status'))
        <div class="mb-5 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold">
            <i class="fa-solid fa-circle-check ml-1"></i> {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" data-ajax data-success-toast="تم إرسال الرابط إلى بريدك" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="auth-label">البريد الإلكتروني</label>
            <div class="relative">
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="auth-input @error('email') has-error @enderror" placeholder="you@example.com">
                <i class="fa-solid fa-envelope auth-icon"></i>
            </div>
            @error('email')<p class="auth-error">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="auth-btn-primary">
            <i class="fa-solid fa-paper-plane"></i>
            إرسال رابط الاستعادة
        </button>

        <p class="text-center text-sm text-slate-600">
            تذكّرت كلمة المرور؟
            <a href="{{ route('login') }}" class="font-bold text-blue-600 hover:text-blue-800">عُد لتسجيل الدخول</a>
        </p>
    </form>
</x-guest-layout>
