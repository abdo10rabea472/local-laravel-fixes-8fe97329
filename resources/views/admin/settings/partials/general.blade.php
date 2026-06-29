<div class="space-y-8">
    {{-- General Info --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
            <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_general_site_name') }}</label>
            <input type="text" name="site_name" value="{{ site_setting('site_name', 'UNI-LAB MARKET') }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
        </div>
        <div class="space-y-2">
            <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_general_primary_color') }}</label>
            <div class="flex items-center gap-3">
                <input type="color" name="primary_color" value="{{ site_setting('primary_color', '#6366f1') }}" class="h-11 w-16 rounded-xl border border-slate-200 bg-white p-1">
                <input type="text" name="primary_color_text" value="{{ site_setting('primary_color', '#6366f1') }}" class="flex-1 h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-mono">
            </div>
        </div>
        <div class="space-y-2 md:col-span-2">
            <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_general_welcome_message') }}</label>
            <textarea name="welcome_message" rows="3" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm">{{ site_setting('welcome_message') }}</textarea>
        </div>
    </div>

    {{-- Welcome Popup --}}
    <div class="border-t border-slate-100 pt-6">
        <h4 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
            <span class="h-6 w-1 rounded-full bg-rose-500"></span>
            {{ __('app.admin_settings_general_popup_section') }}
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2 md:col-span-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="welcome_popup_enabled" value="0">
                    <input type="checkbox" name="welcome_popup_enabled" value="1" {{ site_setting('welcome_popup_enabled', '1') == '1' ? 'checked' : '' }} class="rounded">
                    <span class="text-sm font-bold text-slate-700">{{ __('app.admin_settings_general_popup_enable') }}</span>
                </label>
            </div>

            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_general_popup_title') }}</label>
                <input type="text" name="welcome_popup_title" value="{{ site_setting('welcome_popup_title', 'Welcome to UNI-LAB MARKET') }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
            </div>

            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_general_popup_button_text') }}</label>
                <input type="text" name="welcome_popup_button_text" value="{{ site_setting('welcome_popup_button_text', 'Shop Now') }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
            </div>

            <div class="space-y-2 md:col-span-2">
                <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_general_popup_message') }}</label>
                <textarea name="welcome_popup_message" rows="3" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm">{{ site_setting('welcome_popup_message', 'Get an exclusive discount on your first order. Use the code below at checkout.') }}</textarea>
            </div>

            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_general_popup_discount_code') }}</label>
                <input type="text" name="welcome_popup_discount_code" value="{{ site_setting('welcome_popup_discount_code', 'WELCOME10') }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-mono">
            </div>

            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_general_popup_discount_percent') }}</label>
                <input type="number" min="0" max="100" name="welcome_popup_discount_percent" value="{{ site_setting('welcome_popup_discount_percent', '10') }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
            </div>

            <div class="space-y-2 md:col-span-2">
                <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_general_popup_image') }}</label>
                <input type="file" name="welcome_popup_image" accept="image/*" class="w-full text-sm">
                @if(site_setting_url('welcome_popup_image'))
                <div class="flex items-center gap-4 mt-2">
                    <img src="{{ site_setting_url('welcome_popup_image') }}" alt="" class="h-24 w-auto object-contain bg-white rounded-lg p-1 border border-slate-100">
                    <label class="flex items-center gap-2 text-sm text-rose-600 cursor-pointer">
                        <input type="checkbox" name="remove_welcome_popup_image" value="1">
                        {{ __('app.admin_settings_general_popup_remove_image') }}
                    </label>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
