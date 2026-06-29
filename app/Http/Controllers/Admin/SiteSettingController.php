<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function __construct(private ImageService $imageService)
    {
    }

    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'general');
        $allowedTabs = ['general', 'images', 'contact', 'ai', 'mail', 'seo'];

        if (! in_array($tab, $allowedTabs, true)) {
            $tab = 'general';
        }

        $settings = SiteSetting::orderBy('sort_order')->orderBy('id')->get()->keyBy('key');

        $meta = match ($tab) {
            'general' => ['title' => 'معلومات الموقع', 'subtitle' => 'اسم الموقع، اللون الأساسي، ورسالة الترحيب.'],
            'images' => ['title' => 'الصور', 'subtitle' => 'شعار الموقع، الخلفيات، والصور الافتراضية.'],
            'contact' => ['title' => 'معلومات التواصل', 'subtitle' => 'بيانات التواصل والعنوان.'],
            'ai' => ['title' => 'نماذج الذكاء الاصطناعي', 'subtitle' => 'أضف أي مزود AI متوافق مع OpenAI (Base URL + API Key + Model).'],
            'mail' => ['title' => 'إعدادات البريد (SMTP)', 'subtitle' => 'تُحفظ مباشرة في ملف .env وتُستخدم لإرسال البريد.'],
            'seo' => ['title' => 'تهيئة محركات البحث (SEO)', 'subtitle' => 'الفهرسة، خريطة الموقع، التحقق من جوجل، التحليلات، و robots.txt.'],
            default => ['title' => 'إعدادات الموقع', 'subtitle' => ''],
        };

        return view('admin.settings.index', [
            'settings' => $settings,
            'tab' => $tab,
            'activeTab' => $tab,
            'title' => $meta['title'],
            'subtitle' => $meta['subtitle'],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $tab = $request->get('tab', 'general');
        $imageKeys = ['site_logo', 'hero_background', 'default_product_image', 'default_og_image', 'welcome_popup_image'];

        if ($tab === 'mail') {
            $mailKeys = ['MAIL_MAILER','MAIL_HOST','MAIL_PORT','MAIL_ENCRYPTION','MAIL_USERNAME','MAIL_PASSWORD','MAIL_FROM_ADDRESS','MAIL_FROM_NAME'];
            $updates = [];
            foreach ($mailKeys as $k) {
                if ($request->has($k)) $updates[$k] = (string) $request->input($k, '');
            }
            $this->writeEnv($updates);
            \Artisan::call('config:clear');
            return redirect()->route('admin.settings.index', ['tab' => 'mail'])->with('success', 'تم حفظ إعدادات البريد في .env');
        }


        foreach ($request->except(['_token', '_method', 'tab']) as $key => $value) {
            $setting = SiteSetting::firstOrNew(['key' => $key]);

            if ($request->hasFile($key)) {
                if ($setting->value) {
                    $this->imageService->deletePaths($setting->value);
                }
                $setting->value = $this->imageService->storeSettingImage($request->file($key), $key);
                $setting->type = 'image';
            } elseif (in_array($key, $imageKeys, true) && $request->has("remove_{$key}")) {
                if ($setting->value) {
                    $this->imageService->deletePaths($setting->value);
                }
                $setting->value = null;
            } else {
                $setting->value = $value;
            }

            $setting->label = $setting->label ?: $this->defaultLabel($key);
            $setting->group = $setting->group ?: 'general';
            $setting->save();
        }

        SiteSetting::clearCache();

        return redirect()->route('admin.settings.index', ['tab' => $tab])->with('success', 'تم حفظ الإعدادات بنجاح.');
    }

    public function testAi(Request $request)
    {
        $data = $request->validate([
            'base_url' => ['required','url'],
            'api_key'  => ['required','string'],
            'model'    => ['required','string'],
        ]);

        try {
            $ai = new \App\Services\AiService($data['base_url'], $data['api_key'], $data['model']);
            $reply = $ai->chat([
                ['role' => 'user', 'content' => 'Reply with the single word: pong'],
            ], maxTokens: 16, temperature: 0.2, timeout: 25);

            return response()->json([
                'ok' => true,
                'message' => 'تم الاتصال بنجاح ✅ ('.($ai->isGemini() ? 'Gemini' : 'OpenAI-Compatible').')',
                'reply'   => mb_substr(trim($reply), 0, 200),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'فشل الاتصال',
                'error'   => $e->getMessage(),
            ], 200);
        }
    }

    private function defaultLabel(string $key): string
    {
        return match ($key) {
            'site_name' => 'اسم الموقع',
            'primary_color' => 'اللون الأساسي',
            'welcome_message' => 'رسالة الترحيب',
            'site_logo' => 'شعار الموقع',
            'hero_background' => 'خلفية الصفحة الرئيسية',
            'default_product_image' => 'صورة المنتج الافتراضية',
            'default_og_image' => 'صورة Open Graph الافتراضية',
            'contact_email' => 'البريد الإلكتروني للتواصل',
            'contact_phone' => 'رقم الهاتف',
            'contact_address' => 'العنوان',
            'order_id_prefix' => 'بادئة رقم الطلب',
            'welcome_popup_enabled' => 'تفعيل نموذج الترحيب',
            'welcome_popup_title' => 'عنوان نموذج الترحيب',
            'welcome_popup_message' => 'رسالة الترحيب',
            'welcome_popup_discount_code' => 'كود خصم الترحيب',
            'welcome_popup_discount_percent' => 'نسبة خصم الترحيب',
            'welcome_popup_button_text' => 'نص زر الترحيب',
            'welcome_popup_image' => 'صورة نموذج الترحيب',
            'ai_provider_name' => 'اسم مزود AI',
            'ai_base_url' => 'رابط الـ API (Base URL)',
            'ai_api_key' => 'مفتاح الـ API',
            'ai_model' => 'اسم النموذج',
            'ai_enabled' => 'تفعيل الذكاء الاصطناعي',
            default => $key,
        };
    }

    public function testMail(Request $request)
    {
        $data = $request->validate(['to' => ['required','email']]);
        try {
            \Mail::raw('رسالة اختبار من لوحة التحكم — إذا وصلتك فالإعدادات صحيحة.', function ($m) use ($data) {
                $m->to($data['to'])->subject('اختبار SMTP');
            });
            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 200);
        }
    }

    private function writeEnv(array $values): void
    {
        $path = base_path('.env');
        if (! is_file($path)) return;
        $content = file_get_contents($path);
        foreach ($values as $key => $val) {
            $escaped = (preg_match('/\s|#|"/', $val) || $val === '') ? '"'.addcslashes($val, '"\\').'"' : $val;
            $line = $key.'='.$escaped;
            if (preg_match("/^{$key}=.*$/m", $content)) {
                $content = preg_replace("/^{$key}=.*$/m", $line, $content);
            } else {
                $content .= PHP_EOL.$line;
            }
        }
        file_put_contents($path, $content);
    }
}

