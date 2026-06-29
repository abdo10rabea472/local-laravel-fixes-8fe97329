<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(private ImageService $imageService)
    {
    }

    private const SYSTEM_SLUGS = ['about', 'faqs', 'privacy-policy', 'returns-refunds'];
    private const HIDDEN_SLUGS = ['checkout', 'faqs'];

    public function index(): View
    {
        $pages = Page::whereNotIn('slug', self::HIDDEN_SLUGS)
            ->orderBy('sort_order')->orderBy('title')->paginate(20);

        return view('admin.pages.index', compact('pages') + [
            'activeTab' => 'pages',
            'systemSlugs' => self::SYSTEM_SLUGS,
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.form', ['page' => new Page(), 'activeTab' => 'pages']);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePage($request);
        $validated['status'] = $request->boolean('status', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        if ($request->hasFile('og_image')) {
            $validated['og_image'] = $this->imageService->storeSettingImage($request->file('og_image'), 'og');
        }

        Page::create($validated);

        return redirect()->route('admin.pages.index')->with('success', 'تم إضافة الصفحة بنجاح.');
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.form', compact('page') + ['activeTab' => 'pages']);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $validated = $this->validatePage($request, $page->id);
        $validated['status'] = $request->boolean('status', true);

        if ($request->hasFile('og_image')) {
            if ($page->og_image) {
                $this->imageService->deletePaths($page->og_image);
            }
            $validated['og_image'] = $this->imageService->storeSettingImage($request->file('og_image'), 'og');
        } elseif ($request->has('remove_og_image')) {
            if ($page->og_image) {
                $this->imageService->deletePaths($page->og_image);
            }
            $validated['og_image'] = null;
        }

        $page->update($validated);

        return redirect()->route('admin.pages.index')->with('success', 'تم تحديث الصفحة بنجاح.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        if (in_array($page->slug, self::SYSTEM_SLUGS, true)) {
            return redirect()->route('admin.pages.index')
                ->with('error', 'This is a system page and cannot be deleted. You can disable it instead.');
        }

        if ($page->og_image) {
            $this->imageService->deletePaths($page->og_image);
        }

        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'تم حذف الصفحة بنجاح.');
    }

    private function validatePage(Request $request, ?int $ignoreId = null): array
    {
        $slugRule = 'required|string|max:255|unique:pages,slug';
        if ($ignoreId) {
            $slugRule .= ',' . $ignoreId;
        }

        return $request->validate([
            'slug' => $slugRule,
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'seo_keywords' => 'nullable|string',
            'canonical_url' => 'nullable|url|max:255',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string',
            'og_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'slug.required' => 'المعرف (slug) مطلوب.',
            'slug.unique' => 'المعرف مستخدم من قبل.',
            'title.required' => 'عنوان الصفحة مطلوب.',
        ]);
    }
}
