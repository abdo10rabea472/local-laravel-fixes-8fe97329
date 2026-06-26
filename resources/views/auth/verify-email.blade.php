<x-guest-layout :authTitle="'تأكيد البريد الإلكتروني'">

    <div class="text-center mb-8">
        <div class="inline-flex h-20 w-20 rounded-3xl bg-gradient-to-br from-blue-50 to-indigo-100 items-center justify-center text-blue-600 mb-5">
            <i class="fa-solid fa-envelope-circle-check text-3xl"></i>
        </div>
        <h2 class="text-2xl font-extrabold text-slate-900">تحقّق من بريدك الإلكتروني</h2>
        <p class="text-slate-500 mt-3 text-sm leading-relaxed">
            شكرًا لتسجيلك معنا! أرسلنا رابط التحقق إلى بريدك الإلكتروني — افتحه لتفعيل حسابك.
            إذا لم تستلمه، يمكنك طلب رابط جديد.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-5 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold text-center">
            <i class="fa-solid fa-circle-check ml-1"></i> تم إرسال رابط تحقق جديد إلى بريدك.
        </div>
    @endif

    <div class="space-y-3">
        <form method="POST" action="{{ route('verification.send') }}" data-ajax data-success-toast="تم إرسال رابط التحقق">
            @csrf
            <button type="submit" class="auth-btn-primary">
                <i class="fa-solid fa-paper-plane"></i>
                إعادة إرسال رابط التحقق
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full h-12 rounded-2xl border border-slate-200 hover:border-rose-200 text-slate-600 hover:text-rose-600 text-sm font-semibold transition-colors">
                <i class="fa-solid fa-right-from-bracket ml-1"></i> تسجيل الخروج
            </button>
        </form>
    </div>
</x-guest-layout>
