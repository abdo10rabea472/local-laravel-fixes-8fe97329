@php
    $env = function ($k, $d = '') { return env($k, $d); };
@endphp
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500">MAIL_MAILER</label>
        <input type="text" name="MAIL_MAILER" value="{{ $env('MAIL_MAILER','smtp') }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
    </div>
    <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500">MAIL_HOST</label>
        <input type="text" name="MAIL_HOST" value="{{ $env('MAIL_HOST') }}" placeholder="smtp.gmail.com" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
    </div>
    <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500">MAIL_PORT</label>
        <input type="text" name="MAIL_PORT" value="{{ $env('MAIL_PORT','587') }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
    </div>
    <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500">MAIL_ENCRYPTION</label>
        <input type="text" name="MAIL_ENCRYPTION" value="{{ $env('MAIL_ENCRYPTION','tls') }}" placeholder="tls / ssl / null" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
    </div>
    <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500">MAIL_USERNAME</label>
        <input type="text" name="MAIL_USERNAME" value="{{ $env('MAIL_USERNAME') }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm" autocomplete="off">
    </div>
    <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500">MAIL_PASSWORD</label>
        <input type="text" name="MAIL_PASSWORD" value="{{ $env('MAIL_PASSWORD') }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-mono" autocomplete="off">
        <p class="text-[11px] text-slate-400">{{ __('app.admin_settings_mail_password_hint') }}</p>
    </div>
    <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500">MAIL_FROM_ADDRESS</label>
        <input type="email" name="MAIL_FROM_ADDRESS" value="{{ $env('MAIL_FROM_ADDRESS') }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
    </div>
    <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500">MAIL_FROM_NAME</label>
        <input type="text" name="MAIL_FROM_NAME" value="{{ $env('MAIL_FROM_NAME', config('app.name')) }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
    </div>

    <div class="md:col-span-2 flex items-center gap-3 pt-2 border-t border-slate-100">
        <input type="email" id="mail_test_to" placeholder="{{ __('app.admin_settings_mail_test_placeholder') }}" class="flex-1 h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
        <button type="button" id="btn_test_mail" class="h-11 px-5 bg-slate-900 hover:bg-black text-white rounded-2xl text-sm font-bold">
            <i class="fa-solid fa-paper-plane mr-1"></i> {{ __('app.admin_settings_mail_test_btn') }}
        </button>
        <span id="mail_test_result" class="text-xs font-bold"></span>
    </div>
</div>

<script>
document.getElementById('btn_test_mail')?.addEventListener('click', async () => {
    const to = document.getElementById('mail_test_to').value.trim();
    const out = document.getElementById('mail_test_result');
    const msgEnterEmail = '{{ __('app.admin_settings_mail_js_enter_email') }}';
    const msgSending = '{{ __('app.admin_settings_mail_js_sending') }}';
    if (!to) { out.textContent = msgEnterEmail; out.className = 'text-xs font-bold text-rose-600'; return; }
    out.textContent = msgSending; out.className = 'text-xs font-bold text-slate-500';
    try {
        const r = await fetch("{{ route('admin.settings.mail.test') }}", {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
            body: JSON.stringify({ to })
        });
        const j = await r.json();
        out.textContent = j.ok ? '✅ Sent' : ('❌ ' + (j.error || 'Failed'));
        out.className = 'text-xs font-bold ' + (j.ok ? 'text-emerald-600' : 'text-rose-600');
    } catch (e) {
        out.textContent = '❌ ' + e.message;
        out.className = 'text-xs font-bold text-rose-600';
    }
});
</script>
