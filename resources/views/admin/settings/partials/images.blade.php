<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    {{-- Site Logo --}}
    <div class="space-y-3 p-4 bg-slate-50 rounded-2xl border border-slate-100">
        <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_images_logo_label') }}</label>
        <input type="file" name="site_logo" accept="image/*" class="w-full text-sm">
        @if(site_setting_url('site_logo'))
        <div class="flex items-center gap-4 mt-2">
            <img src="{{ site_setting_url('site_logo') }}" alt="" class="h-12 w-auto object-contain bg-white rounded-lg p-1">
            <label class="flex items-center gap-2 text-sm text-rose-600 cursor-pointer">
                <input type="checkbox" name="remove_site_logo" value="1">
                {{ __('app.admin_settings_images_logo_remove') }}
            </label>
        </div>
        @endif
    </div>

    {{-- Hero Background --}}
    <div class="space-y-3 p-4 bg-slate-50 rounded-2xl border border-slate-100">
        <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_images_hero_label') }}</label>
        <input type="file" name="hero_background" accept="image/*" class="w-full text-sm">
        @if(site_setting_url('hero_background'))
        <div class="space-y-2 mt-2">
            <img src="{{ site_setting_url('hero_background') }}" alt="" class="h-24 w-auto object-cover rounded-lg">
            <label class="flex items-center gap-2 text-sm text-rose-600 cursor-pointer">
                <input type="checkbox" name="remove_hero_background" value="1">
                {{ __('app.admin_settings_images_hero_remove') }}
            </label>
        </div>
        @endif
    </div>

    {{-- Default Product Image --}}
    <div class="space-y-3 p-4 bg-slate-50 rounded-2xl border border-slate-100">
        <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_images_product_label') }}</label>
        <input type="file" name="default_product_image" accept="image/*" class="w-full text-sm">
        @if(site_setting_url('default_product_image'))
        <div class="space-y-2 mt-2">
            <img src="{{ site_setting_url('default_product_image') }}" alt="" class="h-24 w-24 object-contain bg-white rounded-lg p-1">
            <label class="flex items-center gap-2 text-sm text-rose-600 cursor-pointer">
                <input type="checkbox" name="remove_default_product_image" value="1">
                {{ __('app.admin_settings_images_product_remove') }}
            </label>
        </div>
        @endif
    </div>

    {{-- Default OG Image --}}
    <div class="space-y-3 p-4 bg-slate-50 rounded-2xl border border-slate-100">
        <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_images_og_label') }}</label>
        <input type="file" name="default_og_image" accept="image/*" class="w-full text-sm">
        @if(site_setting_url('default_og_image'))
        <div class="space-y-2 mt-2">
            <img src="{{ site_setting_url('default_og_image') }}" alt="" class="h-24 w-auto object-cover rounded-lg">
            <label class="flex items-center gap-2 text-sm text-rose-600 cursor-pointer">
                <input type="checkbox" name="remove_default_og_image" value="1">
                {{ __('app.admin_settings_images_og_remove') }}
            </label>
        </div>
        @endif
    </div>
</div>
