@extends('admin.settings.layout')

@section('settings-content')
<div class="space-y-6">

    {{-- ─────────────── Header ─────────────── --}}
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 px-6 py-4 flex items-center gap-3">
            <div class="h-10 w-10 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                <i class="fa-solid fa-credit-card"></i>
            </div>
            <div class="flex-1">
                <h3 class="text-base font-bold text-slate-800">{{ __('app.admin_settings_payment_title') }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">إدارة {{ __('app.admin_settings_payment_title') }} المتاحة للعملاء، الرسوم الإضافية، والدول المسموح بها.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="mx-6 mt-4 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold">
                <i class="fa-solid fa-circle-check ml-2"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mx-6 mt-4 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-semibold">
                <i class="fa-solid fa-circle-exclamation ml-2"></i> {{ session('error') }}
            </div>
        @endif

        <div class="p-6">
            @if($gateways->isEmpty())
                <div class="p-12 text-center text-slate-500 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200">
                    <i class="fa-solid fa-credit-card text-5xl text-slate-300 mb-3"></i>
                    <p class="font-semibold">{{ __('app.admin_settings_payment_empty') }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ __('app.admin_settings_payment_empty_hint') }}</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach($gateways as $g)
                        <div class="border border-slate-200 rounded-2xl p-5 hover:shadow-md transition-shadow bg-white flex flex-col">
                            <div class="flex items-start justify-between gap-3 mb-4">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-indigo-50 to-violet-50 flex items-center justify-center text-indigo-600 shrink-0">
                                        @if($g->logo)
                                            <img src="{{ $g->logo }}" alt="" class="h-8 w-8 object-contain">
                                        @else
                                            <i class="fa-solid fa-wallet text-lg"></i>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="font-bold text-slate-900 truncate">{{ $g->name }}</h4>
                                        <p class="text-[11px] text-slate-500 font-mono truncate">{{ $g->code }} · {{ $g->driver }}</p>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('admin.settings.payment-gateways.toggle', $g) }}" class="shrink-0">
                                    @csrf @method('PATCH')
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" onchange="this.form.submit()" {{ $g->is_active ? 'checked' : '' }} class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-300 rounded-full peer peer-checked:bg-emerald-500 transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-transform peer-checked:after:translate-x-5"></div>
                                    </label>
                                </form>
                            </div>

                            <div class="flex flex-wrap gap-2 mb-4">
                                @if($g->is_active)
                                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-1 rounded-full border border-emerald-200">{{ __('app.admin_settings_payment_badge_active') }}</span>
                                @else
                                    <span class="text-[10px] font-bold text-slate-600 bg-slate-100 px-2 py-1 rounded-full">{{ __('app.admin_settings_payment_badge_inactive') }}</span>
                                @endif
                                @if($g->sandbox)
                                    <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-1 rounded-full border border-amber-200">Sandbox</span>
                                @else
                                    <span class="text-[10px] font-bold text-indigo-700 bg-indigo-50 px-2 py-1 rounded-full border border-indigo-200">Live</span>
                                @endif
                            </div>

                            <dl class="text-xs text-slate-600 space-y-1.5 mb-5 flex-1">
                                <div class="flex justify-between">
                                    <dt class="text-slate-500">{{ __('app.admin_settings_payment_extra_fees') }}</dt>
                                    <dd class="font-bold text-slate-800">{{ money($g->extra_fees) }}</dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-slate-500 shrink-0">{{ __('app.admin_settings_payment_countries') }}</dt>
                                    <dd class="font-bold text-slate-800 text-left truncate">
                                        @if(!empty($g->allowed_countries))
                                            {{ implode(', ', $g->allowed_countries) }}
                                        @else
                                            <span class="text-emerald-600">{{ __('app.admin_settings_payment_countries_all') }}</span>
                                        @endif
                                    </dd>
                                </div>
                            </dl>

                            <div class="flex items-center gap-2 pt-3 border-t border-slate-100">
                                <a href="{{ route('admin.settings.payment-gateways.edit', $g) }}" class="flex-1 text-center text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2.5 rounded-xl transition-colors">
                                    <i class="fa-solid fa-pen ml-1"></i> {{ __('app.admin_settings_payment_btn_edit') }}
                                </a>
                                <button type="button" onclick="testGateway({{ $g->id }}, this)" class="text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-2.5 rounded-xl transition-colors">
                                    <i class="fa-solid fa-plug-circle-check ml-1"></i> اختبار
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function testGateway(id, btn) {
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin ml-1"></i> {{ __('app.admin_settings_payment_testing') }}';
    fetch('{{ url('admin/settings/payment-gateways') }}/' + id + '/test', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(r => r.json()).then(d => {
        alert((d.ok ? '✓ ' : '✗ ') + (d.message || ''));
    }).catch(e => alert('خطأ: ' + e.message))
      .finally(() => { btn.disabled = false; btn.innerHTML = orig; });
}
</script>
@endsection
