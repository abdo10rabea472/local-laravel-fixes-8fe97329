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

        // Single source of truth: faqs table (managed from /admin/faqs).
        $faqs = [];
        if (Schema::hasTable('faqs')) {
            $faqs = Faq::query()
                ->where('active', true)
                ->orderBy('category')->orderBy('sort_order')->orderBy('id')
                ->get(['question', 'answer', 'category'])
                ->map(fn ($f) => ['q' => $f->question, 'a' => $f->answer, 'category' => $f->category ?: 'General'])
                ->all();
        }

        // FAQPage JSON-LD for rich results
        $seo = $this->buildSeo($page, 'FAQs | UNI-LAB MARKET', 'Frequently asked questions about ordering, shipping, returns, and payments.');
        if (! empty($faqs)) {
            $seo['schema_markup'] = json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => array_map(fn ($f) => [
                    '@type' => 'Question',
                    'name' => $f['q'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => strip_tags((string) $f['a']),
                    ],
                ], $faqs),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return view('pages.faqs', [
            'page' => $page,
            'faqs' => $faqs,
            'seo' => $seo,
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
