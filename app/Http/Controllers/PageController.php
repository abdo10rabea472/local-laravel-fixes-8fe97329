<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Page;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PageController extends Controller
{
    private const RESERVED_SLUGS = ['about', 'faqs', 'privacy-policy', 'returns-refunds', 'payment-success', 'checkout', 'contact', 'blog', 'offers'];

    public function show(string $slug): View
    {
        if (in_array($slug, self::RESERVED_SLUGS, true)) {
            abort(404);
        }

        $page = Page::bySlug($slug)->active()->firstOrFail();

        return view('pages.dynamic', [
            'page' => $page,
            'seo'  => $this->buildSeo($page, $page->title, (string) ($page->seo_description ?? '')),
        ]);
    }

    public function faqs(): View
    {
        $page = Page::bySlug('faqs')->active()->first();

        $defaultFaqs = [
            ['q' => 'How long does delivery take?', 'a' => 'Standard delivery within Cairo and Giza takes 2–4 business days. Delivery to other governorates usually takes 4–7 business days.'],
            ['q' => 'Do you ship to universities and laboratories?', 'a' => 'Yes! We offer special shipping and invoicing services for universities, research centers, and educational institutions across Egypt.'],
            ['q' => 'What payment methods do you accept?', 'a' => 'We accept Vodafone Cash, Fawry, credit/debit cards, and Cash on Delivery through secure encrypted gateways.'],
            ['q' => 'Do your products come with warranty?', 'a' => 'Most laboratory equipment comes with 1 to 3 years manufacturer warranty. Glassware and consumables are covered for manufacturing defects only.'],
            ['q' => 'Can I return an item if I changed my mind?', 'a' => 'Yes, you can return most items within 30 days if they are in original unused condition with all packaging.'],
            ['q' => 'Are the prices inclusive of VAT?', 'a' => 'All prices shown include VAT (14%). No additional tax will be added at checkout.'],
            ['q' => 'Do you provide technical support after purchase?', 'a' => 'Our technical team helps with installation, calibration, and usage via phone, WhatsApp, and email.'],
            ['q' => 'Can I get a quote for bulk orders?', 'a' => 'Yes. Send requirements to ' . (\App\Models\SiteSetting::get('contact_email') ?: 'ahmedkhamis@gmail.com') . ' and we will reply within 24 hours.'],
        ];

        // Prefer DB-managed FAQs (admin/faqs). Fall back to legacy page content / defaults.
        $dbFaqs = [];
        if (Schema::hasTable('faqs')) {
            $dbFaqs = Faq::query()
                ->where('active', true)
                ->orderBy('category')->orderBy('sort_order')->orderBy('id')
                ->get(['question', 'answer', 'category'])
                ->map(fn ($f) => ['q' => $f->question, 'a' => $f->answer, 'category' => $f->category ?: 'General'])
                ->all();
        }
        $faqs = !empty($dbFaqs) ? $dbFaqs : $this->parseFaqs($page?->content, $defaultFaqs);

        return view('pages.faqs', [
            'page' => $page,
            'faqs' => $faqs,
            'seo' => $this->buildSeo($page, 'FAQs | UNI-LAB MARKET', 'Frequently asked questions about ordering, shipping, returns, and payments.'),
        ]);
    }

    public function privacy(): View
    {
        $page = Page::bySlug('privacy-policy')->active()->first();

        return view('pages.privacy', [
            'page' => $page,
            'seo' => $this->buildSeo($page, 'Privacy Policy | UNI-LAB MARKET', 'How we collect, use, and protect your personal information.'),
        ]);
    }

    public function returns(): View
    {
        $page = Page::bySlug('returns-refunds')->active()->first();

        return view('pages.returns', [
            'page' => $page,
            'seo' => $this->buildSeo($page, 'Returns & Refunds | UNI-LAB MARKET', 'Our clear and fair return and refund policy.'),
        ]);
    }

    public function paymentSuccess(): View
    {
        $page = Page::bySlug('payment-success')->active()->first();

        return view('pages.payment-success', [
            'page' => $page,
            'seo' => $this->buildSeo($page, 'Order Confirmed | UNI-LAB MARKET', 'Your order was placed successfully.'),
        ]);
    }

    private function buildSeo(?Page $page, string $defaultTitle, string $defaultDescription): array
    {
        return [
            'seo_title' => $page?->seo_title ?: $defaultTitle,
            'seo_description' => $page?->seo_description ?: $defaultDescription,
            'seo_keywords' => $page?->seo_keywords ?? '',
            'canonical_url' => $page?->canonical_url ?: url()->current(),
            'og_title' => $page?->og_title ?: ($page?->seo_title ?: $defaultTitle),
            'og_description' => $page?->og_description ?: ($page?->seo_description ?: $defaultDescription),
            'og_image' => $page?->og_image_url ?: \App\Models\SiteSetting::getUrl('default_og_image'),
        ];
    }

    private function parseFaqs(?string $content, array $defaultFaqs): array
    {
        if (! $content) {
            return $defaultFaqs;
        }

        $decoded = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return $defaultFaqs;
    }
}
