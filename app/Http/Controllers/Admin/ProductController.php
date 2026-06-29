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

        $categories = $this->categoryOptions();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product(),
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function edit(Product $product): View
    {
        $product->load('images');

        return view('admin.products.form', [
            'product' => $product,
            'categories' => $this->categoryOptions(),
        ]);
    }

    private function categoryOptions()
    {
        return Category::active()->orderBy('name')->get(['id', 'name', 'parent_id']);
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

    /** تكرار منتج (يُنشئ نسخة جديدة بنفس البيانات بدون الصور). */
    public function duplicate(Product $product): RedirectResponse
    {
        $copy = $product->replicate(['slug']);
        $copy->name  = $product->name . ' (نسخة)';
        $copy->slug  = Str::slug($product->slug . '-' . Str::random(5));
        $copy->sku   = $product->sku ? $product->sku . '-COPY-' . Str::random(4) : null;
        $copy->stock = 0;
        $copy->featured = false;
        $copy->save();

        NavigationService::clearCache();
        return redirect()->route('admin.products.edit', $copy)->with('success', 'تم تكرار المنتج. عدّل ما تريد وأضف الصور.');
    }

    /** تفعيل / تعطيل / تمييز / إلغاء تمييز جماعي. */
    public function bulkAction(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer|exists:products,id',
            'action' => 'required|in:feature,unfeature,delete',
        ]);

        $q = Product::whereIn('id', $data['ids']);
        $count = match ($data['action']) {
            'feature'   => $q->update(['featured' => true]),
            'unfeature' => $q->update(['featured' => false]),
            'delete'    => (function () use ($q) { $n = $q->count(); $q->delete(); return $n; })(),
        };

        NavigationService::clearCache();
        return back()->with('success', "تم تنفيذ الإجراء على {$count} منتج.");
    }

    /** تصدير المنتجات كـ CSV. */
    public function exportCsv()
    {
        $filename = 'products-' . now()->format('Ymd-His') . '.csv';
        return response()->streamDownload(function () {
            $h = fopen('php://output', 'w');
            fwrite($h, "\xEF\xBB\xBF");
            fputcsv($h, ['ID','الاسم','SKU','التصنيف','السعر','سعر العرض','المخزون','مميز','تاريخ الإنشاء']);
            Product::with('category:id,name')->chunk(200, function ($rows) use ($h) {
                foreach ($rows as $p) {
                    fputcsv($h, [
                        $p->id, $p->name, $p->sku, $p->category?->name,
                        $p->price, $p->sale_price, $p->stock,
                        $p->featured ? 'نعم' : 'لا',
                        $p->created_at?->format('Y-m-d'),
                    ]);
                }
            });
            fclose($h);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
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
            'short_description' => 'nullable|string|max:200',
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
            'images' => 'nullable|array|max:8',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp,gif|max:4096',
            'library_image_ids' => 'nullable|array|max:8',
            'library_image_ids.*' => 'integer|exists:product_images,id',
        ], [
            'images.max' => 'الحد الأقصى 8 صور لكل منتج.',
            'images.*.image' => 'الملف المرفوع يجب أن يكون صورة.',
            'images.*.max' => 'حجم الصورة يجب أن لا يتجاوز 4 ميجابايت.',
        ]);
    }

    private function storeImages(Request $request, Product $product): void
    {
        // الحد الأقصى الإجمالي = 8
        $existingCount = $product->images()->count();
        $removeCount = is_array($request->input('remove_images')) ? count($request->input('remove_images')) : 0;
        $availableSlots = max(0, 8 - ($existingCount - $removeCount));
        if ($availableSlots <= 0) return;

        $sortOrder = $product->images()->max('sort_order') ?? 0;

        // 1) صور من المكتبة (نسخ مرجع للمسارات نفسها)
        $libraryIds = (array) $request->input('library_image_ids', []);
        if ($libraryIds && $availableSlots > 0) {
            $libImages = ProductImage::whereIn('id', $libraryIds)->limit($availableSlots)->get();
            foreach ($libImages as $img) {
                $sortOrder++;
                $availableSlots--;
                $product->images()->create([
                    'image' => $img->image,
                    'thumb' => $img->thumb,
                    'medium' => $img->medium,
                    'large' => $img->large,
                    'sort_order' => $sortOrder,
                ]);
                if ($availableSlots <= 0) return;
            }
        }

        // 2) ملفات مرفوعة
        if ($request->hasFile('images') && $availableSlots > 0) {
            $files = array_slice($request->file('images'), 0, $availableSlots);
            foreach ($files as $file) {
                $sortOrder++;
                $paths = $this->imageService->storeProductImages($file, $product->id);
                $product->images()->create(array_merge($paths, ['sort_order' => $sortOrder]));
            }
        }
    }

    /** AJAX: مكتبة صور لإعادة الاستخدام عند إنشاء/تعديل المنتجات. */
    public function imageLibrary(Request $request)
    {
        $q = ProductImage::query()
            ->select(['id', 'product_id', 'thumb', 'medium', 'image'])
            ->with('product:id,name')
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $q->whereHas('product', fn($p) => $p->where('name', 'like', $term));
        }

        $images = $q->limit(60)->get()->map(fn($img) => [
            'id' => $img->id,
            'thumb' => $img->getUrl('thumb'),
            'product' => $img->product?->name,
        ]);

        return response()->json(['images' => $images]);
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
