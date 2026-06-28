<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    protected $fillable = [
        'blog_category_id','author_id','title','slug','image',
        'excerpt','content','views','published_at',
        'meta_title','meta_description','meta_keywords','og_image','canonical_url','no_index',
        'is_featured','tags',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'no_index' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function getTagsArrayAttribute(): array
    {
        if (empty($this->tags)) return [];
        return array_values(array_filter(array_map('trim', explode(',', $this->tags))));
    }

    protected static function booted(): void
    {
        static::saving(function ($p) {
            $p->slug = static::uniqueSlug($p->slug ?: $p->title, $p->id);
            $p->canonical_url = 'blog/'.$p->slug;

            if (empty($p->published_at)) {
                $p->published_at = now();
            }
        });

        static::created(function ($p) {
            try {
                NewsletterSubscriber::where('active', true)
                    ->orderBy('id')
                    ->chunk(100, function ($rows) use ($p) {
                        foreach ($rows as $sub) {
                            try {
                                \Illuminate\Support\Facades\Mail::to($sub->email)
                                    ->send(new \App\Mail\NewsletterArticleMail($p));
                            } catch (\Throwable $e) {
                                \Log::warning('Newsletter auto-send failed for '.$sub->email.': '.$e->getMessage());
                            }
                        }
                    });
            } catch (\Throwable $e) {
                \Log::warning('Newsletter auto-send error: '.$e->getMessage());
            }
        });
    }


    public static function normalizeSlug(?string $value): string
    {
        $value = trim((string) $value);

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $value = parse_url($value, PHP_URL_PATH) ?: $value;
        }

        $value = urldecode(preg_replace('/[?#].*$/', '', $value) ?? $value);
        $value = trim($value, "/ \t\n\r\0\x0B");
        $value = preg_replace('#^(?:index\.php/)?blog/#iu', '', $value) ?? $value;

        if (str_contains($value, '/')) {
            $parts = preg_split('#/+#', $value, -1, PREG_SPLIT_NO_EMPTY);
            $value = $parts ? end($parts) : $value;
        }

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

    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? asset('storage/'.$this->image)
            : (site_setting_url('default_product_image') ?: asset('imges/products/default.jpg'));
    }

    public function scopePublished($q)
    {
        return $q->where(function ($query) {
            $query->whereNull('published_at')
                ->orWhere('published_at', '<=', now());
        });
    }

    public function comments(): HasMany
    {
        return $this->hasMany(BlogComment::class)->latest();
    }

    public function approvedComments(): HasMany
    {
        return $this->hasMany(BlogComment::class)->where('approved', true)->latest();
    }
}
