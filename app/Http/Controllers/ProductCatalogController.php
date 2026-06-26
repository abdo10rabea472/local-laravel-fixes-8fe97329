<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $colleges = Category::query()
            ->roots()
            ->active()
            ->orderBy('sort_order')
            ->select(['id', 'name', 'slug', 'image', 'primary_color', 'secondary_color'])
            ->withCount('children')
            ->with(['children' => fn ($q) => $q->active()
                ->orderBy('sort_order')
                ->select(['id', 'parent_id', 'name', 'slug'])
                ->withCount(['products' => fn ($pq) => $pq->where('status', true)])])
            ->get();

        $activeCollege = $request->filled('college')
            ? $colleges->firstWhere('slug', $request->college)
            : null;

        $activeDepartment = null;
        if ($activeCollege && $request->filled('department')) {
            $activeDepartment = $activeCollege->children->firstWhere('slug', $request->department);
        }

        $categoryIds = null;
        if ($activeDepartment) {
            $categoryIds = [$activeDepartment->id];
        } elseif ($activeCollege) {
            $categoryIds = $activeCollege->children->pluck('id')->toArray() ?: [-1];
        }

        $productsQuery = Product::query()
            ->select(['id', 'name', 'slug', 'price', 'sale_price', 'stock', 'featured', 'category_id', 'short_description'])
            ->active()
            ->with([
                'category:id,name,slug,parent_id',
                'category.parent:id,name,slug',
                'images' => fn ($q) => $q->select(['id', 'product_id', 'thumb', 'medium', 'image'])->orderBy('sort_order'),
                'activeDiscount',
            ]);


        if ($categoryIds !== null) {
            $productsQuery->whereIn('category_id', $categoryIds);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $productsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('featured')) {
            $productsQuery->where('featured', true);
        }

        if ($request->boolean('in_stock')) {
            $productsQuery->where('stock', '>', 0);
        }

        match ($request->get('sort', 'newest')) {
            'price_asc' => $productsQuery->orderBy('price'),
            'price_desc' => $productsQuery->orderByDesc('price'),
            'name' => $productsQuery->orderBy('name'),
            default => $productsQuery->orderByDesc('created_at'),
        };

        $products = $productsQuery->paginate(12)->withQueryString();

        $pageTitle = \App\Models\SiteSetting::get('catalog_page_title', 'All Products');
        $pageSubtitle = \App\Models\SiteSetting::get('catalog_page_subtitle', 'Browse all available products');

        $seo = [
            'seo_title' => \App\Models\SiteSetting::get('catalog_seo_title', 'All Products | UNI-LAB MARKET'),
            'seo_description' => \App\Models\SiteSetting::get('catalog_seo_description', 'Browse all laboratory and educational tools across every university college.'),
            'seo_keywords' => \App\Models\SiteSetting::get('catalog_seo_keywords', ''),
            'canonical_url' => route('products.index'),
            'og_image' => \App\Models\SiteSetting::getUrl('default_og_image'),
        ];

        return view('products.index', compact(
            'products',
            'colleges',
            'activeCollege',
            'activeDepartment',
            'seo',
            'pageTitle',
            'pageSubtitle',
        ));
    }
}
