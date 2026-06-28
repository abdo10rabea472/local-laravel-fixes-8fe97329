<?php

use App\Models\SiteSetting;
use App\Services\CurrencyService;
use App\Services\LanguageService;

if (! function_exists('site_setting')) {
    function site_setting(string $key, ?string $default = null): ?string
    {
        return SiteSetting::get($key, $default);
    }
}

if (! function_exists('site_setting_url')) {
    function site_setting_url(string $key, ?string $default = null): ?string
    {
        return SiteSetting::getUrl($key, $default);
    }
}

if (! function_exists('current_locale')) {
    function current_locale(): string
    {
        return app()->getLocale();
    }
}

if (! function_exists('current_language')) {
    function current_language(): ?\App\Models\Language
    {
        return app(LanguageService::class)->find(current_locale())
            ?? app(LanguageService::class)->default();
    }
}

if (! function_exists('available_languages')) {
    function available_languages()
    {
        return app(LanguageService::class)->all();
    }
}

if (! function_exists('current_currency')) {
    function current_currency(): ?\App\Models\Currency
    {
        return app(CurrencyService::class)->current();
    }
}

if (! function_exists('available_currencies')) {
    function available_currencies()
    {
        return app(CurrencyService::class)->all();
    }
}

if (! function_exists('money')) {
    function money(float|int|string|null $amount, ?\App\Models\Currency $currency = null): string
    {
        return app(CurrencyService::class)->format((float) ($amount ?? 0), $currency);
    }
}

if (! function_exists('convert_price')) {
    function convert_price(float|int|string|null $amount, ?\App\Models\Currency $to = null): float
    {
        return app(CurrencyService::class)->convert((float) ($amount ?? 0), $to);
    }
}

if (! function_exists('switch_locale_url')) {
    function switch_locale_url(string $code, ?string $url = null): string
    {
        return app(LanguageService::class)->switchUrl($code, $url);
    }
}

if (! function_exists('is_rtl')) {
    function is_rtl(): bool
    {
        $lang = current_language();
        return $lang ? $lang->isRtl() : false;
    }
}
