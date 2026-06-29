<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $cat = trim((string) $request->get('category', ''));
        $perPage = (int) $request->get('per_page', 5);
        if (!in_array($perPage, [5, 10, 20, 30, 50, 100], true)) $perPage = 5;

        $query = Faq::query()->orderBy('category')->orderBy('sort_order');
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('question', 'like', "%{$q}%")
                  ->orWhere('answer', 'like', "%{$q}%");
            });
        }
        if ($cat !== '') {
            $query->where('category', $cat);
        }
        $faqs = $query->paginate($perPage)->withQueryString();
        $usedCats = Faq::query()->whereNotNull('category')->where('category', '!=', '')
            ->distinct()->orderBy('category')->pluck('category');
        $customCats = collect(json_decode(\App\Models\SiteSetting::get('faq_categories', '[]'), true) ?: []);
        $categories = $usedCats->merge($customCats)->unique()->values();

        $seoPage = Page::firstOrCreate(
            ['slug' => 'faqs'],
            ['title' => 'Frequently Asked Questions', 'content' => '', 'active' => true]
        );

        return view('admin.content.faqs.index', compact('faqs', 'q', 'cat', 'perPage', 'categories', 'seoPage'));
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100']]);
        $name = trim($data['name']);
        $list = collect(json_decode(\App\Models\SiteSetting::get('faq_categories', '[]'), true) ?: []);
        $used = Faq::query()->distinct()->pluck('category')->filter();
        if (! $list->merge($used)->contains($name)) {
            $list->push($name);
            \App\Models\SiteSetting::updateOrCreate(
                ['key' => 'faq_categories'],
                ['value' => json_encode($list->values()->all(), JSON_UNESCAPED_UNICODE), 'type' => 'json', 'group' => 'faqs']
            );
            \App\Models\SiteSetting::clearCache();
        }
        return back()->with('success', 'تمت إضافة التصنيف.');
    }

    public function destroyCategory(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100']]);
        $name = $data['name'];
        $list = collect(json_decode(\App\Models\SiteSetting::get('faq_categories', '[]'), true) ?: [])
            ->reject(fn ($v) => $v === $name)->values();
        \App\Models\SiteSetting::updateOrCreate(
            ['key' => 'faq_categories'],
            ['value' => json_encode($list->all(), JSON_UNESCAPED_UNICODE), 'type' => 'json', 'group' => 'faqs']
        );
        \App\Models\SiteSetting::clearCache();
        return back()->with('success', 'تم حذف التصنيف من القائمة.');
    }


    public function updateSeo(Request $request)
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'seo_keywords' => ['nullable', 'string', 'max:500'],
        ]);
        $page = Page::firstOrCreate(['slug' => 'faqs'], ['title' => 'FAQs', 'content' => '', 'active' => true]);
        $page->fill(array_filter($data, fn ($v) => $v !== null))->save();
        $this->clearCache();
        return back()->with('success', 'تم تحديث بيانات السيو.');
    }

    public function store(Request $request)
    {
        Faq::create($this->validated($request));
        $this->clearCache();
        return back()->with('success', 'تمت الإضافة.');
    }

    public function update(Request $request, Faq $faq)
    {
        $faq->update($this->validated($request));
        $this->clearCache();
        return back()->with('success', 'تم التحديث.');
    }

    public function toggle(Faq $faq)
    {
        $faq->update(['active' => ! $faq->active]);
        $this->clearCache();
        return back();
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();
        $this->clearCache();
        return back()->with('success', 'تم الحذف.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'category' => ['nullable','string','max:100'],
            'question' => ['required','string','max:500'],
            'answer' => ['required','string'],
            'sort_order' => ['nullable','integer'],
            'active' => ['nullable','boolean'],
        ]) + ['active' => $request->boolean('active')];
    }

    private function clearCache(): void
    {
        Cache::forget('faqs.grouped');
        Cache::forget('faqs.public');
    }
}
