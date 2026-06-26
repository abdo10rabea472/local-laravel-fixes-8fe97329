<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingCarrier extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'auto_track' => 'boolean',
        'default_cost' => 'decimal:2',
    ];

    protected $hidden = ['api_key', 'webhook_secret'];


    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function buildTrackingUrl(?string $tracking): ?string
    {
        if (!$tracking || !$this->tracking_url_template) return null;
        return str_replace('{tracking}', urlencode($tracking), $this->tracking_url_template);
    }
}
