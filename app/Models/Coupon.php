<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'starts_at', 'ends_at',
        'min_order_total', 'max_discount_amount', 'usage_limit',
        'used_count', 'scope', 'is_active', 'description',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_order_total' => 'decimal:2',
            'max_discount_amount' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'coupon_products');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'coupon_categories');
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function scopeActive($query)
    {
        $now = now();
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
            ->where(fn ($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'));
    }

    /**
     * Validate this coupon against a cart and identity.
     * $cart = array of ['id'=>productId, 'price'=>x, 'quantity'=>n]
     * Returns ['ok'=>bool, 'message'=>string, 'discount'=>float, 'subtotal'=>float]
     */
    public function validateFor(array $cart, ?int $userId = null, ?string $email = null, ?string $phone = null): array
    {
        if (! $this->is_active) {
            return ['ok' => false, 'message' => 'كود الخصم غير مفعل.', 'discount' => 0];
        }
        $now = now();
        if ($this->starts_at && $this->starts_at->gt($now)) {
            return ['ok' => false, 'message' => 'كود الخصم لم يبدأ بعد.', 'discount' => 0];
        }
        if ($this->ends_at && $this->ends_at->lt($now)) {
            return ['ok' => false, 'message' => 'كود الخصم منتهي الصلاحية.', 'discount' => 0];
        }
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return ['ok' => false, 'message' => 'تم استنفاد الحد الأقصى لاستخدامات هذا الكود.', 'discount' => 0];
        }

        // Per-user / per-identity uniqueness
        $redeemed = CouponRedemption::where('coupon_id', $this->id)
            ->where(function ($q) use ($userId, $email, $phone) {
                if ($userId) $q->orWhere('user_id', $userId);
                if ($email) $q->orWhere('email', strtolower(trim($email)));
                if ($phone) $q->orWhere('phone', preg_replace('/\s+/', '', $phone));
            })->exists();
        if ($redeemed) {
            return ['ok' => false, 'message' => 'لقد استخدمت هذا الكود من قبل.', 'discount' => 0];
        }

        // Determine eligible items based on scope
        $eligibleIds = collect($cart)->pluck('id')->map(fn ($i) => (int) $i)->all();
        if ($this->scope === 'products') {
            $allowed = $this->products()->pluck('products.id')->all();
            $eligibleIds = array_values(array_intersect($eligibleIds, $allowed));
        } elseif ($this->scope === 'categories') {
            $allowedCats = $this->categories()->pluck('categories.id')->all();
            $allowed = Product::whereIn('category_id', $allowedCats)->pluck('id')->all();
            $eligibleIds = array_values(array_intersect($eligibleIds, $allowed));
        }

        if (empty($eligibleIds)) {
            return ['ok' => false, 'message' => 'الكود غير صالح للمنتجات الموجودة في السلة.', 'discount' => 0];
        }

        $eligibleSubtotal = 0.0;
        $cartTotal = 0.0;
        foreach ($cart as $item) {
            $line = ((float) ($item['price'] ?? 0)) * ((int) ($item['quantity'] ?? 1));
            $cartTotal += $line;
            if (in_array((int) ($item['id'] ?? 0), $eligibleIds, true)) {
                $eligibleSubtotal += $line;
            }
        }

        if ($this->min_order_total !== null && $cartTotal < (float) $this->min_order_total) {
            return [
                'ok' => false,
                'message' => 'الحد الأدنى للطلب لاستخدام هذا الكود هو ' . number_format((float) $this->min_order_total, 2) . ' EGP.',
                'discount' => 0,
            ];
        }

        $discount = $this->type === 'percent'
            ? round($eligibleSubtotal * ((float) $this->value / 100), 2)
            : min($eligibleSubtotal, (float) $this->value);

        if ($this->max_discount_amount !== null) {
            $discount = min($discount, (float) $this->max_discount_amount);
        }

        return [
            'ok' => true,
            'message' => 'تم تطبيق كود الخصم بنجاح.',
            'discount' => round($discount, 2),
            'subtotal' => $cartTotal,
        ];
    }
}
