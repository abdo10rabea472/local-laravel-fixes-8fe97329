@extends('admin.settings.layout')

@section('settings-content')
@php
    $heroLabels = [
        'hero_badge'             => ['ar' => 'شارة Hero', 'en' => 'Hero Badge'],
        'hero_title'             => ['ar' => 'العنوان الرئيسي', 'en' => 'Main Title'],
        'hero_subtitle'          => ['ar' => 'النص الفرعي', 'en' => 'Subtitle'],
        'hero_shop_all'          => ['ar' => 'زر "تسوّق الكل"', 'en' => '"Shop All" Button'],
        'hero_browse_colleges'   => ['ar' => 'زر "تصفّح الكليات"', 'en' => '"Browse Colleges" Button'],
        'hero_stat_products'     => ['ar' => 'تسمية إحصائية المنتجات', 'en' => 'Stat: Products label'],
        'hero_stat_colleges'     => ['ar' => 'تسمية إحصائية الكليات', 'en' => 'Stat: Colleges label'],
        'hero_stat_departments'  => ['ar' => 'تسمية إحصائية الأقسام', 'en' => 'Stat: Departments label'],
        'hero_card_microscopes'  => ['ar' => 'بطاقة المجاهر', 'en' => 'Card: Microscopes'],
        'hero_card_glassware'    => ['ar' => 'بطاقة الزجاجيات', 'en' => 'Card: Glassware'],
        'hero_card_medical'      => ['ar' => 'بطاقة الأطقم الطبية', 'en' => 'Card: Medical kits'],
        'hero_card_engineering'  => ['ar' => 'بطاقة الهندسة', 'en' => 'Card: Engineering'],
        'hero_card_subtitle'     => ['ar' => 'النص الفرعي للبطاقات', 'en' => 'Cards subtitle'],
    ];
    $longKeys = ['hero_title','hero_subtitle'];
@endphp

<div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
    <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
        <div>
            <h3 class="text-base font-bold text-slate-800">{{ __('app.admin_homepage_title') }}</h3>
            <p class="text-xs text-slate-500 mt-1">{{ __('app.admin_homepage_subtitle') }}</p>
        </div>
        @if(session('success'))
            <span class="text-xs font-bold text-emerald-600 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-full">
                <i class="fa-solid fa-check"></i> {{ session('success') }}
            </span>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.homepage.update') }}" enctype="multipart/form-data" class="p-6 space-y-8">
        @csrf
        @method('PUT')

        {{-- ════════ HERO SECTION ════════ --}}
        <div class="border border-slate-200 rounded-2xl overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-50 to-violet-50 px-5 py-4 border-b border-slate-200 flex items-center gap-3">
                <span class="h-8 w-8 rounded-xl bg-violet-600 text-white flex items-center justify-center text-sm">
                    <i class="fa-solid fa-bullhorn"></i>
                </span>
                <div>
                    <h4 class="text-sm font-bold text-slate-800">{{ __('app.admin_homepage_hero_section_title') }}</h4>
                    <p class="text-[11px] text-slate-500">{{ __('app.admin_homepage_hero_section_desc') }} <code class="text-[10px] bg-white px-1.5 py-0.5 rounded">resources/lang/{ar,en}/home.php</code></p>
                </div>
            </div>

            {{-- Language tabs --}}
            <div x-data="{ tab: 'en' }" class="p-5">
                <div class="flex gap-2 mb-5">
                    <button type="button" @click="tab='en'" :class="tab==='en' ? 'bg-violet-600 text-white shadow' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2">
                        <span>🇬🇧</span> English
                    </button>
                    <button type="button" @click="tab='ar'" :class="tab==='ar' ? 'bg-violet-600 text-white shadow' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2">
                        <span>🇸🇦</span> العربية
                    </button>
                </div>

                @foreach($locales as $locale)
                    <div x-show="tab==='{{ $locale }}'" x-cloak {{ $locale === 'ar' ? 'dir=rtl' : 'dir=ltr' }}>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($heroKeys as $key)
                                @php
                                    $isLong = in_array($key, $longKeys);
                                    $val = $heroTranslations[$locale][$key] ?? '';
                                    $label = $heroLabels[$key][$locale] ?? $key;
                                @endphp
                                <div class="space-y-1.5 {{ $isLong ? 'md:col-span-2' : '' }}">
                                    <label class="text-xs font-bold text-slate-600 flex items-center justify-between">
                                        <span>{{ $label }}</span>
                                        <code class="text-[10px] font-mono text-slate-400 bg-slate-50 px-1.5 py-0.5 rounded">{{ $key }}</code>
                                    </label>
                                    @if($isLong)
                                        <textarea name="hero[{{ $locale }}][{{ $key }}]" rows="2"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 focus:border-violet-500 focus:ring-2 focus:ring-violet-100 rounded-xl text-sm transition">{{ $val }}</textarea>
                                    @else
                                        <input type="text" name="hero[{{ $locale }}][{{ $key }}]" value="{{ $val }}"
                                            class="w-full h-11 px-4 bg-white border border-slate-200 focus:border-violet-500 focus:ring-2 focus:ring-violet-100 rounded-xl text-sm transition">
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Hero background image --}}
            <div class="border-t border-slate-200 bg-slate-50/50 px-5 py-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-600">{{ __('app.admin_homepage_hero_bg_label') }}</label>
                        <input type="file" name="hero_background" accept="image/*" class="w-full text-sm file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-violet-100 file:text-violet-700 file:font-bold">
                    </div>
                    @if(site_setting_url('hero_background'))
                        <div class="flex items-center gap-4">
                            <img src="{{ site_setting_url('hero_background') }}" alt="" class="h-20 w-32 object-cover rounded-lg border border-slate-200">
                            <label class="flex items-center gap-2 text-xs font-bold text-rose-600 cursor-pointer">
                                <input type="checkbox" name="remove_hero_background" value="1" class="rounded">
                                {{ __('app.admin_homepage_remove_image') }}
                            </label>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ════════ FEATURED SECTION ════════ --}}
        <div class="border border-slate-200 rounded-2xl p-5">
            <h4 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="h-8 w-8 rounded-xl bg-amber-500 text-white flex items-center justify-center text-sm"><i class="fa-solid fa-star"></i></span>
                قسم المنتجات المميزة
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-600">{{ __('app.admin_homepage_label_title') }}</label>
                    <input type="text" name="featured_section_title" value="{{ site_setting('featured_section_title', 'Top Picks for Students') }}" class="w-full h-11 px-4 bg-white border border-slate-200 rounded-xl text-sm">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-600">{{ __('app.admin_homepage_label_subtitle') }}</label>
                    <input type="text" name="featured_section_subtitle" value="{{ site_setting('featured_section_subtitle') }}" class="w-full h-11 px-4 bg-white border border-slate-200 rounded-xl text-sm">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-600">{{ __('app.admin_homepage_products_count') }}</label>
                    <input type="number" min="1" max="50" name="featured_limit" value="{{ site_setting('featured_limit', 8) }}" class="w-full h-11 px-4 bg-white border border-slate-200 rounded-xl text-sm">
                </div>
            </div>
        </div>

        {{-- ════════ ALL PRODUCTS SECTION ════════ --}}
        <div class="border border-slate-200 rounded-2xl p-5">
            <h4 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="h-8 w-8 rounded-xl bg-violet-500 text-white flex items-center justify-center text-sm"><i class="fa-solid fa-grip"></i></span>
                {{ __('app.admin_homepage_all_products_section') }}
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-600">{{ __('app.admin_homepage_label_title') }}</label>
                    <input type="text" name="products_section_title" value="{{ site_setting('products_section_title', 'All Products') }}" class="w-full h-11 px-4 bg-white border border-slate-200 rounded-xl text-sm">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-600">{{ __('app.admin_homepage_label_subtitle') }}</label>
                    <input type="text" name="products_section_subtitle" value="{{ site_setting('products_section_subtitle') }}" class="w-full h-11 px-4 bg-white border border-slate-200 rounded-xl text-sm">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-600">{{ __('app.admin_homepage_products_per_page') }}</label>
                    <input type="number" min="1" max="100" name="products_limit" value="{{ site_setting('products_limit', 12) }}" class="w-full h-11 px-4 bg-white border border-slate-200 rounded-xl text-sm">
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-4 border-t border-slate-100">
            <button type="submit" class="h-11 px-8 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl transition-colors shadow-lg shadow-emerald-500/20">
                <i class="fa-solid fa-floppy-disk ml-2"></i> {{ __('app.admin_homepage_save') }}
            </button>
        </div>
    </form>
</div>
@endsection
