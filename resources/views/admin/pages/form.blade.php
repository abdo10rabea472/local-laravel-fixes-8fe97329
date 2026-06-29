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
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-xs font-bold text-slate-500">الأسئلة والأجوبة</label>
                        <p class="text-[11px] text-slate-400 mt-1">أضف الأسئلة والأجوبة التي ستظهر في صفحة FAQs العامة.</p>
                    </div>
                    <button type="button" id="faq-add" class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold px-4 py-2 rounded-xl">
                        <i class="fa-solid fa-plus"></i> إضافة سؤال
                    </button>
                </div>

                <div id="faq-list" class="space-y-3">
                    @foreach($faqItems as $i => $item)
                    <div class="faq-row bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500">سؤال <span class="faq-index">{{ $i + 1 }}</span></span>
                            <button type="button" class="faq-remove text-rose-500 hover:text-rose-700 text-xs font-bold inline-flex items-center gap-1">
                                <i class="fa-solid fa-trash"></i> حذف
                            </button>
                        </div>
                        <input type="text" class="faq-q w-full h-11 px-4 bg-white border border-slate-200 rounded-xl text-sm" placeholder="السؤال" value="{{ $item['q'] }}">
                        <textarea class="faq-a w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm" rows="3" placeholder="الإجابة">{{ $item['a'] }}</textarea>
                        <input type="text" class="faq-cat w-full h-10 px-4 bg-white border border-slate-200 rounded-xl text-xs" placeholder="التصنيف (اختياري) مثل: Shipping, Payment" value="{{ $item['category'] }}">
                    </div>
                    @endforeach
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
        const list = document.getElementById('faq-list');
        const hidden = document.getElementById('content-editor');
        const form = hidden.closest('form');

        const reindex = () => list.querySelectorAll('.faq-row').forEach((row, i) => {
            row.querySelector('.faq-index').textContent = i + 1;
        });

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
                <input type="text" class="faq-cat w-full h-10 px-4 bg-white border border-slate-200 rounded-xl text-xs" placeholder="التصنيف (اختياري)">
            `;
            return wrap;
        };

        document.getElementById('faq-add').addEventListener('click', () => {
            list.appendChild(rowTemplate());
            reindex();
        });

        list.addEventListener('click', (e) => {
            const btn = e.target.closest('.faq-remove');
            if (!btn) return;
            if (list.querySelectorAll('.faq-row').length <= 1) {
                const row = btn.closest('.faq-row');
                row.querySelectorAll('input, textarea').forEach(el => el.value = '');
                return;
            }
            btn.closest('.faq-row').remove();
            reindex();
        });

        form.addEventListener('submit', () => {
            const items = [];
            list.querySelectorAll('.faq-row').forEach(row => {
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
    }
</script>
@endpush
@endsection

