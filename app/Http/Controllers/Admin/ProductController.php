<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ImageService;
use App\Services\NavigationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private ImageService $imageService)
    {
    }

    public function index(Request $request): View
    {
        $query = Product::query()
            ->select(['id', 'name', 'slug', 'sku', 'category_id', 'price', 'sale_price', 'stock', 'featured', 'status', 'created_at'])
            ->with([
                'category:id,name',
                'images:id,product_id,thumb,medium,image',
                'activeDiscount',
            ]);


        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status === 'active');
        }

        $products = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        $categories = Category::active()->orderBy('name')->get(['id', 'name', 'parent_id']);

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::active()->orderBy('name')->get(['id', 'name', 'parent_id']);

        return view('admin.products.form', [
            'product' => new Product(),
            'categories' => $categories,
        ]);
    }

    public function edit(Product $product): View
    {
        $product->load('images');
        $categories = Category::active()->orderBy('name')->get(['id', 'name', 'parent_id']);

        return view('admin.products.form', compact('product', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);

        $validated['featured'] = $request->boolean('featured');
        $validated['status'] = $request->boolean('status', true);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $product = Product::create($validated);

        $this->storeImages($request, $product);
        NavigationService::clearCache();

        return redirect()->route('admin.products.index')->with('success', 'تم إضافة المنتج بنجاح.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validateProduct($request, $product->id);

        $validated['featured'] = $request->boolean('featured');
        $validated['status'] = $request->boolean('status', true);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $product->update($validated);

        $this->storeImages($request, $product);

        if ($request->filled('remove_images')) {
            $this->removeImages($product, $request->remove_images);
        }

        NavigationService::clearCache();

        return redirect()->route('admin.products.index')->with('success', 'تم تحديث المنتج بنجاح.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        foreach ($product->images as $image) {
            $this->imageService->deletePaths($image->image, $image->thumb, $image->medium, $image->large);
        }

        $product->delete();

        NavigationService::clearCache();

        return redirect()->route('admin.products.index')->with('success', 'تم حذف المنتج بنجاح.');
    }

    private function validateProduct(Request $request, ?int $ignoreId = null): array
    {
        $slugRule = 'nullable|string|max:255|unique:products,slug';
        $skuRule = 'nullable|string|max:100|unique:products,sku';

        if ($ignoreId) {
            $slugRule .= ',' . $ignoreId;
            $skuRule .= ',' . $ignoreId;
        }

        return $request->validate([
            'name' => 'required|string|max:255',
            'slug' => $slugRule,
            'sku' => $skuRule,
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lte:price',
            'stock' => 'required|integer|min:0',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'featured' => 'nullable|boolean',
            'status' => 'nullable|boolean',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'seo_keywords' => 'nullable|string',
            'canonical_url' => 'nullable|url|max:255',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string',
            'og_image' => 'nullable|string|max:255',
            'schema_markup' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);
    }

    private function storeImages(Request $request, Product $product): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $sortOrder = $product->images()->max('sort_order') ?? 0;

        foreach ($request->file('images') as $file) {
            $sortOrder++;
            $paths = $this->imageService->storeProductImages($file, $product->id);
            $product->images()->create(array_merge($paths, ['sort_order' => $sortOrder]));
        }
    }

    private function removeImages(Product $product, array $imageIds): void
    {
        $images = ProductImage::where('product_id', $product->id)
            ->whereIn('id', $imageIds)
            ->get();

        foreach ($images as $image) {
            $this->imageService->deletePaths($image->image, $image->thumb, $image->medium, $image->large);
            $image->delete();
        }
    }
}
