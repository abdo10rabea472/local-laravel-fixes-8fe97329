<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('blog', 'public');
        }
        if ($request->hasFile('og_image')) {
            $data['og_image'] = $request->file('og_image')->store('blog/og', 'public');
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
        ]);
    }

    public function update(Request $request, BlogPost $blog)
    {
        $data = $this->validated($request, $blog->id);
        if ($request->hasFile('image')) {
            if ($blog->image) Storage::disk('public')->delete($blog->image);
            $data['image'] = $request->file('image')->store('blog', 'public');
        }
        if ($request->hasFile('og_image')) {
            if ($blog->og_image) Storage::disk('public')->delete($blog->og_image);
            $data['og_image'] = $request->file('og_image')->store('blog/og', 'public');
        }
        $blog->update($data);

        return redirect()->route('admin.blog.index')->with('success', 'تم تحديث المقال.');
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
        $data = $request->validate([
            'blog_category_id' => ['nullable','exists:categories,id'],
            'title' => ['required','string','max:255'],
            'slug' => ['nullable','string','max:255','unique:blog_posts,slug,'.($id ?? 'NULL')],
            'excerpt' => ['nullable','string','max:500'],
            'content' => ['required','string'],
            'image' => ['nullable','image','max:4096'],
            'published_at' => ['nullable','date'],
            // SEO
            'meta_title' => ['nullable','string','max:255'],
            'meta_description' => ['nullable','string','max:320'],
            'meta_keywords' => ['nullable','string','max:255'],
            'og_image' => ['nullable','image','max:4096'],
            'canonical_url' => ['nullable','url','max:255'],
            'no_index' => ['nullable','boolean'],
        ]);
        $data['no_index'] = $request->boolean('no_index');
        return $data;
    }
}

