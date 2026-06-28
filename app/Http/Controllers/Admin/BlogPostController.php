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
        $product  = !empty($data['product_id']) ? Product::find($data['product_id']) : null;

        $context = [];
        if (!empty($data['title']))  $context[] = 'العنوان المقترح: '.$data['title'];
        if ($category)                $context[] = 'التصنيف: '.$category->name;
        if ($product) {
            $context[] = 'المنتج المُراد الكتابة عنه: '.$product->name;
            if (!empty($product->short_description)) $context[] = 'وصف قصير: '.$product->short_description;
            if (!empty($product->description))       $context[] = 'وصف تفصيلي: '.\Illuminate\Support\Str::limit(strip_tags($product->description), 1200);
            if (isset($product->price))              $context[] = 'السعر: '.$product->price;
        }

        $system = $lang === 'ar'
            ? 'أنت كاتب محتوى محترف بالعربية الفصحى. تكتب مقالات مدوّنة عالية الجودة، متوافقة مع SEO، بتنسيق HTML نظيف (عناوين h2/h3، فقرات، قوائم عند الحاجة). لا تستخدم Markdown، فقط HTML. لا تكرر العنوان داخل المحتوى.'
            : 'You are a professional blog writer. Output clean SEO-friendly HTML (h2/h3, p, ul). No markdown, no code fences. Do not repeat the title inside the body.';

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
            $raw = $ai->chat([
                ['role' => 'system', 'content' => $system],
                ['role' => 'user',   'content' => $userPrompt],
            ], maxTokens: 4096, temperature: 0.8, timeout: 90);

            $json = trim($raw);
            $json = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $json);
            if (preg_match('/\{.*\}/s', $json, $m)) $json = $m[0];
            $parsed = json_decode($json, true);

            if (!is_array($parsed)) {
                $parsed = [
                    'title'   => $data['title'] ?? '',
                    'content' => '<p>'.nl2br(e(trim($raw))).'</p>',
                ];
            }

            return response()->json([
                'ok'   => true,
                'data' => [
                    'title'            => (string) ($parsed['title'] ?? $data['title'] ?? ''),
                    'excerpt'          => (string) ($parsed['excerpt'] ?? ''),
                    'content'          => (string) ($parsed['content'] ?? ''),
                    'meta_title'       => mb_substr((string) ($parsed['meta_title'] ?? ''), 0, 60),
                    'meta_description' => mb_substr((string) ($parsed['meta_description'] ?? ''), 0, 160),
                    'meta_keywords'    => (string) ($parsed['meta_keywords'] ?? ''),
                    'tags'             => (string) ($parsed['tags'] ?? ''),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'تعذّر توليد المقال',
                'error' => $e->getMessage(),
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

