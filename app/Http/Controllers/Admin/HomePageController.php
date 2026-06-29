<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomePageController extends Controller
{
    public function __construct(private ImageService $imageService)
    {
    }

    public function edit(): View
    {
        $keys = [
            'hero_title','hero_subtitle','hero_badge','hero_background',
            'featured_section_title','featured_section_subtitle',
            'products_section_title','products_section_subtitle',
            'cta_badge','cta_title','cta_subtitle','cta_button','cta_url',
            'cta_bg_image','cta_image_1','cta_image_2','cta_image_3','cta_image_4',
        ];
        $settings = SiteSetting::whereIn('key', $keys)->get()->keyBy('key');

        return view('admin.homepage.edit', compact('settings') + ['activeTab' => 'homepage']);
    }

    public function update(Request $request): RedirectResponse
    {
        $textKeys = [
            'hero_title','hero_subtitle','hero_badge',
            'featured_section_title','featured_section_subtitle','featured_limit',
            'products_section_title','products_section_subtitle','products_limit',
            'cta_badge','cta_title','cta_subtitle','cta_button','cta_url',
        ];

        foreach ($textKeys as $key) {
            $setting = SiteSetting::firstOrNew(['key' => $key]);
            $setting->value = $request->input($key);
            $setting->type = 'text';
            $setting->group = 'homepage';
            $setting->label = $setting->label ?: $this->label($key);
            $setting->save();
        }

        // Image fields (single file each, with optional removal).
        $imageKeys = ['hero_background', 'cta_bg_image', 'cta_image_1', 'cta_image_2', 'cta_image_3', 'cta_image_4'];
        foreach ($imageKeys as $key) {
            if ($request->hasFile($key)) {
                $setting = SiteSetting::firstOrNew(['key' => $key]);
                if ($setting->value) $this->imageService->deletePaths($setting->value);
                $setting->value = $this->imageService->storeSettingImage($request->file($key), $key);
                $setting->type = 'image';
                $setting->group = 'homepage';
                $setting->label = $setting->label ?: $this->label($key);
                $setting->save();
            } elseif ($request->has('remove_'.$key)) {
                $setting = SiteSetting::firstOrNew(['key' => $key]);
                if ($setting->value) $this->imageService->deletePaths($setting->value);
                $setting->value = null;
                $setting->save();
            }
        }

        SiteSetting::clearCache();

        return redirect()->route('admin.homepage.edit')->with('success', 'تم تحديث الصفحة الرئيسية بنجاح.');
    }

    private function label(string $key): string
    {
        return match ($key) {
            'hero_title' => 'عنوان Hero',
            'hero_subtitle' => 'نص Hero الفرعي',
            'hero_badge' => 'شارة Hero',
            'hero_background' => 'خلفية Hero',
            'featured_section_title' => 'عنوان قسم المنتجات المميزة',
            'featured_section_subtitle' => 'نص قسم المنتجات المميزة',
            'featured_limit' => 'عدد المنتجات المميزة',
            'products_section_title' => 'عنوان قسم جميع المنتجات',
            'products_section_subtitle' => 'نص قسم جميع المنتجات',
            'products_limit' => 'عدد المنتجات في الصفحة',
            'cta_badge' => 'شارة قسم الطلبات بالجملة',
            'cta_title' => 'عنوان قسم الطلبات بالجملة',
            'cta_subtitle' => 'وصف قسم الطلبات بالجملة',
            'cta_button' => 'نص الزر',
            'cta_url' => 'رابط الزر',
            'cta_bg_image' => 'خلفية قسم الطلبات بالجملة',
            'cta_image_1' => 'صورة 1 — معرض CTA',
            'cta_image_2' => 'صورة 2 — معرض CTA',
            'cta_image_3' => 'صورة 3 — معرض CTA',
            'cta_image_4' => 'صورة 4 — معرض CTA',
            default => $key,
        };
    }
}
