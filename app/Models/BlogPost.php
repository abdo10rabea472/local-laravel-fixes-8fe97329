<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    protected $fillable = [
        'blog_category_id','author_id','title','slug','image',
        'excerpt','content','views','published_at',
        'meta_title','meta_description','meta_keywords','og_image','canonical_url','no_index',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'no_index' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function ($p) {
            $p->slug = static::uniqueSlug($p->slug ?: $p->title, $p->id);

            if (empty($p->published_at)) {
                $p->published_at = now();
            }
        });
    }

    public static function normalizeSlug(?string $value): string
    {
        $value = trim((string) $value);

        $slug = Str::slug($value);

        if ($slug === '') {
            $slug = preg_replace('/[^\pL\pN]+/u', '-', $value) ?: '';
            $slug = trim($slug, '-');
            $slug = preg_replace('/-+/u', '-', $slug) ?: '';
        }

        return $slug !== '' ? mb_strtolower($slug, 'UTF-8') : 'post-'.Str::lower(Str::random(8));
    }

    public static function uniqueSlug(?string $value, ?int $ignoreId = null): string
    {
        $base = static::normalizeSlug($value);
        $slug = $base;
        $counter = 2;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    public function category(): BelongsTo
    {
        // Now points to product Category table (shared taxonomy).
        return $this->belongsTo(Category::class, 'blog_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'author_id');
    }

    public function scopePublished($q)
    {
        return $q->whereNotNull('published_at')->where('published_at', '<=', now());
    }
}
