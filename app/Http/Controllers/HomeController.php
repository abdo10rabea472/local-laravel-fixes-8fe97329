<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $featuredLimit = (int) SiteSetting::get('featured_limit', 8);
        $featuredProducts = Cache::remember("home.featured_products.{$featuredLimit}", 3600, function () use ($featuredLimit) {
            return Product::query()
                ->forListing()
                ->featured()
                ->active()
                ->limit($featuredLimit)
                ->get();
        });

        $query = Product::query()
            ->forListing()
            ->active();

        if ($request->filled('search')) {
            $search = (string) $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $query->orderByDesc('created_at')->paginate((int) SiteSetting::get('products_limit', 12));

        $mainCategories = Cache::remember('main_categories', 86400, function () {
            return Category::query()
                ->roots()
                ->active()
                ->orderBy('sort_order')
                ->select(['id', 'name', 'slug', 'image', 'banner', 'description', 'primary_color', 'secondary_color'])
                ->withCount(['children', 'products'])
                ->with(['children' => fn ($q) => $q->active()->orderBy('sort_order')->select(['id', 'parent_id', 'name', 'slug', 'image'])])
                ->get();
        });

        $realStats = Cache::remember('homepage_stats', 3600, function () {
            return [
                'products' => Product::active()->count(),
                'colleges' => Category::roots()->active()->count(),
                'departments' => Category::whereNotNull('parent_id')->active()->count(),
            ];
        });

        // Settings are served from SiteSetting's permanent cache (one query first
        // time, in-memory thereafter), so individual ::get() calls below are O(1).



        $hero = [
            'title' => \App\Models\SiteSetting::get('hero_title', 'Professional Tools for Future Professionals'),
            'subtitle' => \App\Models\SiteSetting::get('hero_subtitle', 'Your one-stop shop for premium educational equipment.'),
            'badge' => \App\Models\SiteSetting::get('hero_badge', 'Trusted by 10,000+ students'),
            'stat_products' => $realStats['products'] . '+',
            'stat_colleges' => $realStats['colleges'],
            'stat_departments' => $realStats['departments'] . '+',
            'background' => \App\Models\SiteSetting::getUrl('hero_background'),
        ];

        $homeSections = [
            'featured_title' => \App\Models\SiteSetting::get('featured_section_title', 'Top Picks for Students'),
            'featured_subtitle' => \App\Models\SiteSetting::get('featured_section_subtitle', 'Hand-picked products recommended for your studies'),
            'products_title' => \App\Models\SiteSetting::get('products_section_title', 'All Products'),
            'products_subtitle' => \App\Models\SiteSetting::get('products_section_subtitle'),
        ];

        $seo = [
            'seo_title' => 'UNI-LAB MARKET | Medical & Educational Equipment',
            'seo_description' => 'Your one-stop shop for premium educational and medical equipment.',
            'seo_keywords' => 'uni-lab, medical equipment, educational tools',
            'canonical_url' => url('/'),
            'og_title' => 'UNI-LAB MARKET',
            'og_description' => 'Premium educational and medical equipment store.',
            'og_image' => \App\Models\SiteSetting::getUrl('default_og_image', asset('imges/photo_٢٠٢٦-٠٢-٢٥_٠٨-٤٧-٣٧-removebg-preview.png')),
            'schema_markup' => null,
        ];

        return view('welcome', compact('products', 'featuredProducts', 'mainCategories', 'hero', 'realStats', 'seo', 'homeSections'));
    }
}
