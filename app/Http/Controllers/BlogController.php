<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogPost::published()->with('category:id,name,slug');

        if ($request->filled('category')) {
            $cat = Category::where('slug', $request->category)->first();
            if ($cat) $query->where('blog_category_id', $cat->id);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn($w) => $w->where('title', 'like', "%$q%")->orWhere('excerpt', 'like', "%$q%"));
        }

        $posts = $query->latest('published_at')->paginate(9)->withQueryString();
        $categories = Category::whereHas('blogPosts')->orderBy('name')->get(['id','name','slug']);

        return view('pages.blog.index', compact('posts', 'categories'));
    }

    public function show(string $slug)
    {
        $post = BlogPost::published()->with('category:id,name,slug')->where('slug', $slug)->firstOrFail();
        $post->increment('views');

        $related = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->where('blog_category_id', $post->blog_category_id)
            ->limit(3)->get();

        $ogImageUrl = $post->og_image
            ? asset('storage/'.$post->og_image)
            : ($post->image ? asset('storage/'.$post->image) : null);

        $seo = [
            'seo_title'       => $post->meta_title ?: $post->title,
            'seo_description' => $post->meta_description ?: $post->excerpt,
            'seo_keywords'    => $post->meta_keywords,
            'og_title'        => $post->meta_title ?: $post->title,
            'og_description'  => $post->meta_description ?: $post->excerpt,
            'og_image'        => $ogImageUrl,
            'canonical_url'   => $post->canonical_url ? url($post->canonical_url) : route('blog.show', $post->slug),
            'no_index'        => $post->no_index,
        ];

        return view('pages.blog.show', compact('post', 'related', 'seo'));
    }
}
