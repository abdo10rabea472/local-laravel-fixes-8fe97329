@extends('admin.layouts.app')

@section('title', $product->exists ? 'Edit Product' : 'Add New Product')

@section('content')
<form
    method="POST"
    action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}"
    enctype="multipart/form-data"
>
    @csrf
    @if($product->exists) @method('PUT') @endif

    <x-admin.page
        :title="$product->exists ? 'Edit Lab Product' : 'Add New Lab Product'"
        subtitle="Fill in technical data, pricing and SEO for lab equipment and university kits."
        :back="route('admin.products.index')"
    >
        {{-- ============ MAIN COLUMN ============ --}}

        {{-- Basic Info --}}
        <x-admin.card title="Basic Product Information" icon="fa-circle-info">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">Product Name (Arabic) *</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                           placeholder="e.g., Binocular Light Microscope"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">Product Name (English)</label>
                    <input type="text" name="name_en" value="{{ old('name_en', $product->name_en ?? '') }}"
                           placeholder="e.g., Binocular Biological Microscope"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">SKU (Stock Keeping Unit)</label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}"
                           placeholder="UL-MED-4820"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-mono focus:border-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">Slug URL</label>
                    <input type="text" name="slug" value="{{ old('slug', $product->slug) }}"
                           placeholder="binocular-microscope-lab"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-mono focus:border-primary-500 focus:outline-none">
                </div>
            </div>

            <div class="mt-4">
                <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">Detailed Description</label>
                <textarea name="description" rows="5"
                          placeholder="Write specs and technical details for this lab kit..."
                          class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">{{ old('description', $product->description) }}</textarea>
            </div>
        </x-admin.card>

        {{-- Pricing & Stock --}}
        <x-admin.card title="Pricing & Stock" icon="fa-coins">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">Cost Price</label>
                    <input type="number" step="0.01" name="cost_price" value="{{ old('cost_price', $product->cost_price ?? '') }}"
                           placeholder="0.00 EGP"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">Sale Price *</label>
                    <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" required
                           placeholder="0.00 EGP"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">Discount Price (Students)</label>
                    <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}"
                           placeholder="0.00 EGP"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">Total Stock in Warehouse</label>
                    <input type="number" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" required
                           placeholder="100"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">Low-stock alert threshold</label>
                    <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 5) }}"
                           placeholder="5 units"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
            </div>
        </x-admin.card>

        {{-- Images --}}
        <x-admin.card title="High-quality Product Images" icon="fa-images">
            @php $currentCount = $product->exists ? $product->images->count() : 0; $remaining = max(0, 5 - $currentCount); @endphp
            <label for="product-images" class="border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-2xl p-10 text-center block cursor-pointer hover:border-primary-500 transition-colors">
                <i class="fas fa-cloud-arrow-up text-4xl text-gray-400 mb-3"></i>
                <p class="font-bold text-gray-700 dark:text-gray-200">Drag & drop product images here</p>
                <p class="text-xs text-gray-400 mt-1">Supports PNG, JPG up to 8MB ({{ $remaining }} remaining)</p>
                <input id="product-images" type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp,image/gif"
                       class="hidden" @if($remaining === 0) disabled @endif>
            </label>
            @error('images') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
            @error('images.*') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror

            @if($product->exists && $product->images->count())
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4">
                @foreach($product->images as $image)
                <label class="relative border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden">
                    <img src="{{ $image->getUrl('thumb') }}" alt="" class="w-full h-28 object-cover">
                    <div class="p-2 text-xs flex items-center gap-1.5 bg-white dark:bg-dark-800">
                        <input type="checkbox" name="remove_images[]" value="{{ $image->id }}"> Remove
                    </div>
                </label>
                @endforeach
            </div>
            @endif
        </x-admin.card>

        {{-- SEO --}}
        <x-admin.card title="SEO Settings" icon="fa-magnifying-glass-chart">
            <div class="space-y-3">
                <input type="text" name="seo_title" value="{{ old('seo_title', $product->exists ? $product->getRawOriginal('seo_title') : '') }}" placeholder="SEO Title"
                       class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                <textarea name="seo_description" rows="2" placeholder="Meta Description"
                          class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">{{ old('seo_description', $product->exists ? $product->getRawOriginal('seo_description') : '') }}</textarea>
                <input type="text" name="seo_keywords" value="{{ old('seo_keywords', $product->seo_keywords) }}" placeholder="Keywords"
                       class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                <input type="url" name="canonical_url" value="{{ old('canonical_url', $product->canonical_url) }}" placeholder="Canonical URL"
                       class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
            </div>
        </x-admin.card>

        {{-- ============ SIDE COLUMN ============ --}}
        <x-slot:side>
            <x-admin.card title="Category & Store Assignment" icon="fa-sitemap">
                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">Main College</label>
                        <select class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none" disabled>
                            <option>Faculty of Medicine & Dentistry</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">Subcategory *</label>
                        <select name="category_id" required class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                            <option value="">Choose category</option>
                            @foreach($categories->whereNull('parent_id') as $parent)
                                <optgroup label="{{ $parent->name }}">
                                    @foreach($categories->where('parent_id', $parent->id) as $child)
                                        <option value="{{ $child->id }}" @selected(old('category_id', $product->category_id) == $child->id)>{{ $child->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">Brand</label>
                        <input type="text" name="brand" value="{{ old('brand', $product->brand ?? '') }}" placeholder="Littmann Germany"
                               class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                    </div>

                    <div class="pt-3 border-t border-gray-100 dark:border-gray-800 space-y-3">
                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200 cursor-pointer">
                            <input type="checkbox" name="status" value="1" @checked(old('status', $product->exists ? $product->status : true))
                                   class="rounded text-primary-600 focus:ring-primary-500">
                            Activate and publish product
                        </label>
                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200 cursor-pointer">
                            <input type="checkbox" name="featured" value="1" @checked(old('featured', $product->featured))
                                   class="rounded text-primary-600 focus:ring-primary-500">
                            Feature on homepage
                        </label>
                    </div>
                </div>

                <div class="mt-5 pt-5 border-t border-gray-100 dark:border-gray-800 space-y-2">
                    <button type="submit" class="w-full h-12 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/20 transition-colors">
                        <i class="fas fa-rocket mr-1"></i> {{ $product->exists ? 'Update Product' : 'Publish Product' }}
                    </button>
                    <button type="submit" name="draft" value="1" class="w-full h-11 bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-dark-700 font-bold rounded-xl transition-colors">
                        Save as draft
                    </button>
                </div>
            </x-admin.card>
        </x-slot:side>
    </x-admin.page>
</form>
@endsection
