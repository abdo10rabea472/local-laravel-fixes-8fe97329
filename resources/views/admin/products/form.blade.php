@extends('admin.layouts.app')

@section('title', $product->exists ? __('app.admin_product_form_title_edit') : __('app.admin_product_form_title_create'))

@section('content')
<form
    method="POST"
    action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}"
    enctype="multipart/form-data"
>
    @csrf
    @if($product->exists) @method('PUT') @endif

    <x-admin.page
        :title="$product->exists ? __('app.admin_product_form_heading_edit') : __('app.admin_product_form_heading_create')"
        :subtitle="__('app.admin_product_form_subtitle')"
        :back="route('admin.products.index')"
    >
        <x-admin.card :title="__('app.admin_product_form_basic')" icon="fa-circle-info">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">{{ __('app.admin_product_form_name_ar') }} *</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                           placeholder="{{ __('app.admin_product_form_name_ar_ph') }}"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">{{ __('app.admin_product_form_name_en') }}</label>
                    <input type="text" name="name_en" value="{{ old('name_en', $product->name_en ?? '') }}"
                           placeholder="{{ __('app.admin_product_form_name_en_ph') }}"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">{{ __('app.admin_product_form_sku') }}</label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}"
                           placeholder="UL-MED-4820"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-mono focus:border-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">{{ __('app.admin_product_form_slug') }}</label>
                    <input type="text" name="slug" value="{{ old('slug', $product->slug) }}"
                           placeholder="binocular-microscope-lab"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-mono focus:border-primary-500 focus:outline-none">
                </div>
            </div>

            <div class="mt-4">
                <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">{{ __('app.admin_product_form_short_description') }}</label>
                <textarea name="short_description" rows="2" maxlength="500"
                          placeholder="{{ __('app.admin_product_form_short_description_ph') }}"
                          class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">{{ old('short_description', $product->short_description) }}</textarea>
                <p class="text-[11px] text-gray-400 mt-1">{{ __('app.admin_product_form_short_description_hint') }}</p>
            </div>
            <div class="mt-4">
                <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">{{ __('app.admin_product_form_description') }}</label>
                <textarea name="description" rows="8"
                          placeholder="{{ __('app.admin_product_form_description_ph') }}"
                          class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">{{ old('description', $product->description) }}</textarea>
            </div>
        </x-admin.card>

        @php
            $defaultCurrency = app(\App\Services\CurrencyService::class)->default();
            $cur = $defaultCurrency->code ?? 'EGP';
            $curSymbol = $defaultCurrency->symbol ?? $cur;
            $curName = $defaultCurrency->name ?? $cur;
        @endphp

        <div class="relative overflow-hidden rounded-2xl mb-5 p-6 md:p-7 bg-gradient-to-br from-amber-50 via-orange-50 to-amber-100 dark:from-amber-900/30 dark:via-orange-900/20 dark:to-amber-800/30 border-2 border-amber-300 dark:border-amber-700 shadow-lg shadow-amber-200/40 dark:shadow-amber-900/30">
            <div class="absolute -top-8 -right-8 w-32 h-32 bg-amber-300/30 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-8 -left-8 w-32 h-32 bg-orange-300/30 rounded-full blur-2xl"></div>
            <div class="relative flex items-start gap-4">
                <div class="shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/40">
                    <i class="fas fa-triangle-exclamation text-white text-2xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-lg md:text-xl font-extrabold text-amber-900 dark:text-amber-100 mb-2 flex flex-wrap items-center gap-2">
                        {{ __('app.admin_product_form_currency_notice_title') }}
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white dark:bg-amber-950 text-amber-700 dark:text-amber-200 text-sm font-mono font-bold border border-amber-300 dark:border-amber-700 shadow-sm">
                            <i class="fas fa-coins text-xs"></i> {{ $curSymbol }} {{ $cur }}
                        </span>
                    </h4>
                    <p class="text-sm md:text-base text-amber-900/90 dark:text-amber-100/90 leading-relaxed">
                        {{ __('app.admin_product_form_currency_notice', ['currency' => $cur, 'name' => $curName]) }}
                    </p>
                </div>
            </div>
        </div>

        <x-admin.card :title="__('app.admin_product_form_pricing')" icon="fa-coins">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">{{ __('app.admin_product_form_cost_price') }} ({{ $cur }})</label>
                    <input type="number" step="0.01" name="cost_price" value="{{ old('cost_price', $product->cost_price ?? '') }}"
                           placeholder="0.00"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">{{ __('app.admin_product_form_sale_price') }} ({{ $cur }}) *</label>
                    <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" required
                           placeholder="0.00"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">{{ __('app.admin_product_form_discount_price') }} ({{ $cur }})</label>
                    <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}"
                           placeholder="0.00"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">{{ __('app.admin_product_form_stock') }}</label>
                    <input type="number" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" required
                           placeholder="100"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">{{ __('app.admin_product_form_low_stock') }}</label>
                    <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 5) }}"
                           placeholder="5"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
            </div>
        </x-admin.card>

        <x-admin.card :title="__('app.admin_product_form_images')" icon="fa-images">
            @php $currentCount = $product->exists ? $product->images->count() : 0; $remaining = max(0, 8 - $currentCount); @endphp
            <div id="img-uploader" data-max="8" data-existing="{{ $currentCount }}">
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <label for="product-images" class="inline-flex items-center gap-2 px-4 h-10 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-xl cursor-pointer">
                        <i class="fas fa-upload"></i> {{ __('app.admin_product_form_images_drop') }}
                    </label>
                    <button type="button" id="open-library-btn" class="inline-flex items-center gap-2 px-4 h-10 bg-gray-100 dark:bg-dark-800 hover:bg-gray-200 dark:hover:bg-dark-700 text-sm font-bold rounded-xl">
                        <i class="fas fa-photo-film"></i> اختر من المكتبة
                    </button>
                    <span class="text-xs text-gray-500" id="img-counter">0 / 8</span>
                </div>

                <label for="product-images" class="border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-2xl p-8 text-center block cursor-pointer hover:border-primary-500 transition-colors">
                    <i class="fas fa-cloud-arrow-up text-3xl text-gray-400 mb-2"></i>
                    <p class="font-bold text-gray-700 dark:text-gray-200 text-sm">اسحب الصور هنا أو اضغط للاختيار</p>
                    <p class="text-xs text-gray-400 mt-1">JPG / PNG / WEBP — حد أقصى 4MB لكل صورة، 8 صور إجمالاً</p>
                </label>
                <input id="product-images" type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp,image/gif" class="hidden">

                <div id="upload-previews" class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4"></div>
                <div id="library-picked" class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-3"></div>
            </div>

            @error('images') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
            @error('images.*') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror

            @if($product->exists && $product->images->count())
            <div class="mt-5">
                <p class="text-xs font-bold text-gray-500 mb-2">الصور الحالية</p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach($product->images as $image)
                    <label class="relative border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden">
                        <img src="{{ $image->getUrl('thumb') }}" alt="" class="w-full h-28 object-cover">
                        <div class="p-2 text-xs flex items-center gap-1.5 bg-white dark:bg-dark-800">
                            <input type="checkbox" name="remove_images[]" value="{{ $image->id }}"> {{ __('app.admin_common_remove') }}
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif
        </x-admin.card>

        {{-- Library modal --}}
        <div id="lib-modal" class="fixed inset-0 bg-black/60 z-50 hidden items-center justify-center p-4">
            <div class="bg-white dark:bg-dark-900 w-full max-w-4xl max-h-[85vh] rounded-2xl flex flex-col overflow-hidden">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-800">
                    <h3 class="font-bold">مكتبة الصور</h3>
                    <div class="flex gap-2">
                        <input id="lib-search" type="text" placeholder="بحث بالمنتج..." class="h-10 px-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm">
                        <button type="button" id="lib-close" class="h-10 px-4 bg-gray-100 dark:bg-dark-800 rounded-lg text-sm font-bold">إغلاق</button>
                    </div>
                </div>
                <div id="lib-grid" class="p-4 grid grid-cols-3 md:grid-cols-6 gap-3 overflow-y-auto"></div>
            </div>
        </div>

        <script>
        (function(){
            const MAX = 8;
            const existing = {{ $currentCount }};
            const fileInput = document.getElementById('product-images');
            const previews = document.getElementById('upload-previews');
            const libraryPicked = document.getElementById('library-picked');
            const counter = document.getElementById('img-counter');
            const libModal = document.getElementById('lib-modal');
            const libGrid = document.getElementById('lib-grid');
            const libSearch = document.getElementById('lib-search');

            let selectedFiles = []; // {file, id}
            let pickedLibIds = new Set();

            function updateCounter(){
                const total = existing + selectedFiles.length + pickedLibIds.size;
                counter.textContent = total + ' / ' + MAX;
                counter.className = 'text-xs font-bold ' + (total > MAX ? 'text-red-600' : 'text-gray-500');
            }

            function syncFileInput(){
                const dt = new DataTransfer();
                selectedFiles.forEach(s => dt.items.add(s.file));
                fileInput.files = dt.files;
            }

            function renderPreviews(){
                previews.innerHTML = '';
                selectedFiles.forEach((s, idx) => {
                    const card = document.createElement('div');
                    card.className = 'relative border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden';
                    const isImg = s.file.type.startsWith('image/');
                    const tooBig = s.file.size > 4 * 1024 * 1024;
                    const ok = isImg && !tooBig;
                    const url = URL.createObjectURL(s.file);
                    card.innerHTML = `
                        <img src="${url}" class="w-full h-28 object-cover ${ok ? '' : 'opacity-40'}">
                        <div class="absolute top-1 right-1 flex gap-1">
                            <button type="button" data-idx="${idx}" class="rm-file w-7 h-7 bg-red-600 text-white rounded-full text-xs"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="p-2 text-[11px] ${ok ? 'bg-white dark:bg-dark-800' : 'bg-red-50 text-red-700'}">
                            ${ok ? '<i class="fas fa-check text-green-600"></i> جاهز' : (tooBig ? 'فشل: حجم كبير (>4MB)' : 'فشل: ليست صورة')}
                            <div class="h-1 bg-gray-200 rounded mt-1 overflow-hidden"><div class="h-full bg-primary-500" style="width:${ok ? 100 : 0}%"></div></div>
                        </div>`;
                    previews.appendChild(card);
                });
                previews.querySelectorAll('.rm-file').forEach(b => b.addEventListener('click', e => {
                    const i = parseInt(b.dataset.idx);
                    selectedFiles.splice(i, 1);
                    syncFileInput(); renderPreviews(); updateCounter();
                }));
            }

            function renderLibPicked(){
                libraryPicked.innerHTML = '';
                pickedLibIds.forEach(id => {
                    const meta = window.__libCache?.[id];
                    if (!meta) return;
                    const card = document.createElement('div');
                    card.className = 'relative border-2 border-primary-500 rounded-2xl overflow-hidden';
                    card.innerHTML = `
                        <input type="hidden" name="library_image_ids[]" value="${id}">
                        <img src="${meta.thumb}" class="w-full h-28 object-cover">
                        <div class="absolute top-1 right-1">
                            <button type="button" data-id="${id}" class="rm-lib w-7 h-7 bg-red-600 text-white rounded-full text-xs"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="p-2 text-[11px] bg-primary-50 text-primary-700"><i class="fas fa-book"></i> من المكتبة</div>`;
                    libraryPicked.appendChild(card);
                });
                libraryPicked.querySelectorAll('.rm-lib').forEach(b => b.addEventListener('click', () => {
                    pickedLibIds.delete(parseInt(b.dataset.id));
                    renderLibPicked(); updateCounter();
                }));
            }

            fileInput.addEventListener('change', e => {
                Array.from(e.target.files).forEach(f => selectedFiles.push({file: f}));
                syncFileInput(); renderPreviews(); updateCounter();
            });

            // Drag & drop
            const dropZone = fileInput.previousElementSibling;
            ['dragover','dragenter'].forEach(ev => dropZone.addEventListener(ev, e => { e.preventDefault(); dropZone.classList.add('border-primary-500'); }));
            ['dragleave','drop'].forEach(ev => dropZone.addEventListener(ev, e => { e.preventDefault(); dropZone.classList.remove('border-primary-500'); }));
            dropZone.addEventListener('drop', e => {
                Array.from(e.dataTransfer.files).forEach(f => selectedFiles.push({file: f}));
                syncFileInput(); renderPreviews(); updateCounter();
            });

            // Library
            window.__libCache = {};
            async function loadLibrary(q=''){
                libGrid.innerHTML = '<p class="col-span-full text-center text-sm text-gray-500 py-8">جارٍ التحميل...</p>';
                const res = await fetch('{{ route('admin.products.image-library') }}?search=' + encodeURIComponent(q), {headers: {'Accept':'application/json'}});
                const data = await res.json();
                libGrid.innerHTML = '';
                if (!data.images.length) { libGrid.innerHTML = '<p class="col-span-full text-center text-sm text-gray-500 py-8">لا توجد صور.</p>'; return; }
                data.images.forEach(img => {
                    window.__libCache[img.id] = img;
                    const div = document.createElement('div');
                    const selected = pickedLibIds.has(img.id);
                    div.className = 'relative cursor-pointer rounded-xl overflow-hidden border-2 ' + (selected ? 'border-primary-500' : 'border-transparent');
                    div.innerHTML = `<img src="${img.thumb}" class="w-full h-24 object-cover"><div class="p-1 text-[10px] truncate bg-white dark:bg-dark-800">${img.product||''}</div>`;
                    div.addEventListener('click', () => {
                        if (pickedLibIds.has(img.id)) pickedLibIds.delete(img.id);
                        else pickedLibIds.add(img.id);
                        loadLibrary(libSearch.value); renderLibPicked(); updateCounter();
                    });
                    libGrid.appendChild(div);
                });
            }
            document.getElementById('open-library-btn').addEventListener('click', () => {
                libModal.classList.remove('hidden'); libModal.classList.add('flex'); loadLibrary();
            });
            document.getElementById('lib-close').addEventListener('click', () => {
                libModal.classList.add('hidden'); libModal.classList.remove('flex');
            });
            let t; libSearch.addEventListener('input', () => { clearTimeout(t); t = setTimeout(() => loadLibrary(libSearch.value), 300); });

            updateCounter();
        })();
        </script>

        <x-admin.card :title="__('app.admin_common_seo_settings')" icon="fa-magnifying-glass-chart">
            <div class="space-y-3">
                <input type="text" name="seo_title" value="{{ old('seo_title', $product->exists ? $product->getRawOriginal('seo_title') : '') }}" placeholder="{{ __('app.admin_common_seo_title') }}"
                       class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                <textarea name="seo_description" rows="2" placeholder="{{ __('app.admin_common_meta_description') }}"
                          class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">{{ old('seo_description', $product->exists ? $product->getRawOriginal('seo_description') : '') }}</textarea>
                <input type="text" name="seo_keywords" value="{{ old('seo_keywords', $product->seo_keywords) }}" placeholder="{{ __('app.admin_common_keywords') }}"
                       class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                <input type="url" name="canonical_url" value="{{ old('canonical_url', $product->canonical_url) }}" placeholder="{{ __('app.admin_common_canonical_url') }}"
                       class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
            </div>
        </x-admin.card>

        <x-slot:side>
            <x-admin.card :title="__('app.admin_product_form_assign')" icon="fa-sitemap">
                <div class="space-y-4">
                    @php
                        $parents = $categories->whereNull('parent_id')->values();
                        $selectedChildId = (int) old('category_id', $product->category_id);
                        $selectedChild = $categories->firstWhere('id', $selectedChildId);
                        $selectedParentId = old('parent_category_id', $selectedChild?->parent_id);
                    @endphp
                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">{{ __('app.admin_product_form_main_college') }} *</label>
                        <select id="parent-category" class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                            <option value="">{{ __('app.admin_product_form_main_college_default') }}</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}" @selected($selectedParentId == $parent->id)>{{ $parent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">{{ __('app.admin_product_form_subcategory') }} *</label>
                        <select id="child-category" name="category_id" required class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                            <option value="">{{ __('app.admin_product_form_choose_category') }}</option>
                        </select>
                        @error('category_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <script>
                    (function(){
                        const allChildren = @json($categories->whereNotNull('parent_id')->map(fn($c) => ['id'=>$c->id,'parent_id'=>$c->parent_id,'name'=>$c->name])->values());
                        const parentSel = document.getElementById('parent-category');
                        const childSel = document.getElementById('child-category');
                        const preselectChild = {{ $selectedChildId ?: 'null' }};
                        function refresh(){
                            const pid = parseInt(parentSel.value);
                            const current = childSel.value;
                            childSel.innerHTML = '<option value="">{{ __('app.admin_product_form_choose_category') }}</option>';
                            allChildren.filter(c => !pid || c.parent_id === pid).forEach(c => {
                                const o = document.createElement('option');
                                o.value = c.id; o.textContent = c.name;
                                if (String(c.id) === String(current) || c.id === preselectChild) o.selected = true;
                                childSel.appendChild(o);
                            });
                        }
                        parentSel.addEventListener('change', refresh);
                        refresh();
                    })();
                    </script>
                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">{{ __('app.admin_product_form_brand') }}</label>
                        <input type="text" name="brand" value="{{ old('brand', $product->brand ?? '') }}" placeholder="Littmann Germany"
                               class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                    </div>

                    <div class="pt-3 border-t border-gray-100 dark:border-gray-800 space-y-3">
                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200 cursor-pointer">
                            <input type="checkbox" name="status" value="1" @checked(old('status', $product->exists ? $product->status : true))
                                   class="rounded text-primary-600 focus:ring-primary-500">
                            {{ __('app.admin_product_form_activate') }}
                        </label>
                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200 cursor-pointer">
                            <input type="checkbox" name="featured" value="1" @checked(old('featured', $product->featured))
                                   class="rounded text-primary-600 focus:ring-primary-500">
                            {{ __('app.admin_product_form_feature') }}
                        </label>
                    </div>
                </div>

                <div class="mt-5 pt-5 border-t border-gray-100 dark:border-gray-800 space-y-2">
                    <button type="submit" class="w-full h-12 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/20 transition-colors">
                        <i class="fas fa-rocket mr-1"></i> {{ $product->exists ? __('app.admin_product_form_update') : __('app.admin_product_form_publish') }}
                    </button>
                    <button type="submit" name="draft" value="1" class="w-full h-11 bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-dark-700 font-bold rounded-xl transition-colors">
                        {{ __('app.admin_product_form_save_draft') }}
                    </button>
                </div>
            </x-admin.card>
        </x-slot:side>
    </x-admin.page>
</form>
@endsection
