<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Product;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BlogPostController extends Controller
{
    public function index(Request $request)
    {
        $posts = BlogPost::with('category:id,name')
            ->when($request->q, fn ($q) => $q->where('title', 'like', "%{$request->q}%"))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.content.blog.index', compact('posts'));
    }

    public function create()
    {
        return view('admin.content.blog.form', [
            'post' => new BlogPost(),
            'categories' => $this->categoryTree(),
            'aiProducts' => Product::orderByDesc('id')->limit(200)->get(['id','name']),
        ]);
    }


    public function store(Request $request)
    {
        $data = $this->validated($request);
        if ($request->hasFile('image')) {
            $data['image'] = $this->storeAsWebp($request->file('image'), 'blog');
        }
        if ($request->hasFile('og_image')) {
            $data['og_image'] = $this->storeAsWebp($request->file('og_image'), 'blog/og');
        }
        $data['author_id'] = auth('admin')->id();
        BlogPost::create($data);

        return redirect()->route('admin.blog.index')->with('success', 'تم إنشاء المقال.');
    }

    public function edit(BlogPost $blog)
    {
        return view('admin.content.blog.form', [
            'post' => $blog,
            'categories' => $this->categoryTree(),
            'aiProducts' => Product::orderByDesc('id')->limit(200)->get(['id','name']),
        ]);
    }

    public function aiGenerate(Request $request)
    {
        $data = $request->validate([
            'title'            => ['nullable','string','max:255'],
            'blog_category_id' => ['nullable','exists:categories,id'],
            'product_id'       => ['nullable','exists:products,id'],
            'language'         => ['nullable','in:ar,en'],
        ]);

        if (!AiService::isEnabled()) {
            return response()->json([
                'ok' => false,
                'message' => 'الذكاء الاصطناعي غير مفعّل. فعّله من إعدادات الموقع → نماذج الذكاء الاصطناعي.',
            ], 422);
        }

        $lang = $data['language'] ?? 'ar';
        $category = !empty($data['blog_category_id']) ? Category::find($data['blog_category_id']) : null;
        $product  = !empty($data['product_id']) ? Product::with('category:id,name')->find($data['product_id']) : null;

        // إذا لم يحدد المستخدم تصنيفًا للمقال، استخدم تصنيف المنتج تلقائيًا
        if (!$category && $product && $product->category) {
            $category = $product->category;
        }

        $context = [];
        if (!empty($data['title']))  $context[] = 'العنوان المقترح: '.$data['title'];
        if ($category)                $context[] = 'التصنيف: '.$category->name;
        if ($product) {
            $context[] = 'المنتج المُراد الكتابة عنه: '.$product->name;
            if (!empty($product->short_description)) $context[] = 'وصف قصير: '.$product->short_description;
            if (!empty($product->description))       $context[] = 'وصف تفصيلي: '.\Illuminate\Support\Str::limit(strip_tags($product->description), 1500);
            if (isset($product->price))              $context[] = 'السعر: '.$product->price;
        }

        $system = $lang === 'ar'
            ? 'أنت كاتب محتوى محترف بالعربية الفصحى. تكتب مقالات مدوّنة عالية الجودة، متوافقة مع SEO، بتنسيق HTML نظيف (عناوين h2/h3، فقرات، قوائم عند الحاجة). لا تستخدم Markdown، فقط HTML. لا تكرر العنوان داخل المحتوى. إن انقطع المحتوى بسبب حد التوكنز أعد ما استطعت بصيغة JSON صالحة.'
            : 'You are a professional blog writer. Output clean SEO-friendly HTML (h2/h3, p, ul). No markdown, no code fences. Do not repeat the title inside the body. If you run out of tokens, still return valid JSON with whatever you produced.';

        $userPrompt = ($lang === 'ar'
                ? "اكتب مقالاً متكاملاً بناءً على المعلومات التالية:\n"
                : "Write a complete blog article based on the following:\n")
            .implode("\n", $context)
            ."\n\n"
            .($lang === 'ar'
                ? "أعد JSON صالحًا فقط بدون أي شرح خارجي بالشكل التالي:\n"
                  ."{\n  \"title\": \"عنوان جذاب\",\n  \"excerpt\": \"مقتطف قصير 1-2 جملة\",\n  \"content\": \"<p>...</p>\",\n  \"meta_title\": \"≤ 60 حرف\",\n  \"meta_description\": \"≤ 160 حرف\",\n  \"meta_keywords\": \"كلمة1, كلمة2, كلمة3\",\n  \"tags\": \"وسم1, وسم2, وسم3\"\n}"
                : "Return ONLY valid JSON with keys: title, excerpt, content (HTML), meta_title, meta_description, meta_keywords, tags."
            );

        try {
            $ai = new AiService();
            // الحد الأقصى للتوكنز يأخذ من الإعدادات (افتراضي 8000)، والمزوّد سيُرجع ما يستطيع فقط
            $maxTokens = max(512, (int) (site_setting('ai_max_tokens') ?: 8000));
            $messages = [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user',   'content' => $userPrompt],
            ];
            try {
                $raw = $ai->chat($messages, maxTokens: $maxTokens, temperature: 0.8, timeout: 120);
            } catch (\Throwable $e1) {
                // إعادة المحاولة تلقائيًا بالحد الأقصى المسموح به من الرصيد عند 402
                if (preg_match('/can only afford\s+(\d+)/i', $e1->getMessage(), $m)) {
                    $affordable = max(256, (int) $m[1] - 50);
                    $raw = $ai->chat($messages, maxTokens: $affordable, temperature: 0.8, timeout: 120);
                } else {
                    throw $e1;
                }
            }

            $json = trim($raw);
            $json = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $json);
            if (preg_match('/\{.*\}/s', $json, $m)) $json = $m[0];
            $parsed = json_decode($json, true);

            // إصلاح JSON المقطوع بسبب حد التوكنز
            if (!is_array($parsed) && !empty($json)) {
                $repaired = rtrim($json);
                $repaired = preg_replace('/,\s*$/', '', $repaired);
                if (substr_count($repaired, '{') > substr_count($repaired, '}')) {
                    $repaired .= str_repeat('"}', substr_count($repaired, '{') - substr_count($repaired, '}'));
                }
                $parsed = json_decode($repaired, true);
            }

            if (!is_array($parsed)) {
                $parsed = [
                    'title'   => $data['title'] ?? '',
                    'content' => '<p>'.nl2br(e(trim($raw))).'</p>',
                ];
            }

            $content = (string) ($parsed['content'] ?? '');

            // إضافة زر تسويق المنتج في نهاية المقال
            if ($product) {
                $productUrl = route('product.show', $product->slug ?? $product->id);
                $ctaTitle   = $lang === 'ar' ? 'تعرّف على '.$product->name : 'Discover '.$product->name;
                $ctaBtn     = $lang === 'ar' ? 'تسوّق المنتج الآن' : 'Shop the product';
                $priceLine  = isset($product->price) && $product->price
                    ? '<p style="margin:8px 0 16px;font-size:18px;font-weight:700;color:#0f766e;">'
                        .($lang === 'ar' ? 'السعر: ' : 'Price: ').e($product->price).'</p>'
                    : '';
                $content .= '
<div style="margin:32px 0;padding:24px;background:linear-gradient(135deg,#ecfeff,#eef2ff);border:1px solid #c7d2fe;border-radius:16px;text-align:center;">
  <h3 style="margin:0 0 8px;font-size:22px;font-weight:800;color:#1e293b;">'.e($ctaTitle).'</h3>
  '.$priceLine.'
  <a href="'.e($productUrl).'" style="display:inline-block;margin-top:8px;padding:12px 28px;background:#4f46e5;color:#fff;font-weight:700;border-radius:10px;text-decoration:none;">
    '.e($ctaBtn).' &larr;
  </a>
</div>';
            }

            return response()->json([
                'ok'   => true,
                'data' => [
                    'title'            => (string) ($parsed['title'] ?? $data['title'] ?? ''),
                    'excerpt'          => (string) ($parsed['excerpt'] ?? ''),
                    'content'          => $content,
                    'meta_title'       => mb_substr((string) ($parsed['meta_title'] ?? ''), 0, 60),
                    'meta_description' => mb_substr((string) ($parsed['meta_description'] ?? ''), 0, 160),
                    'meta_keywords'    => (string) ($parsed['meta_keywords'] ?? ''),
                    'tags'             => (string) ($parsed['tags'] ?? ''),
                    'blog_category_id' => $category?->id,
                ],
            ]);
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            $friendly = 'تعذّر توليد المقال. حاول مرة أخرى.';
            if (preg_match('/can only afford\s+(\d+)/i', $msg, $m) || str_contains($msg, '402')) {
                $afford = isset($m[1]) ? (int) $m[1] : null;
                $friendly = 'رصيد مزوّد الذكاء الاصطناعي غير كافٍ لإكمال هذا الطلب'
                    .($afford ? " (الحد المتاح حاليًا: {$afford} توكن)" : '')
                    .'. الحلول: 1) قلّل قيمة "ai_max_tokens" من إعدادات الموقع. '
                    .'2) اشحن رصيد OpenRouter من https://openrouter.ai/settings/credits. '
                    .'3) بدّل إلى Google Gemini المجاني (Base URL: https://generativelanguage.googleapis.com/v1beta و Model: gemini-flash-latest).';
            } elseif (preg_match('/\(401\)|invalid api key|unauthorized/i', $msg)) {
                $friendly = 'مفتاح API غير صحيح أو منتهي. حدّثه من إعدادات الموقع → نماذج الذكاء الاصطناعي.';
            } elseif (preg_match('/\(429\)|rate limit/i', $msg)) {
                $friendly = 'تم تجاوز حد الطلبات لحظيًا. انتظر دقيقة وحاول مجددًا.';
            } elseif (preg_match('/timeout|cURL error 28/i', $msg)) {
                $friendly = 'انتهت مهلة الاتصال بمزوّد الذكاء الاصطناعي. حاول مرة أخرى.';
            }
            return response()->json([
                'ok' => false,
                'message' => $friendly,
                'error' => $msg,
            ], 200);
        }
    }

    public function update(Request $request, BlogPost $blog)
    {
        $data = $this->validated($request, $blog->id);
        if ($request->hasFile('image')) {
            if ($blog->image) Storage::disk('public')->delete($blog->image);
            $data['image'] = $this->storeAsWebp($request->file('image'), 'blog');
        }
        if ($request->hasFile('og_image')) {
            if ($blog->og_image) Storage::disk('public')->delete($blog->og_image);
            $data['og_image'] = $this->storeAsWebp($request->file('og_image'), 'blog/og');
        }
        $blog->update($data);

        return redirect()->route('admin.blog.index')->with('success', 'تم تحديث المقال.');
    }

    /**
     * Store an uploaded image as .webp on the public disk. Falls back to the
     * original file if GD/WebP support is unavailable.
     */
    private function storeAsWebp(\Illuminate\Http\UploadedFile $file, string $dir, int $quality = 85): string
    {
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $slug = \Illuminate\Support\Str::slug($filename) ?: 'image';
        $name = $slug.'-'.uniqid().'.webp';
        $relative = trim($dir, '/').'/'.$name;

        if (!function_exists('imagewebp') || !function_exists('imagecreatefromstring')) {
            return $file->store($dir, 'public');
        }

        try {
            $img = @imagecreatefromstring(file_get_contents($file->getRealPath()));
            if (!$img) return $file->store($dir, 'public');

            // Preserve transparency
            imagepalettetotruecolor($img);
            imagealphablending($img, true);
            imagesavealpha($img, true);

            $tmp = tempnam(sys_get_temp_dir(), 'webp_');
            imagewebp($img, $tmp, $quality);
            imagedestroy($img);

            Storage::disk('public')->put($relative, file_get_contents($tmp));
            @unlink($tmp);
            return $relative;
        } catch (\Throwable $e) {
            return $file->store($dir, 'public');
        }
    }


    public function destroy(BlogPost $blog)
    {
        if ($blog->image) Storage::disk('public')->delete($blog->image);
        if ($blog->og_image) Storage::disk('public')->delete($blog->og_image);
        $blog->delete();
        return back()->with('success', 'تم الحذف.');
    }

    private function categoryTree()
    {
        return Category::orderBy('parent_id')->orderBy('name')->get(['id','name','parent_id']);
    }

    private function validated(Request $request, ?int $id = null): array
    {
        if ($request->filled('slug')) {
            $request->merge(['slug' => BlogPost::normalizeSlug($request->input('slug'))]);
        }

        $data = $request->validate([
            'blog_category_id' => ['nullable','exists:categories,id'],
            'title' => ['required','string','max:255'],
            'slug' => ['nullable','string','max:255', Rule::unique('blog_posts', 'slug')->ignore($id)],
            'excerpt' => ['nullable','string','max:500'],
            'content' => ['required','string'],
            'image' => ['nullable','image','max:4096'],
            'published_at' => ['nullable','date'],
            // SEO
            'meta_title' => ['nullable','string','max:255'],
            'meta_description' => ['nullable','string','max:320'],
            'meta_keywords' => ['nullable','string','max:255'],
            'og_image' => ['nullable','image','max:4096'],
            'no_index' => ['nullable','boolean'],
            'is_featured' => ['nullable','boolean'],
            'tags' => ['nullable','string','max:500'],
        ]);
        $data['no_index'] = $request->boolean('no_index');
        $data['is_featured'] = $request->boolean('is_featured');
        if (!empty($data['tags'])) {
            $parts = array_filter(array_map('trim', explode(',', $data['tags'])));
            $data['tags'] = implode(', ', $parts);
        }
        unset($data['canonical_url']);
        // Auto-publish if no date provided (so the post appears on /blog immediately)
        if (empty($data['published_at'])) {
            $data['published_at'] = now();
        }
        return $data;
    }
}

