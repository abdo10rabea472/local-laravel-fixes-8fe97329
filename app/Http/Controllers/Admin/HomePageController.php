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
    /**
     * Hero translation keys editable from the admin (per language).
     * label => translation key inside resources/lang/{locale}/home.php
     */
    private const HERO_TRANSLATION_KEYS = [
        'hero_badge',
        'hero_title',
        'hero_subtitle',
        'hero_shop_all',
        'hero_browse_colleges',
        'hero_stat_products',
        'hero_stat_colleges',
        'hero_stat_departments',
        'hero_card_microscopes',
        'hero_card_glassware',
        'hero_card_medical',
        'hero_card_engineering',
        'hero_card_subtitle',
    ];

    private const LOCALES = ['en', 'ar'];

    public function __construct(private ImageService $imageService)
    {
    }

    public function edit(): View
    {
        $keys = ['hero_background',
            'featured_section_title','featured_section_subtitle','featured_limit',
            'products_section_title','products_section_subtitle','products_limit',
        ];
        $settings = SiteSetting::whereIn('key', $keys)->get()->keyBy('key');

        $heroTranslations = [];
        foreach (self::LOCALES as $locale) {
            $heroTranslations[$locale] = $this->loadHomeLang($locale);
        }

        return view('admin.homepage.edit', [
            'settings'         => $settings,
            'activeTab'        => 'homepage',
            'heroKeys'         => self::HERO_TRANSLATION_KEYS,
            'heroTranslations' => $heroTranslations,
            'locales'          => self::LOCALES,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        // 1) Hero translation keys → write into resources/lang/{locale}/home.php
        $payload = $request->input('hero', []);
        foreach (self::LOCALES as $locale) {
            $current = $this->loadHomeLang($locale);
            foreach (self::HERO_TRANSLATION_KEYS as $key) {
                if (isset($payload[$locale][$key])) {
                    $current[$key] = (string) $payload[$locale][$key];
                }
            }
            $this->saveHomeLang($locale, $current);
        }

        // 2) Plain site_setting text values (non-hero)
        $textKeys = [
            'featured_section_title','featured_section_subtitle','featured_limit',
            'products_section_title','products_section_subtitle','products_limit',
        ];
        foreach ($textKeys as $key) {
            $setting = SiteSetting::firstOrNew(['key' => $key]);
            $setting->value = $request->input($key);
            $setting->type = 'text';
            $setting->group = 'homepage';
            $setting->label = $setting->label ?: $key;
            $setting->save();
        }

        // 3) Hero background image
        $imageKeys = ['hero_background'];
        foreach ($imageKeys as $key) {
            if ($request->hasFile($key)) {
                $setting = SiteSetting::firstOrNew(['key' => $key]);
                if ($setting->value) $this->imageService->deletePaths($setting->value);
                $setting->value = $this->imageService->storeSettingImage($request->file($key), $key);
                $setting->type = 'image';
                $setting->group = 'homepage';
                $setting->label = $setting->label ?: $key;
                $setting->save();
            } elseif ($request->has('remove_'.$key)) {
                $setting = SiteSetting::firstOrNew(['key' => $key]);
                if ($setting->value) $this->imageService->deletePaths($setting->value);
                $setting->value = null;
                $setting->save();
            }
        }

        SiteSetting::clearCache();
        if (function_exists('opcache_reset')) { @opcache_reset(); }

        return redirect()->route('admin.homepage.edit')->with('success', 'تم تحديث الصفحة الرئيسية بنجاح.');
    }

    private function langPath(string $locale): string
    {
        return resource_path("lang/{$locale}/home.php");
    }

    private function loadHomeLang(string $locale): array
    {
        $path = $this->langPath($locale);
        if (!file_exists($path)) return [];
        $data = include $path;
        return is_array($data) ? $data : [];
    }

    private function saveHomeLang(string $locale, array $data): void
    {
        $path = $this->langPath($locale);
        $export = "<?php\n\nreturn " . $this->varExportShort($data, 0) . ";\n";
        file_put_contents($path, $export);
    }

    private function varExportShort($value, int $indent = 0): string
    {
        if (is_array($value)) {
            $pad = str_repeat('    ', $indent);
            $padInner = str_repeat('    ', $indent + 1);
            $lines = [];
            $isList = array_keys($value) === range(0, count($value) - 1);
            foreach ($value as $k => $v) {
                $line = $padInner;
                if (!$isList) {
                    $line .= var_export($k, true) . ' => ';
                }
                $line .= $this->varExportShort($v, $indent + 1);
                $lines[] = $line . ',';
            }
            return "[\n" . implode("\n", $lines) . "\n{$pad}]";
        }
        return var_export($value, true);
    }
}
