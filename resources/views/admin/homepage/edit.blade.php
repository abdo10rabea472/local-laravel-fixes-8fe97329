@extends('admin.settings.layout')

@section('settings-content')
<div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
    <div class="border-b border-slate-100 px-6 py-4">
        <h3 class="text-base font-bold text-slate-800">الصفحة الرئيسية</h3>
        <p class="text-xs text-slate-500 mt-1">تحكم في محتوى Hero والأقسام الرئيسية.</p>
    </div>

    <form method="POST" action="{{ route('admin.homepage.update') }}" enctype="multipart/form-data" class="p-6 space-y-8">
        @csrf
        @method('PUT')

        <div class="border-b border-slate-100 pb-6">
            <h4 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="h-6 w-1 rounded-full bg-emerald-500"></span>
                قسم Hero
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2 md:col-span-2">
                    <label class="text-xs font-bold text-slate-500">العنوان</label>
                    <input type="text" name="hero_title" value="{{ site_setting('hero_title', 'Professional Tools for Future Professionals') }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                </div>
                <div class="space-y-2 md:col-span-2">
                    <label class="text-xs font-bold text-slate-500">النص الفرعي</label>
                    <textarea name="hero_subtitle" rows="2" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm">{{ site_setting('hero_subtitle', 'Your one-stop shop for premium educational equipment.') }}</textarea>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500">الشارة (Badge)</label>
                    <input type="text" name="hero_badge" value="{{ site_setting('hero_badge', 'Trusted by 10,000+ students') }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500">خلفية Hero</label>
                    <input type="file" name="hero_background" accept="image/*" class="w-full text-sm">
                    @if(site_setting_url('hero_background'))
                    <div class="space-y-2 mt-2">
                        <img src="{{ site_setting_url('hero_background') }}" alt="" class="h-24 w-auto object-cover rounded-lg">
                        <label class="flex items-center gap-2 text-sm text-rose-600 cursor-pointer">
                            <input type="checkbox" name="remove_hero_background" value="1">
                            حذف الصورة
                        </label>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="border-b border-slate-100 pb-6">
            <h4 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="h-6 w-1 rounded-full bg-amber-500"></span>
                قسم المنتجات المميزة
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500">العنوان</label>
                    <input type="text" name="featured_section_title" value="{{ site_setting('featured_section_title', 'Top Picks for Students') }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500">النص الفرعي</label>
                    <input type="text" name="featured_section_subtitle" value="{{ site_setting('featured_section_subtitle', 'Hand-picked products recommended for your studies') }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500">عدد المنتجات المعروضة</label>
                    <input type="number" min="1" max="50" name="featured_limit" value="{{ site_setting('featured_limit', 8) }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                </div>
            </div>
        </div>

        <div>
            <h4 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="h-6 w-1 rounded-full bg-violet-500"></span>
                قسم جميع المنتجات
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500">العنوان</label>
                    <input type="text" name="products_section_title" value="{{ site_setting('products_section_title', 'All Products') }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500">النص الفرعي</label>
                    <input type="text" name="products_section_subtitle" value="{{ site_setting('products_section_subtitle') }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500">عدد المنتجات في الصفحة</label>
                    <input type="number" min="1" max="100" name="products_limit" value="{{ site_setting('products_limit', 12) }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                </div>
            </div>
        </div>




        <div class="flex justify-end pt-4 border-t border-slate-100">
            <button type="submit" class="h-11 px-8 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl transition-colors shadow-lg shadow-emerald-500/20">
                <i class="fa-solid fa-floppy-disk ml-2"></i> حفظ التغييرات
            </button>
        </div>
    </form>
</div>
@endsection
