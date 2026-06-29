<x-guest-layout :authTitle="__('app.auth_login_title')">

    <div class="mb-7">
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">{{ __('app.auth_login_title') }}</h2>
        <p class="text-slate-500 mt-1.5 text-sm">{{ __('app.auth_login_subtitle') }}</p>
    </div>

    @if(session('status'))
        <div class="mb-5 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">
            <i class="fa-solid fa-circle-check ml-1.5"></i>{{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" data-ajax data-success-toast="{{ __('app.auth_login_success_toast') }}" class="space-y-4">
        @csrf

        <div class="field">
            <label for="email" class="field-label">{{ __('app.auth_email_label') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="field-input @error('email') has-error @enderror" placeholder="{{ __('app.auth_email_placeholder') }}">
            @error('email')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <div class="field">
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="field-label !mb-0">{{ __('app.auth_password_label') }}</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs link">{{ __('app.auth_forgot_password') }}</a>
                @endif
            </div>
            <div class="relative">
                <input id="password" type="password" name="password" required autocomplete="current-password"
                       class="field-input has-toggle @error('password') has-error @enderror" placeholder="{{ __('app.auth_password_placeholder') }}">
                <button type="button" class="field-toggle" data-toggle-password="#password" aria-label="{{ __('app.auth_show_password') }}">
                    <i class="fa-solid fa-eye text-sm"></i>
                </button>
            </div>
            @error('password')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <label class="inline-flex items-center gap-2 cursor-pointer select-none pt-1">
            <input type="checkbox" name="remember" class="checkbox">
            <span class="text-sm text-slate-600">{{ __('app.auth_remember_me') }}</span>
        </label>

        <button type="submit" class="btn-primary !mt-6">
            {{ __('app.auth_login_submit') }}
            <i class="fa-solid {{ is_rtl() ? 'fa-arrow-left' : 'fa-arrow-right' }} text-xs"></i>
        </button>

        <p class="text-center text-sm text-slate-600 !mt-6">
            {{ __('app.auth_no_account') }}
            <a href="{{ route('register') }}" class="link">{{ __('app.auth_create_account_link') }}</a>
        </p>
    </form>
</x-guest-layout>
