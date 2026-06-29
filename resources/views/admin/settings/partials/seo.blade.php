<div class="space-y-8" x-data="{ pingStatus:'', pinging:false }">

    {{-- Quick links / status --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ url('/sitemap.xml') }}" target="_blank"
           class="group p-4 rounded-2xl border border-slate-200 bg-slate-50 hover:border-violet-300 hover:bg-white transition flex items-center gap-3">
            <div class="h-10 w-10 rounded-xl bg-violet-100 text-violet-600 flex items-center justify-center"><i class="fa-solid fa-sitemap"></i></div>
            <div class="min-w-0">
                <div class="text-sm font-bold text-slate-800">sitemap.xml</div>
                <div class="text-[11px] text-slate-500 truncate">{{ url('/sitemap.xml') }}</div>
            </div>
        </a>
        <a href="{{ url('/robots.txt') }}" target="_blank"
           class="group p-4 rounded-2xl border border-slate-200 bg-slate-50 hover:border-violet-300 hover:bg-white transition flex items-center gap-3">
            <div class="h-10 w-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center"><i class="fa-solid fa-robot"></i></div>
            <div class="min-w-0">
                <div class="text-sm font-bold text-slate-800">robots.txt</div>
                <div class="text-[11px] text-slate-500 truncate">{{ url('/robots.txt') }}</div>
            </div>
        </a>
        <button type="button"
                @click="pinging=true; pingStatus=''; fetch('{{ route('admin.settings.seo.ping') }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}}).then(r=>r.json()).then(d=>{ pingStatus = d.ok ? '✅ تم إخطار جوجل بنجاح' : '⚠ تعذّر إخطار جوجل'; }).catch(()=>{ pingStatus='⚠ خطأ في الاتصال'; }).finally(()=>{ pinging=false; })"
                class="p-4 rounded-2xl border border-slate-200 bg-slate-50 hover:border-violet-300 hover:bg-white transition flex items-center gap-3 text-left">
            <div class="h-10 w-10 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center">
                <i class="fa-brands fa-google" x-show="!pinging"></i>
                <i class="fa-solid fa-spinner fa-spin" x-show="pinging" x-cloak></i>
            </div>
            <div class="min-w-0">
                <div class="text-sm font-bold text-slate-800">إخطار جوجل بالخريطة</div>
                <div class="text-[11px] text-slate-500" x-text="pingStatus || 'يرسل ping إلى google.com/ping'"></div>
            </div>
        </button>
    </div>

    {{-- Default SEO meta --}}
    <div class="border-t border-slate-100 pt-6">
        <h4 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
            <span class="h-6 w-1 rounded-full bg-violet-500"></span>
            بيانات SEO الافتراضية للموقع
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2 md:col-span-2">
                <label class="text-xs font-bold text-slate-500">العنوان الافتراضي (Meta Title)</label>
                <input type="text" name="seo_meta_title_default" maxlength="70"
                       value="{{ site_setting('seo_meta_title_default', site_setting('site_name','UNI-LAB MARKET')) }}"
                       class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                <p class="text-[11px] text-slate-400">≤ 60 حرف موصى به</p>
            </div>
            <div class="space-y-2 md:col-span-2">
                <label class="text-xs font-bold text-slate-500">الوصف الافتراضي (Meta Description)</label>
                <textarea name="seo_meta_description_default" rows="3" maxlength="200"
                          class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm">{{ site_setting('seo_meta_description_default') }}</textarea>
                <p class="text-[11px] text-slate-400">≤ 160 حرف موصى به</p>
            </div>
            <div class="space-y-2 md:col-span-2">
                <label class="text-xs font-bold text-slate-500">الكلمات المفتاحية الافتراضية</label>
                <input type="text" name="seo_meta_keywords_default"
                       value="{{ site_setting('seo_meta_keywords_default') }}"
                       class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm"
                       placeholder="ecommerce, lab, science, ...">
            </div>
        </div>
    </div>

    {{-- Verification & analytics --}}
    <div class="border-t border-slate-100 pt-6">
        <h4 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
            <span class="h-6 w-1 rounded-full bg-emerald-500"></span>
            التحقق والتحليلات
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500">Google Search Console — رمز التحقق</label>
                <input type="text" name="google_site_verification"
                       value="{{ site_setting('google_site_verification') }}"
                       placeholder="abcDEF123..."
                       class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-mono">
                <p class="text-[11px] text-slate-400">قيمة الـ <code>content</code> فقط من وسم <code>google-site-verification</code>.</p>
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500">Bing Webmaster — رمز التحقق</label>
                <input type="text" name="bing_site_verification"
                       value="{{ site_setting('bing_site_verification') }}"
                       class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-mono">
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500">Google Analytics 4 — Measurement ID</label>
                <input type="text" name="google_analytics_id"
                       value="{{ site_setting('google_analytics_id') }}"
                       placeholder="G-XXXXXXXXXX"
                       class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-mono">
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500">Google Tag Manager — Container ID</label>
                <input type="text" name="google_tag_manager_id"
                       value="{{ site_setting('google_tag_manager_id') }}"
                       placeholder="GTM-XXXXXX"
                       class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-mono">
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500">Facebook Pixel ID</label>
                <input type="text" name="facebook_pixel_id"
                       value="{{ site_setting('facebook_pixel_id') }}"
                       class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-mono">
            </div>
        </div>
    </div>

    {{-- Indexing toggles --}}
    <div class="border-t border-slate-100 pt-6">
        <h4 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
            <span class="h-6 w-1 rounded-full bg-amber-500"></span>
            الفهرسة (Indexing)
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer">
                <input type="hidden" name="seo_indexing_enabled" value="0">
                <input type="checkbox" name="seo_indexing_enabled" value="1"
                       {{ site_setting('seo_indexing_enabled','1') == '1' ? 'checked' : '' }} class="rounded">
                <div>
                    <div class="text-sm font-bold text-slate-700">السماح بفهرسة الموقع</div>
                    <div class="text-[11px] text-slate-500">عند الإيقاف يُضاف <code>noindex, nofollow</code> لكل الصفحات.</div>
                </div>
            </label>
            <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer">
                <input type="hidden" name="sitemap_enabled" value="0">
                <input type="checkbox" name="sitemap_enabled" value="1"
                       {{ site_setting('sitemap_enabled','1') == '1' ? 'checked' : '' }} class="rounded">
                <div>
                    <div class="text-sm font-bold text-slate-700">تفعيل خريطة الموقع التلقائية</div>
                    <div class="text-[11px] text-slate-500">تشمل المنتجات، التصنيفات، المقالات والصفحات.</div>
                </div>
            </label>
        </div>
    </div>

    {{-- robots.txt editor --}}
    <div class="border-t border-slate-100 pt-6">
        <h4 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
            <span class="h-6 w-1 rounded-full bg-rose-500"></span>
            محتوى robots.txt
        </h4>
        <div class="space-y-2">
            <textarea name="robots_txt_content" rows="8"
                      class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-mono leading-relaxed"
                      placeholder="User-agent: *&#10;Allow: /&#10;Disallow: /admin">{{ site_setting('robots_txt_content', "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /account\nDisallow: /cart\nDisallow: /checkout") }}</textarea>
            <p class="text-[11px] text-slate-400">سيتم إلحاق سطر <code>Sitemap:</code> تلقائياً.</p>
        </div>
    </div>
</div>
