<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'image',
        'banner',
        'description',
        'primary_color',
        'secondary_color',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'canonical_url',
        'og_title',
        'og_description',
        'og_image',
        'schema_markup',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Category $category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'blog_category_id');
    }


    public function sections(): HasMany
    {
        return $this->hasMany(PageSection::class)->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function getSeoTitleAttribute($value): string
    {
        return $value ?: ($this->name ?? '');
    }

    public function getSeoDescriptionAttribute($value): ?string
    {
        return $value ?: $this->description;
    }

    public function isCollege(): bool
    {
        return $this->parent_id === null;
    }

    public function getIconUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http') || str_starts_with($this->image, '/')) {
            return $this->image;
        }

        if (str_starts_with($this->image, 'imges/') || str_starts_with($this->image, './imges/')) {
            return asset(ltrim($this->image, './'));
        }

        return asset('storage/' . $this->image);
    }

    public function getBannerUrlAttribute(): ?string
    {
        if (! $this->banner) {
            return null;
        }

        if (str_starts_with($this->banner, 'http') || str_starts_with($this->banner, '/')) {
            return $this->banner;
        }

        if (str_starts_with($this->banner, 'imges/') || str_starts_with($this->banner, './imges/')) {
            return asset(ltrim($this->banner, './'));
        }

        return asset('storage/' . $this->banner);
    }

    public function descendantIds(): array
    {
        $ids = [$this->id];

        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->descendantIds());
        }

        return $ids;
    }
}
