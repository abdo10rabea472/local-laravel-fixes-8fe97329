@extends('admin.settings.layout', ['activeTab' => 'currencies'])

@section('title', 'Currencies')

@section('settings-content')
<style>[x-cloak]{display:none !important;}</style>
<div class="space-y-6" x-data="{ open:false, edit:null, form:{}, defOpen:false, defCur:{id:null,code:'',name:'',url:''} }">
    @if(session('success'))<div class="p-4 rounded-2xl bg-emerald-50 text-emerald-700 text-sm font-bold">{{ session('success') }}</div>@endif
    @if(session('warning'))<div class="p-4 rounded-2xl bg-amber-50 text-amber-800 text-sm font-bold border border-amber-200"><i class="fa-solid fa-triangle-exclamation mr-2"></i>{{ session('warning') }}</div>@endif
    @if(session('error'))<div class="p-4 rounded-2xl bg-rose-50 text-rose-700 text-sm font-bold border border-rose-200"><i class="fa-solid fa-circle-xmark mr-2"></i>{{ session('error') }}</div>@endif
    @php $modalErrorKeys = ['password','confirm_code','understand']; $otherErrors = collect($errors->keys())->diff($modalErrorKeys); @endphp
    @if($otherErrors->count())<div class="p-4 rounded-2xl bg-rose-50 text-rose-700 text-sm">{{ $errors->first($otherErrors->first()) }}</div>@endif

    @php $zeroRates = $currencies->where('exchange_rate', 0)->where('is_default', false); @endphp
    @if($zeroRates->count())
    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-sm">
        <div class="font-black mb-1"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Action required: update exchange rates</div>
        <p class="text-xs">The following currencies have <strong>rate = 0</strong> and will display incorrect prices until you set their exchange rate relative to the default currency:
            <span class="font-mono font-bold">{{ $zeroRates->pluck('code')->join(', ') }}</span>
        </p>
    </div>
    @endif


    <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden">
        <div class="flex items-center justify-between p-5 border-b border-slate-100">
            <div>
                <h2 class="text-lg font-black text-slate-800">Currencies</h2>
                <p class="text-sm text-slate-500">All product prices are stored in the default currency. Other currencies convert via exchange rate.</p>
            </div>
            <button @click="open=true; edit=null; form={symbol_position:'before', decimals:2, decimal_separator:'.', thousands_separator:',', exchange_rate:1, is_active:true, sort_order:({{ $currencies->count() + 1 }})}"
                    class="px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold">
                <i class="fa-solid fa-plus mr-1"></i> Add Currency
            </button>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                <tr>
                    <th class="p-3 text-left">Name</th>
                    <th class="p-3 text-left">Code</th>
                    <th class="p-3 text-left">Symbol</th>
                    <th class="p-3 text-left">Rate</th>
                    <th class="p-3 text-left">Preview</th>
                    <th class="p-3 text-left">Default</th>
                    <th class="p-3 text-left">Active</th>
                    <th class="p-3 text-left">Order</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
            @forelse($currencies as $cur)
                <tr class="border-t border-slate-100">
                    <td class="p-3 font-bold text-slate-800">{{ $cur->name }}</td>
                    <td class="p-3 font-mono">{{ $cur->code }}</td>
                    <td class="p-3">{{ $cur->symbol }} <span class="text-xs text-slate-400">({{ $cur->symbol_position }})</span></td>
                    <td class="p-3 font-mono">{{ rtrim(rtrim(number_format($cur->exchange_rate, 8, '.', ''), '0'), '.') }}</td>
                    <td class="p-3 text-slate-600">{{ $cur->format(1234.5) }}</td>
                    <td class="p-3">
                        @if($cur->is_default)
                            <span class="px-2 py-1 bg-emerald-100 text-emerald-700 text-xs font-bold rounded-full">Default</span>
                        @else
                            <button type="button"
                                @click="defOpen=true; defCur={id:{{ $cur->id }}, code:'{{ addslashes($cur->code) }}', name:'{{ addslashes($cur->name) }}', url:'{{ route('admin.settings.currencies.default', $cur) }}'}"
                                class="inline-flex items-center gap-1 text-xs font-bold text-violet-600 hover:text-violet-800">
                                <i class="fa-solid fa-shield-halved"></i> Make default
                            </button>
                        @endif
                    </td>
                    <td class="p-3">
                        <span class="px-2 py-1 text-xs font-bold rounded-full {{ $cur->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $cur->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="p-3">{{ $cur->sort_order }}</td>
                    <td class="p-3 text-right whitespace-nowrap">
                        <button @click='open=true; edit={{ $cur->id }}; form=@json($cur)'
                                class="text-violet-600 hover:underline text-xs font-bold mr-2">Edit</button>
                        @unless($cur->is_default)
                        <form method="POST" action="{{ route('admin.settings.currencies.destroy', $cur) }}" class="inline"
                              onsubmit="return confirm('Delete this currency?')">
                            @csrf @method('DELETE')
                            <button class="text-rose-600 hover:underline text-xs font-bold">Delete</button>
                        </form>
                        @endunless
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="p-8 text-center text-slate-400">No currencies yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal --}}
    <div x-show="open" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="open=false">
        <form method="POST"
              :action="edit ? '{{ url('admin/settings/currencies') }}/' + edit : '{{ route('admin.settings.currencies.store') }}'"
              class="bg-white rounded-3xl w-full max-w-2xl p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            @csrf
            <template x-if="edit">@method('PUT')</template>
            <h3 class="text-lg font-black" x-text="edit ? 'Edit Currency' : 'Add Currency'"></h3>

            <div class="grid grid-cols-2 gap-3">
                <label class="block text-sm col-span-2">
                    <span class="text-xs font-bold text-slate-500">Name</span>
                    <input type="text" name="name" x-model="form.name" required class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </label>
                <label class="block text-sm">
                    <span class="text-xs font-bold text-slate-500">Code (ISO)</span>
                    <input type="text" name="code" x-model="form.code" required maxlength="10" class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono uppercase">
                </label>
                <label class="block text-sm">
                    <span class="text-xs font-bold text-slate-500">Symbol</span>
                    <input type="text" name="symbol" x-model="form.symbol" required maxlength="10" class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </label>
                <label class="block text-sm">
                    <span class="text-xs font-bold text-slate-500">Symbol Position</span>
                    <select name="symbol_position" x-model="form.symbol_position" class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                        <option value="before">Before ($100)</option>
                        <option value="after">After (100 ج.م)</option>
                    </select>
                </label>
                <label class="block text-sm">
                    <span class="text-xs font-bold text-slate-500">Decimals</span>
                    <input type="number" name="decimals" x-model="form.decimals" min="0" max="6" required class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </label>
                <label class="block text-sm">
                    <span class="text-xs font-bold text-slate-500">Decimal Separator</span>
                    <input type="text" name="decimal_separator" x-model="form.decimal_separator" maxlength="4" required class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </label>
                <label class="block text-sm">
                    <span class="text-xs font-bold text-slate-500">Thousands Separator</span>
                    <input type="text" name="thousands_separator" x-model="form.thousands_separator" maxlength="4" class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </label>
                <label class="block text-sm">
                    <span class="text-xs font-bold text-slate-500">Exchange Rate <span class="text-slate-400">(per 1 default)</span></span>
                    <input type="number" step="0.00000001" name="exchange_rate" x-model="form.exchange_rate" required class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono">
                </label>
                <label class="block text-sm">
                    <span class="text-xs font-bold text-slate-500">Sort Order</span>
                    <input type="number" name="sort_order" x-model="form.sort_order" min="0" class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </label>
            </div>

            <div class="flex items-center gap-6">
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" :checked="form.is_active"> Active
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="is_default" value="0">
                    <input type="checkbox" name="is_default" value="1" :checked="form.is_default"> Set as default
                </label>
            </div>

            <div class="flex gap-2 justify-end pt-2">
                <button type="button" @click="open=false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-sm font-bold">Cancel</button>
                <button class="px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold">Save</button>
            </div>
        </form>
    </div>

    {{-- Secure Make-Default Modal (auto-reopens on validation error) --}}
    @php
        $errCurId = session('default_error_for_currency');
        $errCur   = $errCurId ? $currencies->firstWhere('id', $errCurId) : null;
    @endphp
    <div x-show="defOpen" x-cloak class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" @click.self="defOpen=false" @keydown.escape.window="defOpen=false"
        @if($errCur)
        x-init="defOpen=true; defCur={id:{{ $errCur->id }}, code:'{{ addslashes($errCur->code) }}', name:'{{ addslashes($errCur->name) }}', url:'{{ route('admin.settings.currencies.default', $errCur) }}'}"
        @endif>
        <form method="POST" :action="defCur.url" x-data="{ typed:@js(old('confirm_code','')), pwd:'', ack:false }" class="bg-white rounded-3xl w-full max-w-md p-6 space-y-4 shadow-2xl border-t-4 border-rose-500">
            @csrf
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div>
                    <h3 class="text-lg font-black text-slate-800">High-risk action</h3>
                    <p class="text-xs text-slate-500">Changing the base currency affects all stored prices.</p>
                </div>
            </div>

            <div class="p-3 rounded-xl bg-rose-50 border border-rose-100 text-xs text-rose-700 space-y-1">
                <div>You are about to set <span class="font-black" x-text="defCur.name"></span> (<span class="font-mono" x-text="defCur.code"></span>) as the <strong>base currency</strong>.</div>
                <div class="font-bold pt-1">⚠️ All other currencies' exchange rates will be reset to 0. You must re-enter every rate relative to the new base, or prices will display incorrectly.</div>
            </div>

            <label class="block text-sm">
                <span class="text-xs font-bold text-slate-600">Type the currency code <span class="font-mono text-rose-600" x-text="defCur.code"></span> to confirm</span>
                <input type="text" name="confirm_code" x-model="typed" autocomplete="off" required
                    class="w-full h-10 px-3 mt-1 bg-slate-50 border rounded-xl text-sm font-mono uppercase focus:ring-2 @error('confirm_code') border-rose-400 ring-rose-200 @else border-slate-200 focus:border-rose-400 focus:ring-rose-200 @enderror">
                @error('confirm_code')<p class="mt-1 text-xs font-bold text-rose-600"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>@enderror
            </label>

            <label class="block text-sm">
                <span class="text-xs font-bold text-slate-600">Your admin password</span>
                <input type="password" name="password" x-model="pwd" autocomplete="current-password" required
                    class="w-full h-10 px-3 mt-1 bg-slate-50 border rounded-xl text-sm focus:ring-2 @error('password') border-rose-400 ring-rose-200 @else border-slate-200 focus:border-rose-400 focus:ring-rose-200 @enderror">
                @error('password')<p class="mt-1 text-xs font-bold text-rose-600"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>@enderror
            </label>

            <label class="flex items-start gap-2 text-xs text-slate-600">
                <input type="checkbox" name="understand" value="1" x-model="ack" required class="mt-0.5 rounded">
                <span>I understand exchange rates will be reset and I will update them immediately.</span>
            </label>

            <div class="flex gap-2 justify-end pt-2 border-t">
                <button type="button" @click="defOpen=false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-sm font-bold">Cancel</button>
                <button type="submit"
                    :disabled="!ack || !pwd || typed.toUpperCase() !== defCur.code.toUpperCase()"
                    :class="(!ack || !pwd || typed.toUpperCase() !== defCur.code.toUpperCase()) ? 'opacity-50 cursor-not-allowed' : ''"
                    class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold">
                    <i class="fa-solid fa-shield-halved mr-1"></i> Send OTP &amp; Continue
                </button>
            </div>
        </form>
    </div>


    {{-- OTP Step Modal --}}
    @php
        $otpCurId = session('otp_sent_for_currency');
        $otpCur   = $otpCurId ? $currencies->firstWhere('id', $otpCurId) : null;
    @endphp
    @if($otpCur)
    <div x-data="{ otpOpen:true, code:'' }" x-init="$nextTick(()=>$refs.otpInput && $refs.otpInput.focus())">
        <div x-show="otpOpen" x-cloak class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" @keydown.escape.window="otpOpen=false">
            <form method="POST" action="{{ route('admin.settings.currencies.default', $otpCur) }}" class="bg-white rounded-3xl w-full max-w-md p-6 space-y-4 shadow-2xl border-t-4 border-emerald-500">
                @csrf
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl"><i class="fa-solid fa-envelope-circle-check"></i></div>
                    <div>
                        <h3 class="text-lg font-black text-slate-800">Email verification</h3>
                        <p class="text-xs text-slate-500">Enter the 6-digit code we sent to your admin email.</p>
                    </div>
                </div>
                <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-100 text-xs text-emerald-700">
                    Setting <span class="font-black">{{ $otpCur->name }}</span> (<span class="font-mono">{{ $otpCur->code }}</span>) as base currency. Code expires in 10 minutes.
                </div>
                <label class="block text-sm">
                    <span class="text-xs font-bold text-slate-600">Verification code</span>
                    <input x-ref="otpInput" type="text" name="otp" x-model="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required
                        class="w-full h-12 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-center text-2xl font-mono tracking-[0.5em] focus:border-emerald-400 focus:ring-emerald-200">
                </label>
                <div class="flex gap-2 justify-end pt-2 border-t">
                    <button type="button" @click="otpOpen=false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-sm font-bold">Cancel</button>
                    <button type="submit" :disabled="code.length !== 6" :class="code.length!==6?'opacity-50 cursor-not-allowed':''"
                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold">
                        <i class="fa-solid fa-check mr-1"></i> Verify &amp; Apply
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
