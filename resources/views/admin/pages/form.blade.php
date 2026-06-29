@extends('admin.settings.layout')

@section('settings-content')
<div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
    <div class="border-b border-slate-100 px-6 py-4">
        <h3 class="text-base font-bold text-slate-800">{{ $page->exists ? 'تعديل صفحة: ' . $page->title : 'إضافة صفحة جديدة' }}</h3>
        <p class="text-xs text-slate-500 mt-1">{{ $page->exists ? 'تحديث محتوى وSEO الصفحة.' : 'إنشاء صفحة ثابتة جديدة.' }}</p>
    </div>

    <form method="POST" action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}" enctype="multipart/form-data" class="p-6 space-y-6">
        @csrf
        @if($page->exists) @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500">العنوان *</label>
                <input type="text" name="title" value="{{ old('title', $page->title) }}" required class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500">المعرف (Slug) *</label>
                <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" required class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
            </div>
        </div>

        @php
            $isFaqsPage = $page->slug === 'faqs';
            $faqItems = [];
            if ($isFaqsPage) {
                $decoded = json_decode((string) old('content', $page->content), true);
                if (is_array($decoded)) {
                    foreach ($decoded as $row) {
                        if (is_array($row) && isset($row['q'], $row['a'])) {
                            $faqItems[] = ['q' => (string) $row['q'], 'a' => (string) $row['a'], 'category' => (string) ($row['category'] ?? '')];
                        }
                    }
                }
                if (empty($faqItems)) {
                    $faqItems[] = ['q' => '', 'a' => '', 'category' => ''];
                }
            }
        @endphp

        @if($isFaqsPage)
            <div class="space-y-3">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div>
                        <label class="text-xs font-bold text-slate-500">الأسئلة والأجوبة</label>
                        <p class="text-[11px] text-slate-400 mt-1">
                            المجموع: <span id="faq-total" class="font-bold text-slate-600">{{ count($faqItems) }}</span>
                            — يدعم آلاف الأسئلة مع بحث وتقسيم صفحات.
                        </p>
                    </div>
                    <button type="button" id="faq-add" class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold px-4 py-2 rounded-xl">
                        <i class="fa-solid fa-plus"></i> إضافة سؤال
                    </button>
                </div>

                <div class="flex items-center gap-3 flex-wrap">
                    <div class="relative flex-1 min-w-[220px]">
                        <input type="text" id="faq-search" placeholder="ابحث في الأسئلة، الإجابات، التصنيف…"
                               class="w-full h-11 ps-10 pe-4 bg-white border border-slate-200 rounded-xl text-sm focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 outline-none">
                        <i class="fa-solid fa-magnifying-glass absolute start-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    </div>
                    <select id="faq-perpage" class="h-11 px-3 bg-white border border-slate-200 rounded-xl text-sm">
                        <option value="20">20 / صفحة</option>
                        <option value="50">50 / صفحة</option>
                        <option value="100">100 / صفحة</option>
                    </select>
                </div>

                <div id="faq-list" class="space-y-3">
                    @foreach($faqItems as $i => $item)
                    <div class="faq-row bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-3" data-idx="{{ $i }}">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500">سؤال <span class="faq-index">{{ $i + 1 }}</span></span>
                            <button type="button" class="faq-remove text-rose-500 hover:text-rose-700 text-xs font-bold inline-flex items-center gap-1">
                                <i class="fa-solid fa-trash"></i> حذف
                            </button>
                        </div>
                        <input type="text" class="faq-q w-full h-11 px-4 bg-white border border-slate-200 rounded-xl text-sm" placeholder="السؤال" value="{{ $item['q'] }}">
                        <textarea class="faq-a w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm" rows="3" placeholder="الإجابة">{{ $item['a'] }}</textarea>
                        <div class="flex items-center gap-2">
                            <input type="text" list="faq-categories" class="faq-cat flex-1 h-10 px-4 bg-white border border-slate-200 rounded-xl text-xs" placeholder="اختر تصنيف أو اكتب جديد…" value="{{ $item['category'] }}">
                            <button type="button" class="faq-cat-new inline-flex items-center gap-1 h-10 px-3 bg-violet-50 hover:bg-violet-100 text-violet-700 rounded-xl text-xs font-bold whitespace-nowrap">
                                <i class="fa-solid fa-plus"></i> تصنيف جديد
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>

                @php
                    $defaultCats = ['Shipping', 'Payment', 'Warranty', 'Returns', 'Support', 'Orders', 'General'];
                    $existingCats = collect($faqItems)->pluck('category')->filter()->unique()->values()->all();
                    $allCats = collect(array_merge($defaultCats, $existingCats))->filter()->unique()->values();
                @endphp
                <datalist id="faq-categories">
                    @foreach($allCats as $cat)
                        <option value="{{ $cat }}"></option>
                    @endforeach
                </datalist>

                <div id="faq-empty" class="hidden text-center py-10 text-sm text-slate-500 bg-slate-50 border border-dashed border-slate-200 rounded-2xl">
                    لا توجد نتائج مطابقة للبحث.
                </div>

                <div id="faq-pager" class="flex items-center justify-between gap-3 pt-2">
                    <button type="button" id="faq-prev" class="inline-flex items-center gap-2 h-10 px-4 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-chevron-right"></i> السابق
                    </button>
                    <div class="text-xs text-slate-500">
                        صفحة <span id="faq-page" class="font-bold text-slate-700">1</span> من <span id="faq-pages" class="font-bold text-slate-700">1</span>
                        · <span id="faq-shown">0</span> ظاهر
                    </div>
                    <button type="button" id="faq-next" class="inline-flex items-center gap-2 h-10 px-4 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed">
                        التالي <i class="fa-solid fa-chevron-left"></i>
                    </button>
                </div>

                <textarea name="content" id="content-editor" class="hidden">{{ old('content', $page->content) }}</textarea>
            </div>
        @else
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500">المحتوى</label>
                <textarea id="content-editor" name="content" rows="20" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm">{{ old('content', $page->content) }}</textarea>
                <p class="text-[11px] text-slate-400">سيظهر هذا المحتوى في الصفحة العامة بدل القالب الافتراضي.</p>
            </div>
        @endif

        <div class="border-t border-slate-100 pt-6">
            <h4 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="h-6 w-1 rounded-full bg-violet-500"></span>
                SEO
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500">SEO Title</label>
                    <input type="text" name="seo_title" value="{{ old('seo_title', $page->seo_title) }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500">SEO Keywords</label>
                    <input type="text" name="seo_keywords" value="{{ old('seo_keywords', $page->seo_keywords) }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                </div>
                <div class="space-y-2 md:col-span-2">
                    <label class="text-xs font-bold text-slate-500">SEO Description</label>
                    <textarea name="seo_description" rows="3" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm">{{ old('seo_description', $page->seo_description) }}</textarea>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500">OG Title</label>
                    <input type="text" name="og_title" value="{{ old('og_title', $page->og_title) }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500">OG Image</label>
                    <input type="file" name="og_image" accept="image/*" class="w-full text-sm">
                    @if($page->og_image_url)
                    <div class="flex items-center gap-4 mt-2">
                        <img src="{{ $page->og_image_url }}" alt="" class="h-16 w-auto object-cover rounded-lg">
                        <label class="flex items-center gap-2 text-sm text-rose-600 cursor-pointer">
                            <input type="checkbox" name="remove_og_image" value="1">
                            حذف الصورة
                        </label>
                    </div>
                    @endif
                </div>
                <div class="space-y-2 md:col-span-2">
                    <label class="text-xs font-bold text-slate-500">OG Description</label>
                    <textarea name="og_description" rows="2" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm">{{ old('og_description', $page->og_description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 border-t border-slate-100 pt-6">
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500">Canonical URL</label>
                <input type="url" name="canonical_url" value="{{ old('canonical_url', $page->canonical_url) }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500">الترتيب</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $page->sort_order ?? 0) }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
            </div>
            <div class="flex items-center gap-2 h-11 mt-6">
                <input type="checkbox" name="status" value="1" @checked(old('status', $page->status ?? true)) class="rounded">
                <label class="text-sm font-semibold text-slate-700">نشط</label>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('admin.pages.index') }}" class="h-11 px-6 bg-slate-100 text-slate-700 font-bold rounded-xl flex items-center">إلغاء</a>
            <button type="submit" class="h-11 px-8 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl transition-colors shadow-lg shadow-emerald-500/20">
                {{ $page->exists ? 'تحديث' : 'حفظ' }}
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    const IS_FAQS_PAGE = @json($isFaqsPage ?? false);

    if (!IS_FAQS_PAGE) {
        tinymce.init({
            selector: '#content-editor',
            license_key: 'gpl',
            height: 600,
            directionality: 'ltr',
            language: 'en',
            plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount emoticons codesample',
            toolbar: 'undo redo | blocks fontsize | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table codesample | removeformat code fullscreen preview',
            toolbar_mode: 'wrap',
            menubar: 'edit view insert format tools table help',
            image_advtab: true,
            branding: false,
            promotion: false,
            content_style: 'body { font-family: Inter, system-ui, sans-serif; font-size: 15px; line-height: 1.7; }',
        });
    } else {
        const list      = document.getElementById('faq-list');
        const hidden    = document.getElementById('content-editor');
        const form      = hidden.closest('form');
        const search    = document.getElementById('faq-search');
        const perPageEl = document.getElementById('faq-perpage');
        const pager     = document.getElementById('faq-pager');
        const prevBtn   = document.getElementById('faq-prev');
        const nextBtn   = document.getElementById('faq-next');
        const pageEl    = document.getElementById('faq-page');
        const pagesEl   = document.getElementById('faq-pages');
        const shownEl   = document.getElementById('faq-shown');
        const totalEl   = document.getElementById('faq-total');
        const emptyEl   = document.getElementById('faq-empty');

        let currentPage = 1;
        let perPage = parseInt(perPageEl.value, 10) || 20;

        const rows = () => Array.from(list.querySelectorAll('.faq-row'));

        const reindex = () => rows().forEach((row, i) => {
            row.querySelector('.faq-index').textContent = i + 1;
        });

        const matches = (row, term) => {
            if (!term) return true;
            const q = row.querySelector('.faq-q').value.toLowerCase();
            const a = row.querySelector('.faq-a').value.toLowerCase();
            const c = row.querySelector('.faq-cat').value.toLowerCase();
            return q.includes(term) || a.includes(term) || c.includes(term);
        };

        const render = () => {
            const term = (search.value || '').trim().toLowerCase();
            const all = rows();
            totalEl.textContent = all.length;

            const filtered = all.filter(r => matches(r, term));
            const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
            if (currentPage > totalPages) currentPage = totalPages;

            const start = (currentPage - 1) * perPage;
            const end = start + perPage;
            const visibleSet = new Set(filtered.slice(start, end));

            all.forEach(r => { r.style.display = visibleSet.has(r) ? '' : 'none'; });

            pageEl.textContent = currentPage;
            pagesEl.textContent = totalPages;
            shownEl.textContent = visibleSet.size;
            prevBtn.disabled = currentPage <= 1;
            nextBtn.disabled = currentPage >= totalPages;
            emptyEl.classList.toggle('hidden', filtered.length !== 0);
            pager.classList.toggle('hidden', filtered.length === 0);
        };

        const rowTemplate = () => {
            const wrap = document.createElement('div');
            wrap.className = 'faq-row bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-3';
            wrap.innerHTML = `
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500">سؤال <span class="faq-index"></span></span>
                    <button type="button" class="faq-remove text-rose-500 hover:text-rose-700 text-xs font-bold inline-flex items-center gap-1">
                        <i class="fa-solid fa-trash"></i> حذف
                    </button>
                </div>
                <input type="text" class="faq-q w-full h-11 px-4 bg-white border border-slate-200 rounded-xl text-sm" placeholder="السؤال">
                <textarea class="faq-a w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm" rows="3" placeholder="الإجابة"></textarea>
                <div class="flex items-center gap-2">
                    <input type="text" list="faq-categories" class="faq-cat flex-1 h-10 px-4 bg-white border border-slate-200 rounded-xl text-xs" placeholder="اختر تصنيف أو اكتب جديد…">
                    <button type="button" class="faq-cat-new inline-flex items-center gap-1 h-10 px-3 bg-violet-50 hover:bg-violet-100 text-violet-700 rounded-xl text-xs font-bold whitespace-nowrap">
                        <i class="fa-solid fa-plus"></i> تصنيف جديد
                    </button>
                </div>
            `;
            return wrap;
        };

        document.getElementById('faq-add').addEventListener('click', () => {
            const row = rowTemplate();
            list.appendChild(row);
            reindex();
            // Jump to last page so the new row is visible
            search.value = '';
            const totalPages = Math.max(1, Math.ceil(rows().length / perPage));
            currentPage = totalPages;
            render();
            row.querySelector('.faq-q')?.focus();
        });

        list.addEventListener('click', (e) => {
            const newBtn = e.target.closest('.faq-cat-new');
            if (newBtn) {
                const input = newBtn.parentElement.querySelector('.faq-cat');
                const val = (prompt('اسم التصنيف الجديد:', input.value || '') || '').trim();
                if (val) {
                    input.value = val;
                    const dl = document.getElementById('faq-categories');
                    if (dl && !Array.from(dl.options).some(o => o.value.toLowerCase() === val.toLowerCase())) {
                        const opt = document.createElement('option');
                        opt.value = val;
                        dl.appendChild(opt);
                    }
                }
                return;
            }

            const btn = e.target.closest('.faq-remove');
            if (!btn) return;
            const all = rows();
            if (all.length <= 1) {
                const row = btn.closest('.faq-row');
                row.querySelectorAll('input, textarea').forEach(el => el.value = '');
                render();
                return;
            }
            btn.closest('.faq-row').remove();
            reindex();
            render();
        });

        let searchTimer;
        search.addEventListener('input', () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => { currentPage = 1; render(); }, 120);
        });

        perPageEl.addEventListener('change', () => {
            perPage = parseInt(perPageEl.value, 10) || 20;
            currentPage = 1;
            render();
        });

        prevBtn.addEventListener('click', () => { if (currentPage > 1) { currentPage--; render(); window.scrollTo({ top: list.offsetTop - 80, behavior: 'smooth' }); } });
        nextBtn.addEventListener('click', () => { currentPage++; render(); window.scrollTo({ top: list.offsetTop - 80, behavior: 'smooth' }); });

        form.addEventListener('submit', () => {
            const items = [];
            rows().forEach(row => {
                const q = row.querySelector('.faq-q').value.trim();
                const a = row.querySelector('.faq-a').value.trim();
                const cat = row.querySelector('.faq-cat').value.trim();
                if (q && a) {
                    const item = { q, a };
                    if (cat) item.category = cat;
                    items.push(item);
                }
            });
            hidden.value = JSON.stringify(items);
        });

        render();
    }
</script>
@endpush
@endsection

