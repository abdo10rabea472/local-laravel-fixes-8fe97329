<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;


class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'price',
        'sale_price',
        'stock',
        'featured',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'canonical_url',
        'og_title',
        'og_description',
        'og_image',
        'schema_markup',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'featured' => 'boolean',
            'status' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(ProductDiscount::class);
    }

    public function activeDiscount(): HasOne
    {
        return $this->hasOne(ProductDiscount::class)->active()->latestOfMany();
    }

    public function scopeWithActiveDiscount($query)
    {
        return $query->whereHas('discounts', fn ($q) => $q->active());
    }

    /**
     * Standard column selection + eager-loads used by every public listing
     * (home, catalog, category). Keeps the storefront list queries consistent.
     */
    public function scopeForListing($query)
    {
        return $query
            ->select(['id', 'name', 'slug', 'price', 'sale_price', 'stock', 'category_id', 'short_description', 'featured'])
            ->with([
                'category:id,name,slug',
                'images' => fn ($q) => $q->select(['id', 'product_id', 'thumb', 'medium', 'image'])->orderBy('sort_order'),
                'activeDiscount',
            ]);
    }

    public function getEffectivePriceAttribute(): float
    {
        $base = (float) ($this->sale_price ?? $this->price);
        $discount = $this->resolveActiveDiscount();
        return $discount ? $discount->applyTo($base) : $base;
    }

    public function getOriginalPriceAttribute(): float
    {
        return (float) $this->price;
    }

    public function getCompareAtPriceAttribute(): ?float
    {
        $base = (float) ($this->sale_price ?? $this->price);
        $final = $this->effective_price;
        if ($final < $base) {
            return $base;
        }
        if ($this->sale_price && $this->sale_price < $this->price) {
            return (float) $this->price;
        }
        return null;
    }

    public function getDiscountPercentAttribute(): int
    {
        $base = (float) $this->price;
        $final = $this->effective_price;
        if ($base <= 0 || $final >= $base) return 0;
        return (int) round((($base - $final) / $base) * 100);
    }

    public function getHasActiveDiscountAttribute(): bool
    {
        return $this->discount_percent > 0;
    }

    protected function resolveActiveDiscount(): ?ProductDiscount
    {
        // Always cache via the relation slot to avoid repeated DB hits
        // when multiple accessors (effective_price, compare_at_price,
        // discount_percent) read this on the same model instance.
        if (! $this->relationLoaded('activeDiscount')) {
            $this->setRelation('activeDiscount', $this->activeDiscount()->first());
        }
        return $this->getRelation('activeDiscount');
    }


    public function getPrimaryImageAttribute(): ?ProductImage
    {
        return $this->images->first();
    }

    public function getSeoTitleAttribute($value): string
    {
        return $value ?: ($this->name ?? '');
    }

    public function getSeoDescriptionAttribute($value): ?string
    {
        return $value ?: $this->short_description ?: Str::limit(strip_tags($this->description ?? ''), 160);
    }

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }
}
