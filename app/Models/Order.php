<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    public const STATUSES = ['pending','paid','shipped','delivered','cancelled','refunded'];

    protected $guarded = [];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderByDesc('id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function generateNumber(): string
    {
        return 'ORD-' . now()->format('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }

    public function statusBadgeColor(): string
    {
        return match ($this->status) {
            'pending' => 'amber',
            'paid' => 'sky',
            'shipped' => 'indigo',
            'delivered' => 'emerald',
            'cancelled' => 'rose',
            'refunded' => 'slate',
            default => 'slate',
        };
    }

    public function statusLabel(): string
    {
        return [
            'pending' => 'قيد الانتظار',
            'paid' => 'مدفوع',
            'shipped' => 'تم الشحن',
            'delivered' => 'تم التوصيل',
            'cancelled' => 'ملغي',
            'refunded' => 'مسترد',
        ][$this->status] ?? $this->status;
    }
}
