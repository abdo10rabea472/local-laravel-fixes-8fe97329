<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(string $slug): View
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->active()
            ->with([
                'category:id,name,slug',
                'images' => fn ($q) => $q->orderBy('sort_order'),
                'activeDiscount',
            ])
            ->firstOrFail();

        $relatedProducts = Product::query()
            ->select(['id', 'name', 'slug', 'price', 'sale_price', 'stock', 'category_id'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->active()
            ->with([
                'images' => fn ($q) => $q->select(['id', 'product_id', 'thumb', 'medium', 'image'])->orderBy('sort_order'),
                'activeDiscount',
            ])
            ->limit(4)
            ->get();


        $primaryImage = $product->images->first();
        $ogImage = $product->og_image ?: ($primaryImage ? $primaryImage->getUrl('large') : null);

        $seo = [
            'seo_title' => $product->seo_title,
            'seo_description' => $product->seo_description,
            'seo_keywords' => $product->seo_keywords,
            'canonical_url' => $product->canonical_url ?: url("/product/{$product->slug}"),
            'og_title' => $product->og_title ?: $product->seo_title,
            'og_description' => $product->og_description ?: $product->seo_description,
            'og_image' => $ogImage,
            'schema_markup' => $product->schema_markup ?: $this->defaultProductSchema($product, $ogImage),
        ];

        return view('product.show', compact('product', 'relatedProducts', 'seo'));
    }

    private function defaultProductSchema(Product $product, ?string $image): string
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => $product->seo_description,
            'sku' => $product->sku,
            'offers' => [
                '@type' => 'Offer',
                'price' => $product->effective_price,
                'priceCurrency' => 'EGP',
                'availability' => $product->isInStock()
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
            ],
        ];

        if ($image) {
            $schema['image'] = $image;
        }

        return json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
