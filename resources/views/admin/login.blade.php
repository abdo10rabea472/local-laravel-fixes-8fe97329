<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تسجيل دخول المسؤول | UNI-LAB MARKET</title>

    <!-- Google Fonts: Cairo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        cairo: ['Cairo', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="font-cairo min-h-screen bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-slate-900 via-blue-950 to-slate-950 flex items-center justify-center p-4 relative overflow-hidden">

    <!-- background blobs for premium aesthetic -->
    <div class="absolute top-1/4 right-1/4 w-96 h-96 bg-violet-600/20 rounded-full blur-3xl -z-10 animate-pulse"></div>
    <div class="absolute bottom-1/4 left-1/4 w-96 h-96 bg-cyan-600/20 rounded-full blur-3xl -z-10"></div>

    <div class="w-full max-w-md bg-slate-900/60 backdrop-blur-xl border border-slate-800/80 rounded-3xl p-8 shadow-2xl relative z-10">
        
        <!-- Logo / Title -->
        <div class="text-center mb-8">
            <div class="h-16 w-16 bg-gradient-to-tr from-violet-500 to-indigo-600 text-white rounded-2xl flex items-center justify-center text-3xl shadow-xl shadow-violet-500/20 mx-auto mb-4">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">تسجيل دخول المسؤول</h1>
            <p class="text-slate-400 text-sm mt-2">لوحة تحكم متجر UNI-LAB MARKET</p>
        </div>

        <!-- Global error message -->
        @if (session('error'))
            <div class="bg-rose-950/40 border border-rose-800/30 text-rose-300 px-4 py-3 rounded-2xl text-sm font-semibold mb-6 flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-rose-500"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-rose-950/40 border border-rose-800/30 text-rose-300 px-4 py-3 rounded-2xl text-sm font-semibold mb-6">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-triangle-exclamation text-rose-500 text-[10px]"></i>
                            <span>{{ $error }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('admin.login') }}" class="space-y-6">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-slate-300 text-sm font-bold mb-2">البريد الإلكتروني</label>
                <div class="relative">
                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-500">
                        <i class="fa-regular fa-envelope"></i>
                    </span>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        value="{{ old('email') }}" 
                        required 
                        autofocus 
                        autocomplete="username"
                        placeholder="admin@uni.com"
                        class="w-full h-12 pr-11 pl-4 bg-slate-950/50 border border-slate-800 focus:border-violet-500 rounded-2xl text-white text-sm outline-none transition-all placeholder:text-slate-600"
                    >
                </div>
            </div>

            <!-- Password -->
            <div class="mt-4">
                <label for="password" class="block text-slate-300 text-sm font-bold mb-2">كلمة المرور</label>
                <div class="relative">
                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-500">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        required 
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full h-12 pr-11 pl-4 bg-slate-950/50 border border-slate-800 focus:border-violet-500 rounded-2xl text-white text-sm outline-none transition-all placeholder:text-slate-600"
                    >
                </div>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between mt-4">
                <label class="inline-flex items-center cursor-pointer select-none">
                    <input 
                        type="checkbox" 
                        name="remember" 
                        class="rounded border-slate-800 text-violet-600 shadow-sm focus:ring-violet-500 bg-slate-950/50 h-4 w-4"
                    >
                    <span class="mr-2 text-sm text-slate-400 font-semibold">تذكرني</span>
                </label>
            </div>

            <!-- Submit Button -->
             <button
                type="button"
                onclick="quickLogin()"
                class="w-full h-12 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-2xl transition-all flex items-center justify-center gap-2 mb-3"
            >
                <i class="fa-solid fa-bolt"></i>
                <span>دخول سريع</span>
            </button>
            <div class="pt-2">
                <button 
                    type="submit" 
                    class="w-full h-12 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white font-bold rounded-2xl shadow-xl shadow-violet-600/20 transition-all hover:scale-[1.01] active:scale-[0.99] flex items-center justify-center gap-2"
                >
                    <span>تسجيل الدخول</span>
                    <i class="fa-solid fa-arrow-left-long"></i>
                </button>
            </div>
        </form>

        <div class="mt-8 text-center border-t border-slate-800/80 pt-6">
            <a href="{{ url('/') }}" class="text-sm font-bold text-slate-400 hover:text-violet-400 transition-colors flex items-center justify-center gap-2">
                <i class="fa-solid fa-house"></i>
                <span>العودة لصفحة المتجر الرئيسية</span>
            </a>
        </div>

    </div>


    <script>
function quickLogin() {
    document.getElementById('email').value = 'admin@uni.com';
    document.getElementById('password').value = 'password';

    setTimeout(() => {
        document.querySelector('form').submit();
    }, 300);
}
</script>

</body>
</html>
