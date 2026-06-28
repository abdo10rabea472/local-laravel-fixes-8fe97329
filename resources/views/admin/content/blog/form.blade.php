@extends('admin.layouts.app')
@section('title', $post->exists ? 'تعديل مقال' : 'مقال جديد')

@section('content')
<x-admin.page
    :title="$post->exists ? 'تعديل مقال' : 'مقال جديد'"
    :subtitle="$post->exists ? 'تعديل بيانات المقال وإعدادات الـ SEO.' : 'إنشاء مقال جديد للمدونة.'"
    :back="route('admin.blog.index')"
    backLabel="العودة للمقالات">

    <form method="POST" action="{{ $post->exists ? route('admin.blog.update', $post) : route('admin.blog.store') }}" enctype="multipart/form-data" class="space-y-6" id="blog-form">
        @csrf @if($post->exists) @method('PUT') @endif

        @if($errors->any())
            <div class="bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-400 p-4 rounded-xl border border-rose-200 dark:border-rose-900">
                <ul class="list-disc pr-5 text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- ✨ AI Generator --}}
        <x-admin.card title="الكتابة بالذكاء الاصطناعي" icon="fa-wand-magic-sparkles">
            <div class="space-y-4">
                <p class="text-xs text-gray-500">
                    اختر منتجًا (اختياري) واكتب عنوانًا مقترحًا أو اتركه فارغًا، ثم اضغط
                    <b>"كتابة المقال بالذكاء الاصطناعي"</b> ليتم توليد العنوان، المقتطف، المحتوى، وبيانات الـ SEO تلقائيًا.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1.5">المنتج (اختياري)</label>
                        <select id="ai-product-id" class="w-full h-11 px-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm">
                            <option value="">— بدون منتج —</option>
                            @foreach(($aiProducts ?? collect()) as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1.5">اللغة</label>
                        <select id="ai-language" class="w-full h-11 px-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm">
                            <option value="ar" selected>العربية</option>
                            <option value="en">English</option>
                        </select>
                    </div>
                </div>
                <button type="button" id="ai-generate-btn"
                        class="w-full md:w-auto h-12 px-6 inline-flex items-center justify-center gap-2 bg-gradient-to-l from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-violet-500/20">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <span id="ai-generate-label">كتابة المقال بالذكاء الاصطناعي</span>
                </button>
                <div id="ai-generate-result" class="hidden p-3 rounded-xl text-sm"></div>
            </div>
        </x-admin.card>

        {{-- Main content --}}
        <x-admin.card title="بيانات المقال" icon="fa-pen-to-square">
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1.5">العنوان *</label>
                    <input id="title" name="title" value="{{ old('title', $post->title) }}" required
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1.5">Slug (الرابط)</label>
                    <div class="flex items-stretch" dir="ltr">
                        <span class="inline-flex items-center px-3 bg-gray-100 dark:bg-dark-700 border border-r-0 border-gray-200 dark:border-gray-700 rounded-l-xl text-xs text-gray-600 dark:text-gray-300 font-mono">{{ rtrim(url('/blog'), '/') }}/</span>
                        <input id="slug" name="slug" value="{{ old('slug', $post->slug) }}" dir="ltr" placeholder="your-slug"
                               class="flex-1 h-11 px-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-r-xl text-sm font-mono focus:border-primary-500 focus:outline-none">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">اكتب الجزء الأخير فقط من الرابط، أو اتركه فارغًا لتوليده تلقائيًا.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1.5">مقتطف قصير</label>
                    <textarea name="excerpt" rows="2" maxlength="500"
                              class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">{{ old('excerpt', $post->excerpt) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1.5">التسميات (Tags)</label>
                    <input name="tags" value="{{ old('tags', $post->tags) }}" placeholder="laravel, php, seo"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                    <p class="text-xs text-gray-500 mt-1">افصل بين التسميات بفاصلة. ستظهر كفلاتر في صفحة المدونة.</p>
                </div>

                <label class="flex items-center gap-2 text-sm cursor-pointer select-none">
                    <input type="hidden" name="is_featured" value="0">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $post->is_featured)) class="accent-primary-600 w-4 h-4">
                    <span class="font-bold text-gray-700 dark:text-gray-200"><i class="fa-solid fa-star text-amber-500 me-1"></i> مقال مميز (يظهر في القائمة الجانبية)</span>
                </label>

                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1.5">المحتوى *</label>
                    <textarea id="content-editor" name="content" rows="20"
                              class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm">{{ old('content', $post->content) }}</textarea>
                </div>
            </div>
        </x-admin.card>

    </form>

    <x-slot:side>
        <x-admin.card title="النشر" icon="fa-paper-plane">
            <div class="space-y-3">
                <button form="blog-form" class="w-full h-12 inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/20">
                    <i class="fa-solid fa-save"></i> {{ $post->exists ? 'حفظ التعديلات' : 'نشر المقال' }}
                </button>
                <a href="{{ route('admin.blog.index') }}" class="w-full h-11 inline-flex items-center justify-center bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-gray-200 rounded-xl text-sm font-bold">إلغاء</a>
                @if($post->exists && $post->published_at)
                    <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="w-full h-11 inline-flex items-center justify-center gap-2 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-bold">
                        <i class="fa-solid fa-eye"></i> عرض المقال
                    </a>
                @endif
            </div>
        </x-admin.card>

        <x-admin.card title="التصنيف" icon="fa-folder">
            <select form="blog-form" name="blog_category_id" class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                <option value="">— بدون تصنيف —</option>
                @php $byParent = $categories->groupBy('parent_id'); @endphp
                @foreach($byParent->get(null, collect())->merge($byParent->get(0, collect())) as $root)
                    <option value="{{ $root->id }}" @selected(old('blog_category_id', $post->blog_category_id) == $root->id)>{{ $root->name }}</option>
                    @foreach($byParent->get($root->id, []) as $child)
                        <option value="{{ $child->id }}" @selected(old('blog_category_id', $post->blog_category_id) == $child->id)>— {{ $child->name }}</option>
                    @endforeach
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-2">من تصنيفات المنتجات.</p>
        </x-admin.card>

        <x-admin.card title="تاريخ النشر" icon="fa-calendar">
            <input form="blog-form" type="datetime-local" name="published_at"
                   value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}"
                   class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
            <p class="text-xs text-gray-500 mt-2">اتركه فارغًا للنشر فورًا.</p>
        </x-admin.card>

        <x-admin.card title="الصورة الرئيسية" icon="fa-image">
            <input form="blog-form" type="file" name="image" accept="image/*"
                   class="w-full text-sm file:mr-3 file:px-4 file:py-2 file:border-0 file:rounded-lg file:bg-primary-50 file:text-primary-700 file:font-bold file:cursor-pointer">
            @if($post->image)
                <img src="{{ asset('storage/'.$post->image) }}" class="mt-3 w-full rounded-xl shadow">
            @endif
        </x-admin.card>

        {{-- SEO (sidebar) --}}
        <x-admin.card title="تحسين محركات البحث (SEO)" icon="fa-magnifying-glass">
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1.5">Meta Title</label>
                    <input form="blog-form" name="meta_title" value="{{ old('meta_title', $post->meta_title) }}" maxlength="60"
                           class="w-full h-11 px-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none"
                           oninput="document.getElementById('mt-count').innerText=this.value.length">
                    <p class="text-xs text-gray-500 mt-1">≤ 60 — <span id="mt-count">{{ strlen($post->meta_title ?? '') }}</span>/60</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1.5">Meta Description</label>
                    <textarea form="blog-form" name="meta_description" rows="3" maxlength="160"
                              class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none"
                              oninput="document.getElementById('md-count').innerText=this.value.length">{{ old('meta_description', $post->meta_description) }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">≤ 160 — <span id="md-count">{{ strlen($post->meta_description ?? '') }}</span>/160</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1.5">Keywords</label>
                    <input form="blog-form" name="meta_keywords" value="{{ old('meta_keywords', $post->meta_keywords) }}" placeholder="laravel, php, seo" dir="ltr"
                           class="w-full h-11 px-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-mono focus:border-primary-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1.5">صورة المشاركة (OG Image)</label>
                    <input form="blog-form" type="file" name="og_image" accept="image/*"
                           class="w-full text-xs file:mr-2 file:px-3 file:py-1.5 file:border-0 file:rounded-lg file:bg-primary-50 file:text-primary-700 file:font-bold file:cursor-pointer">
                    @if($post->og_image)<img src="{{ asset('storage/'.$post->og_image) }}" class="mt-2 w-full rounded-lg shadow">@endif
                    <p class="text-xs text-gray-500 mt-1">1200×630 يُوصى به.</p>
                </div>

                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input form="blog-form" type="hidden" name="no_index" value="0">
                    <input form="blog-form" type="checkbox" name="no_index" value="1" @checked(old('no_index', $post->no_index)) class="accent-primary-600">
                    منع الفهرسة (noindex)
                </label>

                {{-- SERP preview --}}
                <div class="mt-3 p-3 bg-gray-50 dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-gray-700">
                    <p class="text-[10px] font-bold text-gray-500 dark:text-gray-400 mb-2">معاينة Google:</p>
                    <div class="bg-white p-2 rounded-lg border" dir="ltr">
                        <p class="text-[10px] text-emerald-700 truncate" id="serp-url">{{ url('/blog/'.($post->slug ?: 'your-slug')) }}</p>
                        <p class="text-blue-700 text-sm leading-tight truncate" id="serp-title">{{ $post->meta_title ?: ($post->title ?: 'عنوان المقال') }}</p>
                        <p class="text-xs text-slate-600 line-clamp-2" id="serp-desc">{{ $post->meta_description ?: ($post->excerpt ?: 'وصف المقال يظهر هنا...') }}</p>
                    </div>
                </div>
            </div>
        </x-admin.card>
    </x-slot:side>
</x-admin.page>

{{-- Tom Select for searchable selects --}}
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        ['ai-product-id'].forEach(id => {
            const el = document.getElementById(id);
            if (el && !el.tomselect) new TomSelect(el, { create:false, allowEmptyOption:true, placeholder:'ابحث عن المنتج...' });
        });
        const catEl = document.querySelector('[name="blog_category_id"]');
        if (catEl && !catEl.tomselect) new TomSelect(catEl, { create:false, allowEmptyOption:true, placeholder:'اختر تصنيفًا...' });
    });
</script>

{{-- TinyMCE rich editor --}}
<script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#content-editor',
        license_key: 'gpl',
        height: 600,
        directionality: 'rtl',
        language: 'ar',
        language_url: 'https://cdn.jsdelivr.net/npm/tinymce-i18n@latest/langs7/ar.js',
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount emoticons codesample',
        toolbar: 'undo redo | blocks fontsize | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table codesample | removeformat code fullscreen preview',
        toolbar_mode: 'wrap',
        menubar: 'edit view insert format tools table help',
        image_advtab: true,
        branding: false,
        promotion: false,
        content_style: 'body { font-family: Inter, system-ui, sans-serif; font-size: 15px; line-height: 1.7; }',
    });

    document.querySelector('[name=meta_title]')?.addEventListener('input', e => {
        document.getElementById('serp-title').textContent = e.target.value || document.getElementById('title').value || 'عنوان المقال';
    });
    document.querySelector('[name=meta_description]')?.addEventListener('input', e => {
        document.getElementById('serp-desc').textContent = e.target.value || 'وصف المقال يظهر هنا...';
    });

    const slugInput = document.getElementById('slug');
    const serpUrl = document.getElementById('serp-url');
    const blogBaseUrl = @json(rtrim(url('/blog'), '/'));
    const previewSlug = value => {
        value = (value || '').trim().replace(/[?#].*$/, '').replace(/^https?:\/\/[^/]+\/?/i, '').replace(/^\/?(?:index\.php\/)?blog\//i, '').replace(/^\/+|\/+$/g, '');
        if (value.includes('/')) value = value.split('/').filter(Boolean).pop() || '';
        return value || 'your-slug';
    };
    slugInput?.addEventListener('input', e => {
        serpUrl.textContent = `${blogBaseUrl}/${previewSlug(e.target.value)}`;
    });

    // Auto-fill slug from title when slug is empty (supports Arabic)
    const titleInput = document.getElementById('title');
    const slugify = (str) => {
        return (str || '').toString().trim().toLowerCase()
            .replace(/[\u064B-\u065F\u0670]/g, '')      // strip Arabic diacritics
            .replace(/[^\p{L}\p{N}]+/gu, '-')           // non letters/numbers -> dash
            .replace(/^-+|-+$/g, '')
            .replace(/-+/g, '-');
    };
    let slugTouched = !!(slugInput && slugInput.value);
    slugInput?.addEventListener('input', () => { slugTouched = true; });
    titleInput?.addEventListener('input', e => {
        if (slugTouched) return;
        const s = slugify(e.target.value);
        if (slugInput) {
            slugInput.value = s;
            serpUrl.textContent = `${blogBaseUrl}/${previewSlug(s)}`;
        }
    });

    // ✨ AI article generator
    (function(){
        const btn = document.getElementById('ai-generate-btn');
        if (!btn) return;
        const label  = document.getElementById('ai-generate-label');
        const out    = document.getElementById('ai-generate-result');
        const url    = @json(route('admin.blog.ai-generate'));
        const token  = document.querySelector('meta[name="csrf-token"]')?.content;

        const setField = (name, value) => {
            if (value === undefined || value === null || value === '') return;
            const el = document.querySelector(`[name="${name}"]`);
            if (!el) return;
            el.value = value;
            el.dispatchEvent(new Event('input', { bubbles: true }));
        };

        const showMsg = (ok, text) => {
            out.classList.remove('hidden');
            out.className = 'mt-2 p-3 rounded-xl text-sm border ' +
                (ok ? 'bg-emerald-50 border-emerald-200 text-emerald-800'
                    : 'bg-rose-50 border-rose-200 text-rose-700');
            out.innerHTML = text;
        };

        btn.addEventListener('click', async () => {
            const payload = {
                title: document.getElementById('title')?.value || '',
                blog_category_id: document.querySelector('[name="blog_category_id"]')?.value || '',
                product_id: document.getElementById('ai-product-id')?.value || '',
                language: document.getElementById('ai-language')?.value || 'ar',
            };

            btn.disabled = true;
            label.textContent = 'جارٍ التوليد...';
            btn.classList.add('opacity-70');
            showMsg(true, '<i class="fa-solid fa-spinner fa-spin"></i> جارٍ توليد المقال من الذكاء الاصطناعي...');

            try {
                const res  = await fetch(url, {
                    method: 'POST',
                    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':token,'Accept':'application/json'},
                    body: JSON.stringify(payload),
                });
                const data = await res.json();
                if (!data.ok) {
                    showMsg(false, '<b><i class="fa-solid fa-circle-xmark"></i> '+(data.message || 'فشل التوليد')+'</b>' +
                        (data.error ? '<pre dir="ltr" class="mt-2 text-xs whitespace-pre-wrap opacity-80">'+data.error+'</pre>' : ''));
                    return;
                }
                const d = data.data || {};
                setField('title', d.title);
                setField('excerpt', d.excerpt);
                setField('meta_title', d.meta_title);
                setField('meta_description', d.meta_description);
                setField('meta_keywords', d.meta_keywords);
                setField('tags', d.tags);
                // ضبط تصنيف المقال تلقائيًا من تصنيف المنتج
                if (d.blog_category_id) {
                    const catEl = document.querySelector('[name="blog_category_id"]');
                    if (catEl) {
                        catEl.value = d.blog_category_id;
                        if (catEl.tomselect) catEl.tomselect.setValue(d.blog_category_id, true);
                        catEl.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
                if (window.tinymce && tinymce.get('content-editor')) {
                    tinymce.get('content-editor').setContent(d.content || '');
                } else {
                    setField('content', d.content);
                }
                showMsg(true, '<b><i class="fa-solid fa-circle-check"></i> تم توليد المقال بنجاح. يمكنك المراجعة والتعديل قبل الحفظ.</b>');
            } catch (e) {
                showMsg(false, '<b>تعذّر الاتصال بالخادم</b><pre dir="ltr" class="mt-2 text-xs">'+e.message+'</pre>');
            } finally {
                btn.disabled = false;
                label.textContent = 'كتابة المقال بالذكاء الاصطناعي';
                btn.classList.remove('opacity-70');
            }
        });
    })();
</script>
@endsection
