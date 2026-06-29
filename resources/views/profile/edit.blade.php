@extends('account.layout')

@section('account_content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-gradient-to-l from-violet-600 via-indigo-600 to-violet-700 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-violet-500/20 relative overflow-hidden">
        <div class="absolute -left-10 -top-10 w-48 h-48 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute -right-10 -bottom-10 w-56 h-56 bg-amber-400/10 rounded-full blur-3xl"></div>
        <div class="relative flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <div class="w-20 h-20 rounded-2xl bg-white/20 backdrop-blur border border-white/30 flex items-center justify-center text-4xl font-black shrink-0">
                {{ mb_substr(auth()->user()->name, 0, 1) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs uppercase tracking-widest text-white/70 font-bold mb-1">{{ __('app.acc_profile_account_data') }}</p>
                <h1 class="text-2xl sm:text-3xl font-black truncate">{{ auth()->user()->name }}</h1>
                <p class="text-white/80 text-sm truncate mt-1">
                    <i class="fa-regular fa-envelope ml-1"></i> {{ auth()->user()->email }}
                </p>
            </div>
            <a href="{{ route('account.dashboard') }}" class="hidden sm:inline-flex items-center gap-2 bg-white/15 hover:bg-white/25 border border-white/30 backdrop-blur px-4 py-2 rounded-xl text-sm font-bold transition">
                <i class="fa-solid fa-gauge-high"></i> {{ __('app.acc_profile_dashboard') }}
            </a>
        </div>
    </div>

    {{-- Tabs nav --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-1.5 flex flex-wrap gap-1 sticky top-4 z-10 shadow-sm">
        <a href="#info" class="profile-tab flex-1 text-center min-w-[120px] px-4 py-2.5 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition">
            <i class="fa-solid fa-user-pen ml-1"></i> {{ __('app.acc_profile_tab_info') }}
        </a>
        <a href="#password" class="profile-tab flex-1 text-center min-w-[120px] px-4 py-2.5 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition">
            <i class="fa-solid fa-lock ml-1"></i> {{ __('app.acc_profile_tab_password') }}
        </a>
        <a href="#danger" class="profile-tab flex-1 text-center min-w-[120px] px-4 py-2.5 rounded-xl text-sm font-bold text-rose-600 hover:bg-rose-50 transition">
            <i class="fa-solid fa-triangle-exclamation ml-1"></i> {{ __('app.acc_profile_tab_danger') }}
        </a>
    </div>

    {{-- Info card --}}
    <section id="info" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden scroll-mt-24">
        <header class="px-6 sm:px-8 py-5 bg-gradient-to-l from-slate-50 to-white border-b border-slate-100 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-violet-100 text-violet-600 flex items-center justify-center">
                <i class="fa-solid fa-id-card"></i>
            </div>
            <div>
                <h2 class="font-black text-slate-900">{{ __('app.acc_profile_info_title') }}</h2>
                <p class="text-xs text-slate-500">{{ __('app.acc_profile_info_sub') }}</p>
            </div>
        </header>
        <div class="p-6 sm:p-8">
            @include('profile.partials.update-profile-information-form')
        </div>
    </section>

    {{-- Password card --}}
    <section id="password" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden scroll-mt-24">
        <header class="px-6 sm:px-8 py-5 bg-gradient-to-l from-slate-50 to-white border-b border-slate-100 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center">
                <i class="fa-solid fa-key"></i>
            </div>
            <div>
                <h2 class="font-black text-slate-900">{{ __('app.acc_profile_pass_title') }}</h2>
                <p class="text-xs text-slate-500">{{ __('app.acc_profile_pass_sub') }}</p>
            </div>
        </header>
        <div class="p-6 sm:p-8">
            @include('profile.partials.update-password-form')
        </div>
    </section>

    {{-- Danger card --}}
    <section id="danger" class="bg-white rounded-2xl border-2 border-rose-200 shadow-sm overflow-hidden scroll-mt-24">
        <header class="px-6 sm:px-8 py-5 bg-rose-50 border-b border-rose-100 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center">
                <i class="fa-solid fa-trash"></i>
            </div>
            <div>
                <h2 class="font-black text-rose-900">{{ __('app.acc_profile_danger_title') }}</h2>
                <p class="text-xs text-rose-600">{{ __('app.acc_profile_danger_sub') }}</p>
            </div>
        </header>
        <div class="p-6 sm:p-8">
            @include('profile.partials.delete-user-form')
        </div>
    </section>
</div>


<script>
document.querySelectorAll('.profile-tab').forEach(t => t.addEventListener('click', e => {
    e.preventDefault();
    document.querySelector(t.getAttribute('href'))?.scrollIntoView({behavior:'smooth', block:'start'});
}));
</script>
@endsection
