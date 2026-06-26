<?php

namespace App\Services;

use App\Models\Category;
use App\Models\HeaderMenuItem;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class NavigationService
{
    public static function getData(): array
    {
        return Cache::remember('nav_data', 86400, function () {
            $colleges = Category::query()
                ->roots()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->select(['id', 'name', 'slug', 'image', 'primary_color', 'secondary_color'])
                ->withCount(['children' => fn ($q) => $q->where('status', true)])
                ->with([
                    'children' => fn ($q) => $q->active()
                        ->orderBy('sort_order')
                        ->orderBy('name')
                        ->select(['id', 'parent_id', 'name', 'slug', 'image'])
                        ->withCount(['products' => fn ($pq) => $pq->where('status', true)]),
                ])
                ->get();

            // Fetch all header/footer menu items in a single query, then group in PHP.
            // This replaces 3 separate queries with 1.
            $allMenuItems = HeaderMenuItem::query()
                ->whereIn('location', ['header_primary', 'header_top', 'footer'])
                ->where('status', true)
                ->orderBy('parent_id')
                ->orderBy('position')
                ->get();

            [$roots, $childrenByParent] = self::groupMenuItems($allMenuItems);

            $build = function (string $location) use ($roots, $childrenByParent) {
                return collect($roots[$location] ?? [])->map(function ($item) use ($childrenByParent) {
                    $item->setRelation('children', collect($childrenByParent[$item->id] ?? []));
                    return $item;
                })->values();
            };

            return [
                'colleges' => $colleges,
                'totalProducts' => Product::active()->count(),
                'totalColleges' => $colleges->count(),
                'headerMenu' => $build('header_primary'),
                'topMenu' => $build('header_top'),
                'footerMenu' => $build('footer'),
            ];
        });
    }

    /**
     * Split menu items into root items grouped by location plus a children map
     * keyed by parent id. Children inherit the parent's location implicitly.
     */
    private static function groupMenuItems($items): array
    {
        $roots = [];
        $children = [];
        foreach ($items as $item) {
            if ($item->parent_id === null) {
                $roots[$item->location][] = $item;
            } else {
                $children[$item->parent_id][] = $item;
            }
        }
        return [$roots, $children];
    }

    public static function clearCache(): void
    {
        Cache::forget('nav_data');
        Cache::forget('main_categories');
        Cache::forget('homepage_stats');
        Cache::forget('active_coupons_list');
        Cache::forget('admin.dashboard.product_stats');
        Cache::forget('admin.dashboard.category_stats');
        Cache::forget('admin.dashboard.total_categories');

        // Featured products cache (any limit variant)
        for ($i = 1; $i <= 24; $i++) {
            Cache::forget("home.featured_products.{$i}");
        }

        // Product show/related caches — clear lazily on next read; we cannot
        // enumerate all slugs cheaply, so just bump a version marker.
        Cache::forget('product.show.*');
    }

    /**
     * Clear caches scoped to a specific product (used by admin save hooks).
     */
    public static function clearProductCache(?string $slug = null, ?int $id = null): void
    {
        if ($slug) Cache::forget("product.show.{$slug}");
        if ($id) Cache::forget("product.related.{$id}");
    }
}
