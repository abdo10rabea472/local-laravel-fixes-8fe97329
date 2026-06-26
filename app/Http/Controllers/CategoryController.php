<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(Request $request, string $slug): View
    {
        $category = Cache::remember("category_page_{$slug}", 1800, function () use ($slug) {
            return Category::query()
                ->where('slug', $slug)
                ->active()
                ->with([
                    'parent' => fn ($q) => $q->select(['id', 'name', 'slug', 'primary_color', 'secondary_color', 'image', 'description', 'banner'])
                        ->with(['children' => fn ($c) => $c->active()
                            ->orderBy('sort_order')
                            ->select(['id', 'parent_id', 'name', 'slug', 'image', 'description'])
                            ->withCount(['products' => fn ($pq) => $pq->where('status', true)])]),
                    'children' => fn ($q) => $q->active()
                        ->orderBy('sort_order')
                        ->select(['id', 'parent_id', 'name', 'slug', 'image', 'description'])
                        ->withCount(['products' => fn ($pq) => $pq->where('status', true)]),
                    'sections' => fn ($q) => $q->active()->orderBy('sort_order'),
                ])
                ->firstOrFail();
        });

        $isCollege = $category->isCollege();
        $themeCategory = $isCollege ? $category : $category->parent;
        $departments = $isCollege ? $category->children : ($category->parent?->children ?? collect());

        $categoryIds = $isCollege
            ? ($category->children->pluck('id')->toArray() ?: [-1])
            : [$category->id];

        $productsQuery = Product::query()
            ->select(['id', 'name', 'slug', 'price', 'sale_price', 'stock', 'featured', 'category_id', 'short_description'])
            ->whereIn('category_id', $categoryIds)
            ->active()
            ->with([
                'category:id,name,slug',
                'images' => fn ($q) => $q->select(['id', 'product_id', 'thumb', 'medium', 'image'])->orderBy('sort_order'),
                'activeDiscount',
            ]);


        if ($request->filled('search')) {
            $search = $request->search;
            $productsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        match ($request->get('sort', 'newest')) {
            'price_asc' => $productsQuery->orderBy('price'),
            'price_desc' => $productsQuery->orderByDesc('price'),
            'name' => $productsQuery->orderBy('name'),
            default => $productsQuery->orderByDesc('created_at'),
        };

        $products = $productsQuery->paginate(12)->withQueryString();

        $seo = [
            'seo_title' => $category->seo_title,
            'seo_description' => $category->seo_description,
            'seo_keywords' => $category->seo_keywords,
            'canonical_url' => $category->canonical_url ?: url("/category/{$category->slug}"),
            'og_title' => $category->og_title ?: $category->seo_title,
            'og_description' => $category->og_description ?: $category->seo_description,
            'og_image' => $category->og_image ?: ($category->banner_url ?? $category->icon_url),
            'schema_markup' => $category->schema_markup,
        ];

        return view('category.show', compact(
            'category',
            'products',
            'seo',
            'themeCategory',
            'isCollege',
            'departments',
        ));
    }
}
