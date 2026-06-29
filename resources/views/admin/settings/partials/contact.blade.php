<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500">Email</label>
        <div class="relative">
            <i class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input type="email" name="contact_email" value="{{ site_setting('contact_email') }}" class="w-full h-11 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
        </div>
    </div>
    <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500">Phone Number</label>
        <div class="relative">
            <i class="fa-solid fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input type="text" name="contact_phone" value="{{ site_setting('contact_phone') }}" class="w-full h-11 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
        </div>
    </div>
    <div class="space-y-2 md:col-span-2">
        <label class="text-xs font-bold text-slate-500">Address</label>
        <div class="relative">
            <i class="fa-solid fa-location-dot absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input type="text" name="contact_address" value="{{ site_setting('contact_address') }}" class="w-full h-11 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
        </div>
    </div>
    <div class="space-y-2 md:col-span-2">
        <label class="text-xs font-bold text-slate-500">Working Hours</label>
        <div class="relative">
            <i class="fa-solid fa-clock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input type="text" name="contact_hours" value="{{ site_setting('contact_hours') }}" placeholder="Sat - Thu: 9am - 9pm" class="w-full h-11 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
        </div>
    <div class="space-y-2 md:col-span-2">
        <label class="text-xs font-bold text-slate-500">Order ID Prefix</label>
        <div class="relative">
            <i class="fa-solid fa-hashtag absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input type="text" name="order_id_prefix" value="{{ site_setting('order_id_prefix', 'HZ-') }}" class="w-full h-11 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
        </div>
        <p class="text-xs text-slate-400 mt-1">Shown as text on the invoice when no logo is uploaded.</p>
    </div>
</div>
