<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PaymentGateway extends Model
{
    protected $fillable = [
        'code', 'driver', 'name', 'logo', 'description',
        'is_active', 'sandbox', 'extra_fees',
        'allowed_countries', 'config', 'position',
    ];

    protected $casts = [
        'is_active'         => 'boolean',
        'sandbox'           => 'boolean',
        'extra_fees'        => 'float',
        'allowed_countries' => 'array',
        'config'            => 'array',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('payment_gateways.active'));
        static::deleted(fn () => Cache::forget('payment_gateways.active'));
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true)->orderBy('position');
    }

    public static function activeFor(?string $countryCode = null)
    {
        return Cache::remember('payment_gateways.active', 600, fn () =>
            static::active()->get()
        )->filter(function (self $g) use ($countryCode) {
            if (! $countryCode || empty($g->allowed_countries)) return true;
            return in_array(strtoupper($countryCode), array_map('strtoupper', $g->allowed_countries), true);
        })->values();
    }

    /** Read a single config value, falling back to env-based nafezly config */
    public function configValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->config, $key, $default);
    }
}
