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
        $perPage = (int) $request->get('per_page', 30);
        if (!in_array($perPage, [20, 30, 50, 100], true)) $perPage = 30;

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
        $categories = Faq::query()->whereNotNull('category')->where('category', '!=', '')
            ->distinct()->orderBy('category')->pluck('category');

        $seoPage = Page::firstOrCreate(
            ['slug' => 'faqs'],
            ['title' => 'Frequently Asked Questions', 'content' => '', 'active' => true]
        );

        return view('admin.content.faqs.index', compact('faqs', 'q', 'cat', 'perPage', 'categories', 'seoPage'));
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
