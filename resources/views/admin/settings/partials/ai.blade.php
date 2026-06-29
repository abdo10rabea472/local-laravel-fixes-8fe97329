<div class="space-y-8">
    <div class="rounded-2xl bg-gradient-to-r from-violet-50 to-indigo-50 border border-violet-200 p-4 text-sm text-violet-900 flex gap-3">
        <i class="fa-solid fa-circle-info text-violet-600 mt-1"></i>
        <div>{!! __('app.admin_settings_ai_info') !!}</div>
    </div>

    {{-- Toggle --}}
    <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-200">
        <div>
            <label for="ai_enabled" class="text-sm font-bold text-slate-800">{{ __('app.admin_settings_ai_enable_label') }}</label>
            <p class="text-xs text-slate-500 mt-1">{{ __('app.admin_settings_ai_enable_hint') }}</p>
        </div>
        <label class="relative inline-flex items-center cursor-pointer">
            <input type="hidden" name="ai_enabled" value="0">
            <input type="checkbox" id="ai_enabled" name="ai_enabled" value="1" @checked(site_setting('ai_enabled') === '1') class="sr-only peer">
            <div class="w-12 h-7 bg-slate-300 peer-focus:ring-2 peer-focus:ring-violet-300 rounded-full peer peer-checked:after:translate-x-5 after:content-[''] after:absolute after:top-0.5 after:start-0.5 after:bg-white after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-violet-600"></div>
        </label>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
            <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_ai_provider_name') }}</label>
            <input type="text" name="ai_provider_name" id="ai_provider_name"
                   value="{{ site_setting('ai_provider_name', 'Gemini') }}" placeholder="OpenAI / Groq / OpenRouter ..."
                   class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
        </div>

        <div class="space-y-2">
            <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_ai_model_name') }}</label>
            <input type="text" name="ai_model" id="ai_model" dir="ltr"
                   value="{{ site_setting('ai_model', 'gemini-flash-latest') }}"
                   placeholder="gpt-4o-mini, gemini-flash-latest, llama-3.1-70b ..."
                   class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-mono">
        </div>

        <div class="space-y-2 md:col-span-2">
            <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_ai_base_url') }}</label>
            <input type="url" name="ai_base_url" id="ai_base_url" dir="ltr"
                   value="{{ site_setting('ai_base_url', 'https://generativelanguage.googleapis.com/v1beta') }}"
                   placeholder="https://generativelanguage.googleapis.com/v1beta"
                   class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-mono">
            <p class="text-xs text-slate-500">
                {{ __('app.admin_settings_ai_base_url_examples') }}
                <span class="font-mono">https://api.openai.com/v1</span> ·
                <span class="font-mono">https://generativelanguage.googleapis.com/v1beta</span> (Gemini) ·
                <span class="font-mono">https://api.groq.com/openai/v1</span> ·
                <span class="font-mono">https://openrouter.ai/api/v1</span> ·
                <span class="font-mono">http://localhost:11434/v1</span>
            </p>
        </div>

        <div class="space-y-2 md:col-span-2">
            <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_ai_api_key') }}</label>
            <div class="relative">
                <input type="password" name="ai_api_key" id="ai_api_key" dir="ltr" autocomplete="new-password"
                       value="{{ site_setting('ai_api_key') }}" placeholder="sk-..."
                       class="w-full h-11 px-4 pe-12 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-mono">
                <button type="button" onclick="(function(b){const i=document.getElementById('ai_api_key');i.type=i.type==='password'?'text':'password';b.querySelector('i').className=i.type==='password'?'fa-solid fa-eye':'fa-solid fa-eye-slash';})(this)"
                        class="absolute inset-y-0 end-2 my-1 px-3 rounded-xl text-slate-400 hover:bg-slate-100">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
            <p class="text-xs text-slate-500">{{ __('app.admin_settings_ai_api_key_hint') }}</p>
        </div>
    </div>

    {{-- Test connection --}}
    <div class="rounded-2xl border border-dashed border-violet-300 bg-violet-50/50 p-5">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h4 class="text-sm font-bold text-slate-800 flex items-center gap-2"><i class="fa-solid fa-plug-circle-bolt text-violet-600"></i> {{ __('app.admin_settings_ai_test_title') }}</h4>
                <p class="text-xs text-slate-500 mt-1">{{ __('app.admin_settings_ai_test_subtitle') }}</p>
            </div>
            <button type="button" id="ai-test-btn"
                    class="h-11 px-6 bg-violet-600 hover:bg-violet-700 text-white font-bold rounded-xl shadow-md shadow-violet-500/20 inline-flex items-center gap-2">
                <i class="fa-solid fa-bolt"></i>
                <span id="ai-test-label">{{ __('app.admin_settings_ai_test_btn') }}</span>
            </button>
        </div>
        <div id="ai-test-result" class="hidden mt-4 p-4 rounded-xl text-sm"></div>
    </div>
</div>

<script>
(function(){
    const btn = document.getElementById('ai-test-btn');
    if (!btn) return;
    const url = @json(route('admin.settings.ai.test'));
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const out = document.getElementById('ai-test-result');
    const label = document.getElementById('ai-test-label');
    const labelDefault = '{{ __('app.admin_settings_ai_test_btn') }}';
    const msgFillFields = '{{ __('app.admin_settings_ai_js_fill_fields') }}';
    const msgTesting = '{{ __('app.admin_settings_ai_js_testing') }}';
    const msgConnecting = '<i class="fa-solid fa-spinner fa-spin"></i> {{ __('app.admin_settings_ai_js_connecting') }}';

    function show(ok, title, detail) {
        out.className = 'mt-4 p-4 rounded-xl text-sm border ' +
            (ok ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800');
        out.innerHTML = '<div class="font-bold flex items-center gap-2"><i class="fa-solid '+(ok?'fa-circle-check':'fa-circle-xmark')+'"></i> '+title+'</div>'
                      + (detail ? '<pre dir="ltr" class="mt-2 text-xs whitespace-pre-wrap font-mono opacity-80">'+detail+'</pre>' : '');
    }

    btn.addEventListener('click', async () => {
        const base_url = document.getElementById('ai_base_url').value.trim();
        const api_key  = document.getElementById('ai_api_key').value.trim();
        const model    = document.getElementById('ai_model').value.trim();

        if (!base_url || !api_key || !model) {
            out.classList.remove('hidden');
            show(false, msgFillFields, '');
            return;
        }

        btn.disabled = true;
        label.textContent = msgTesting;
        btn.classList.add('opacity-70');
        out.classList.remove('hidden');
        out.className = 'mt-4 p-4 rounded-xl text-sm bg-slate-100 text-slate-600';
        out.innerHTML = msgConnecting;

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':token,'Accept':'application/json'},
                body: JSON.stringify({ base_url, api_key, model }),
            });
            const data = await res.json();
            show(!!data.ok, data.message || (data.ok?'Success':'Failed'), data.reply || data.error || '');
        } catch (e) {
            show(false, 'Could not send the request', e.message);
        } finally {
            btn.disabled = false;
            label.textContent = labelDefault;
            btn.classList.remove('opacity-70');
        }
    });
})();
</script>
