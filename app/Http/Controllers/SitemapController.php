<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [];
        $now = now()->toAtomString();

        $add = function (string $loc, ?string $lastmod = null, string $changefreq = 'weekly', string $priority = '0.7') use (&$urls) {
            $urls[] = compact('loc','lastmod','changefreq','priority');
        };

        // Static
        $add(url('/'), $now, 'daily', '1.0');
        $add(route('products.index'), $now, 'daily', '0.9');
        $add(route('blog.index'), $now, 'daily', '0.7');
        $add(route('pages.faqs'), $now, 'monthly', '0.5');
        try { $add(route('about'), $now, 'monthly', '0.5'); } catch (\Throwable $e) {}

        // Products
        if (\Schema::hasTable('products')) {
            Product::query()
                ->when(\Schema::hasColumn('products','status'), fn($q) => $q->where('status', 1))
                ->select(['slug','updated_at'])->orderByDesc('updated_at')->limit(5000)
                ->get()->each(function ($p) use ($add) {
                    if ($p->slug) $add(route('product.show', $p->slug), optional($p->updated_at)?->toAtomString(), 'weekly', '0.8');
                });
        }

        // Categories
        if (\Schema::hasTable('categories')) {
            Category::query()->select(['slug','updated_at'])->orderByDesc('updated_at')->limit(2000)
                ->get()->each(function ($c) use ($add) {
                    if ($c->slug) $add(route('category.show', $c->slug), optional($c->updated_at)?->toAtomString(), 'weekly', '0.7');
                });
        }

        // Blog posts
        if (\Schema::hasTable('blog_posts')) {
            BlogPost::query()
                ->when(\Schema::hasColumn('blog_posts','published_at'), fn($q) => $q->whereNotNull('published_at'))
                ->select(['slug','updated_at'])->orderByDesc('updated_at')->limit(5000)
                ->get()->each(function ($b) use ($add) {
                    if ($b->slug) $add(route('blog.show', $b->slug), optional($b->updated_at)?->toAtomString(), 'weekly', '0.6');
                });
        }

        // Static pages
        if (\Schema::hasTable('pages')) {
            Page::query()
                ->when(\Schema::hasColumn('pages','is_active'), fn($q) => $q->where('is_active', 1))
                ->select(['slug','updated_at'])->limit(500)
                ->get()->each(function ($p) use ($add) {
                    if ($p->slug) $add(route('pages.show', $p->slug), optional($p->updated_at)?->toAtomString(), 'monthly', '0.5');
                });
        }

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as $u) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>".htmlspecialchars($u['loc'], ENT_XML1)."</loc>\n";
            if (!empty($u['lastmod'])) $xml .= "    <lastmod>{$u['lastmod']}</lastmod>\n";
            $xml .= "    <changefreq>{$u['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$u['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $custom = trim((string) site_setting('robots_txt_content', ''));
        if ($custom === '') {
            $custom = "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /account\nDisallow: /cart\nDisallow: /checkout\n";
        }
        $custom .= "\nSitemap: ".url('/sitemap.xml')."\n";
        return response($custom, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function pingGoogle()
    {
        try {
            $res = Http::timeout(10)->get('https://www.google.com/ping', [
                'sitemap' => url('/sitemap.xml'),
            ]);
            return response()->json(['ok' => $res->successful(), 'status' => $res->status()]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 200);
        }
    }
}
