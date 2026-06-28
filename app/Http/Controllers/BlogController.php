<?php

namespace App\Http\Controllers;

use App\Models\BlogComment;
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

        if ($request->filled('tag')) {
            $tag = $request->tag;
            $query->where('tags', 'like', "%{$tag}%");
        }

        $posts = $query->withCount(['approvedComments as comments_count'])
            ->latest('published_at')->paginate(8)->withQueryString();
        $categories = Category::whereHas('blogPosts')->orderBy('name')->get(['id','name','slug']);
        $featured = BlogPost::published()->where('is_featured', true)->latest('published_at')->first()
            ?: BlogPost::published()->latest('published_at')->first();
        $popular = BlogPost::published()->orderByDesc('views')->limit(5)->get(['id','title','slug','image','views','published_at']);

        // Collect all unique tags from published posts
        $tags = BlogPost::published()->whereNotNull('tags')->where('tags','!=','')->pluck('tags')
            ->flatMap(fn($t) => array_filter(array_map('trim', explode(',', $t))))
            ->unique()->values()->take(30);

        return view('pages.blog.index', compact('posts', 'categories', 'featured', 'popular', 'tags'));
    }

    public function show(string $slug)
    {
        $post = BlogPost::published()
            ->with(['category:id,name,slug', 'approvedComments'])
            ->where('slug', $slug)->firstOrFail();
        $post->increment('views');

        $related = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->when($post->blog_category_id, fn($q) => $q->where('blog_category_id', $post->blog_category_id))
            ->latest('published_at')
            ->limit(3)->get();

        // Fallback: if not enough related in the same category, fill with latest posts
        if ($related->count() < 3) {
            $extra = BlogPost::published()
                ->where('id', '!=', $post->id)
                ->whereNotIn('id', $related->pluck('id'))
                ->latest('published_at')
                ->limit(3 - $related->count())->get();
            $related = $related->concat($extra);
        }

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
            'canonical_url'   => route('blog.show', $post->slug),
            'no_index'        => $post->no_index,
        ];

        return view('pages.blog.show', compact('post', 'related', 'seo'));
    }

    public function storeComment(Request $request, string $slug)
    {
        $post = BlogPost::where('slug', $slug)->firstOrFail();

        $user = auth()->user();

        $data = $request->validate([
            'name'  => [$user ? 'nullable' : 'required', 'string', 'max:120'],
            'email' => [$user ? 'nullable' : 'required', 'email', 'max:160'],
            'body'  => ['required','string','min:3','max:2000'],
        ]);

        BlogComment::create([
            'blog_post_id' => $post->id,
            'user_id'      => $user?->id,
            'name'         => $user?->name ?: ($data['name'] ?? 'زائر'),
            'email'        => $user?->email ?: ($data['email'] ?? null),
            'body'         => $data['body'],
            'approved'     => true,
        ]);

        return back()->with('success', 'تم نشر تعليقك. شكراً لمشاركتك!')->withFragment('comments');
    }
}
