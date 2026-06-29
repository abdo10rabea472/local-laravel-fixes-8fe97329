<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    {{-- Email --}}
    <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_contact_email') }}</label>
        <div class="relative">
            <i class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input type="email" name="contact_email" value="{{ site_setting('contact_email') }}" class="w-full h-11 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
        </div>
    </div>

    {{-- Phone Number --}}
    <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_contact_phone') }}</label>
        <div class="relative">
            <i class="fa-solid fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input type="text" name="contact_phone" value="{{ site_setting('contact_phone') }}" class="w-full h-11 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
        </div>
    </div>

    {{-- Address --}}
    <div class="space-y-2 md:col-span-2">
        <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_contact_address') }}</label>
        <div class="relative">
            <i class="fa-solid fa-location-dot absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input type="text" name="contact_address" value="{{ site_setting('contact_address') }}" class="w-full h-11 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
        </div>
    </div>

    {{-- Working Hours --}}
    <div class="space-y-2 md:col-span-2">
        <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_contact_hours') }}</label>
        <div class="relative">
            <i class="fa-solid fa-clock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input type="text" name="contact_hours" value="{{ site_setting('contact_hours') }}" placeholder="{{ __('app.admin_settings_contact_hours_placeholder') }}" class="w-full h-11 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
        </div>
    </div> {{-- قمت بإغلاق هذا القسم هنا --}}

    {{-- Order ID Prefix --}}
    <div class="space-y-2 md:col-span-2">
        <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_contact_order_prefix') }}</label>
        <div class="relative">
            <i class="fa-solid fa-hashtag absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input type="text" name="order_id_prefix" value="{{ site_setting('order_id_prefix', 'HZ-') }}" class="w-full h-11 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
        </div>
        <p class="text-xs text-slate-400 mt-1">{{ __('app.admin_settings_contact_order_prefix_hint') }}</p>
    </div>
</div>
