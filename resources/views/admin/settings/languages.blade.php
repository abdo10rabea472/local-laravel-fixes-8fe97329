@extends('admin.settings.layout', ['activeTab' => 'languages'])

@section('title', __('app.admin_settings_languages_title'))

@section('settings-content')
<style>[x-cloak]{display:none !important;}</style>
<div class="space-y-6" x-data="{ open:false, edit:null, form:{}, defOpen:false, defLang:{id:null,code:'',name:'',url:''} }">

    @if(session('success'))<div class="p-4 rounded-2xl bg-emerald-50 text-emerald-700 text-sm font-bold border border-emerald-100">{{ session('success') }}</div>@endif
    @if(session('warning'))<div class="p-4 rounded-2xl bg-amber-50 text-amber-800 text-sm font-bold border border-amber-200"><i class="fa-solid fa-triangle-exclamation mr-2"></i>{{ session('warning') }}</div>@endif
    @if(session('error'))<div class="p-4 rounded-2xl bg-rose-50 text-rose-700 text-sm font-bold border border-rose-100"><i class="fa-solid fa-circle-xmark mr-2"></i>{{ session('error') }}</div>@endif
    @php $modalErrorKeys = ['password','confirm_code','understand']; $otherErrors = collect($errors->keys())->diff($modalErrorKeys); @endphp
    @if($otherErrors->count())<div class="p-4 rounded-2xl bg-rose-50 text-rose-700 text-sm border border-rose-100">{{ $errors->first($otherErrors->first()) }}</div>@endif

    <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">
        <div class="flex items-center justify-between p-6 border-b border-slate-100">
            <div>
                <h2 class="text-lg font-black text-slate-800">{{ __('app.admin_settings_languages_title') }}</h2>
                <p class="text-sm text-slate-500">{{ __('app.admin_settings_languages_subtitle') }}</p>
            </div>
            <button @click="open=true; edit=null; form={direction:'ltr', is_active:true, sort_order:({{ $languages->count() + 1 }})}"
                    class="px-5 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold shadow-md transition-all">
                <i class="fa-solid fa-plus mr-1"></i> {{ __('app.admin_settings_languages_add') }}
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                    <tr>
                        <th class="p-4 text-left">{{ __('app.admin_settings_languages_col_flag') }}</th>
                        <th class="p-4 text-left">{{ __('app.admin_settings_languages_col_name') }}</th>
                        <th class="p-4 text-left">{{ __('app.admin_settings_languages_col_native') }}</th>
                        <th class="p-4 text-left">{{ __('app.admin_settings_languages_col_code') }}</th>
                        <th class="p-4 text-left">{{ __('app.admin_settings_languages_col_locale') }}</th>
                        <th class="p-4 text-left">{{ __('app.admin_settings_languages_col_dir') }}</th>
                        <th class="p-4 text-left">{{ __('app.admin_settings_languages_col_default') }}</th>
                        <th class="p-4 text-left">{{ __('app.admin_settings_languages_col_active') }}</th>
                        <th class="p-4 text-left">{{ __('app.admin_settings_languages_col_order') }}</th>
                        <th class="p-4 text-right">{{ __('app.admin_settings_languages_col_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($languages as $lang)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4">
                            @if($lang->flag)
                                <img src="{{ asset('storage/'.$lang->flag) }}" alt="" class="w-8 h-6 object-cover rounded shadow-sm">
                            @else
                                <span class="text-slate-300"><i class="fa-solid fa-flag"></i></span>
                            @endif
                        </td>
                        <td class="p-4 font-bold text-slate-800">{{ $lang->name }}</td>
                        <td class="p-4" dir="auto">{{ $lang->native_name }}</td>
                        <td class="p-4 font-mono text-xs">{{ $lang->code }}</td>
                        <td class="p-4 font-mono text-xs">{{ $lang->locale }}</td>
                        <td class="p-4 uppercase text-xs font-bold">{{ $lang->direction }}</td>
                        <td class="p-4">
                            @if($lang->is_default)
                                <span class="px-2 py-1 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded-lg uppercase">{{ __('app.admin_settings_languages_badge_default') }}</span>
                            @else
                                <button type="button"
                                    @click="defOpen=true; defLang={id:{{ $lang->id }}, code:'{{ addslashes($lang->code) }}', name:'{{ addslashes($lang->name) }}', url:'{{ route('admin.settings.languages.default', $lang) }}'}"
                                    class="inline-flex items-center gap-1 text-[11px] font-bold text-violet-600 hover:text-violet-800">
                                    <i class="fa-solid fa-shield-halved"></i> {{ __('app.admin_settings_languages_make_default') }}
                                </button>
                            @endif
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-1 text-[10px] font-bold rounded-lg {{ $lang->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $lang->is_active ? __('app.admin_settings_languages_badge_active') : __('app.admin_settings_languages_badge_inactive') }}
                            </span>
                        </td>
                        <td class="p-4">{{ $lang->sort_order }}</td>
                        <td class="p-4 whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('admin.settings.languages.translations', $lang) }}" title="Translate" class="w-8 h-8 flex items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100"><i class="fa-solid fa-language"></i></a>
                                <a href="{{ route('admin.settings.languages.translations.export', $lang) }}" title="Export" class="w-8 h-8 flex items-center justify-center rounded-lg bg-sky-50 text-sky-600 hover:bg-sky-100"><i class="fa-solid fa-file-export"></i></a>
                                <button type="button" title="Import" @click="$dispatch('open-import', { id: {{ $lang->id }}, name: '{{ addslashes($lang->name) }}', url: '{{ route('admin.settings.languages.translations.import', $lang) }}' })" class="w-8 h-8 flex items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100"><i class="fa-solid fa-file-import"></i></button>
                                <button type="button" title="Edit" @click='open=true; edit={{ $lang->id }}; form=@json($lang)' class="w-8 h-8 flex items-center justify-center rounded-lg bg-violet-50 text-violet-600 hover:bg-violet-100"><i class="fa-solid fa-pen"></i></button>
                                @unless($lang->is_default)
                                <form method="POST" action="{{ route('admin.settings.languages.destroy', $lang) }}" onsubmit="return confirm('{{ __('app.admin_settings_languages_confirm_delete') }}')">
                                    @csrf @method('DELETE')
                                    <button title="Delete" class="w-8 h-8 flex items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100"><i class="fa-solid fa-trash"></i></button>
                                </form>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="p-8 text-center text-slate-400">{{ __('app.admin_settings_languages_empty') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Add/Edit Modal --}}
    <div x-show="open" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="open=false">
        <form method="POST" enctype="multipart/form-data" :action="edit ? '{{ url('admin/settings/languages') }}/' + edit : '{{ route('admin.settings.languages.store') }}'" class="bg-white rounded-3xl w-full max-w-2xl p-6 space-y-4 max-h-[90vh] overflow-y-auto shadow-2xl">
            @csrf
            <template x-if="edit">@method('PUT')</template>
            <h3 class="text-xl font-black text-slate-800" x-text="edit ? '{{ __('app.admin_settings_languages_modal_edit_title') }}' : '{{ __('app.admin_settings_languages_modal_add_title') }}'"></h3>

            <div class="grid grid-cols-2 gap-4">
                <label class="block text-sm"><span class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_languages_field_name') }}</span><input type="text" name="name" x-model="form.name" required class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm"></label>
                <label class="block text-sm"><span class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_languages_field_native_name') }}</span><input type="text" name="native_name" x-model="form.native_name" required class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm"></label>
                <label class="block text-sm"><span class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_languages_field_iso_code') }}</span><input type="text" name="code" x-model="form.code" required maxlength="10" class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono"></label>
                <label class="block text-sm"><span class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_languages_field_locale') }}</span><input type="text" name="locale" x-model="form.locale" required class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono"></label>
                <label class="block text-sm"><span class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_languages_field_direction') }}</span><select name="direction" x-model="form.direction" class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm"><option value="ltr">LTR</option><option value="rtl">RTL</option></select></label>
                <label class="block text-sm"><span class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_languages_field_sort_order') }}</span><input type="number" name="sort_order" x-model="form.sort_order" min="0" class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm"></label>
                <label class="block text-sm col-span-2"><span class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_languages_field_flag') }}</span><input type="file" name="flag_file" accept="image/*" class="w-full mt-2"></label>
            </div>

            <div class="flex items-center gap-6 py-2">
                <label class="flex items-center gap-2 text-sm font-bold"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" :checked="form.is_active" class="rounded"> {{ __('app.admin_settings_languages_checkbox_active') }}</label>
                <label class="flex items-center gap-2 text-sm font-bold"><input type="hidden" name="is_default" value="0"><input type="checkbox" name="is_default" value="1" :checked="form.is_default" class="rounded"> {{ __('app.admin_settings_languages_checkbox_set_default') }}</label>
            </div>

            <div class="flex gap-2 justify-end pt-4 border-t">
                <button type="button" @click="open=false" class="px-5 py-2 rounded-xl bg-slate-100 text-slate-700 text-sm font-bold">{{ __('app.admin_settings_languages_btn_cancel') }}</button>
                <button class="px-5 py-2 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold">{{ __('app.admin_settings_languages_btn_save') }}</button>
            </div>
        </form>
    </div>

    {{-- Import Modal --}}
    <div x-data="{ impOpen:false, impUrl:'', impName:'' }" @open-import.window="impOpen=true; impUrl=$event.detail.url; impName=$event.detail.name">
        <div x-show="impOpen" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="impOpen=false">
            <form method="POST" enctype="multipart/form-data" :action="impUrl" class="bg-white rounded-3xl w-full max-w-md p-6 space-y-4 shadow-2xl">
                @csrf
                <h3 class="text-lg font-black text-slate-800">{{ __('app.admin_settings_languages_import_title') }} — <span x-text="impName"></span></h3>
                <p class="text-xs text-slate-500">{{ __('app.admin_settings_languages_import_hint') }}</p>
                <label class="block text-sm"><span class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_languages_import_field_file') }}</span><input type="file" name="file" accept=".json,application/json" required class="w-full mt-1 text-sm"></label>
                <label class="block text-sm"><span class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_languages_import_field_mode') }}</span><select name="mode" class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm"><option value="merge">{{ __('app.admin_settings_languages_import_mode_merge') }}</option><option value="replace">{{ __('app.admin_settings_languages_import_mode_replace') }}</option></select></label>
                <div class="flex gap-2 justify-end pt-2">
                    <button type="button" @click="impOpen=false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-sm font-bold">{{ __('app.admin_settings_languages_btn_cancel_modal') }}</button>
                    <button class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold">{{ __('app.admin_settings_languages_import_btn') }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Secure Make-Default Modal --}}
    @php
        $errLangId = session('default_error_for_language');
        $errLang   = $errLangId ? $languages->firstWhere('id', $errLangId) : null;
    @endphp
    <div x-show="defOpen" x-cloak class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" @click.self="defOpen=false" @keydown.escape.window="defOpen=false"
        @if($errLang)
        x-init="defOpen=true; defLang={id:{{ $errLang->id }}, code:'{{ addslashes($errLang->code) }}', name:'{{ addslashes($errLang->name) }}', url:'{{ route('admin.settings.languages.default', $errLang) }}'}"
        @endif>
        <form method="POST" :action="defLang.url" x-data="{ typed:@js(old('confirm_code','')), pwd:'', ack:false }" class="bg-white rounded-3xl w-full max-w-md p-6 space-y-4 shadow-2xl border-t-4 border-rose-500">
            @csrf
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div>
                    <h3 class="text-lg font-black text-slate-800">{{ __('app.admin_settings_languages_high_risk_title') }}</h3>
                    <p class="text-xs text-slate-500">{{ __('app.admin_settings_languages_high_risk_subtitle') }}</p>
                </div>
            </div>

            <div class="p-3 rounded-xl bg-rose-50 border border-rose-100 text-xs text-rose-700">
                You are about to set <span class="font-black" x-text="defLang.name"></span> (<span class="font-mono" x-text="defLang.code"></span>) as the default language. This is logged with your IP and user agent.
            </div>

            <label class="block text-sm">
                <span class="text-xs font-bold text-slate-600">{{ __('app.admin_settings_languages_high_risk_type_code') }} <span class="font-mono text-rose-600" x-text="defLang.code"></span> {{ __('app.admin_settings_languages_high_risk_to_confirm') }}</span>
                <input type="text" name="confirm_code" x-model="typed" autocomplete="off" required
                    class="w-full h-10 px-3 mt-1 bg-slate-50 border rounded-xl text-sm font-mono focus:ring-2 @error('confirm_code') border-rose-400 ring-rose-200 @else border-slate-200 focus:border-rose-400 focus:ring-rose-200 @enderror">
                @error('confirm_code')<p class="mt-1 text-xs font-bold text-rose-600"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>@enderror
            </label>

            <label class="block text-sm">
                <span class="text-xs font-bold text-slate-600">{{ __('app.admin_settings_languages_high_risk_password') }}</span>
                <input type="password" name="password" x-model="pwd" autocomplete="current-password" required
                    class="w-full h-10 px-3 mt-1 bg-slate-50 border rounded-xl text-sm focus:ring-2 @error('password') border-rose-400 ring-rose-200 @else border-slate-200 focus:border-rose-400 focus:ring-rose-200 @enderror">
                @error('password')<p class="mt-1 text-xs font-bold text-rose-600"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>@enderror
            </label>

            <label class="flex items-start gap-2 text-xs text-slate-600">
                <input type="checkbox" name="understand" value="1" x-model="ack" required class="mt-0.5 rounded">
                <span>{{ __('app.admin_settings_languages_high_risk_understand') }}</span>
            </label>

            <div class="flex gap-2 justify-end pt-2 border-t">
                <button type="button" @click="defOpen=false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-sm font-bold">{{ __('app.admin_settings_languages_btn_cancel_modal') }}</button>
                <button type="submit"
                    :disabled="!ack || !pwd || typed !== defLang.code"
                    :class="(!ack || !pwd || typed !== defLang.code) ? 'opacity-50 cursor-not-allowed' : ''"
                    class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold">
                    <i class="fa-solid fa-shield-halved mr-1"></i> {{ __('app.admin_settings_languages_btn_confirm_default') }}
                </button>
            </div>
        </form>
    </div>

    {{-- OTP Step Modal --}}
    @php
        $otpLangId = session('otp_sent_for_language');
        $otpLang   = $otpLangId ? $languages->firstWhere('id', $otpLangId) : null;
    @endphp
    @if($otpLang)
    <div x-data="{ otpOpen:true, code:'' }" x-init="$nextTick(()=>$refs.otpInput && $refs.otpInput.focus())">
        <div x-show="otpOpen" x-cloak class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" @keydown.escape.window="otpOpen=false">
            <form method="POST" action="{{ route('admin.settings.languages.default', $otpLang) }}" class="bg-white rounded-3xl w-full max-w-md p-6 space-y-4 shadow-2xl border-t-4 border-emerald-500">
                @csrf
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl"><i class="fa-solid fa-envelope-circle-check"></i></div>
                    <div>
                        <h3 class="text-lg font-black text-slate-800">{{ __('app.admin_settings_languages_otp_title') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('app.admin_settings_languages_otp_subtitle') }}</p>
                    </div>
                </div>
                <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-100 text-xs text-emerald-700">
                    Setting <span class="font-black">{{ $otpLang->name }}</span> (<span class="font-mono">{{ $otpLang->code }}</span>) as default. {{ __('app.admin_settings_languages_otp_expires') }}
                </div>
                <label class="block text-sm">
                    <span class="text-xs font-bold text-slate-600">{{ __('app.admin_settings_languages_otp_field_code') }}</span>
                    <input x-ref="otpInput" type="text" name="otp" x-model="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required
                        class="w-full h-12 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-center text-2xl font-mono tracking-[0.5em] focus:border-emerald-400 focus:ring-emerald-200">
                </label>
                <div class="flex gap-2 justify-end pt-2 border-t">
                    <button type="button" @click="otpOpen=false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-sm font-bold">{{ __('app.admin_settings_languages_btn_cancel_modal') }}</button>
                    <button type="submit" :disabled="code.length !== 6" :class="code.length!==6?'opacity-50 cursor-not-allowed':''"
                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold">
                        <i class="fa-solid fa-check mr-1"></i> {{ __('app.admin_settings_languages_btn_verify') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
