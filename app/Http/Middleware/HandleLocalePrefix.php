<?php

namespace App\Http\Middleware;

use App\Services\LanguageService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

/**
 * Global middleware (runs before routing).
 *
 * If the URL starts with /{locale}/... and {locale} is a known language code,
 * we:
 *   1. Strip the prefix from the request URI so the existing routes match.
 *   2. Persist the locale cookie.
 *   3. Force URL::forceRootUrl so every generated URL (route(), url(), asset
 *      links via url('/...')) keeps the /{locale} prefix.
 */
class HandleLocalePrefix
{
    public function __construct(protected LanguageService $languages) {}

    public function handle(Request $request, Closure $next)
    {
        $first = $request->segment(1);

        if (!$first || !$this->languages->exists($first)) {
            return $next($request);
        }

        // Don't rewrite admin / api / asset paths — they shouldn't be locale-prefixed.
        // (Only applies if someone hits /en/admin which we still want to support; comment kept for clarity.)

        $locale = $first;

        // 1) Strip the locale segment from the request URI.
        $newPath = '/' . ltrim(substr($request->getPathInfo(), strlen('/' . $locale)), '/');
        if ($newPath === '') {
            $newPath = '/';
        }

        $qs = $request->getQueryString();
        $newUri = $newPath . ($qs ? ('?' . $qs) : '');

        $request->server->set('REQUEST_URI', $newUri);
        $request->server->set('PATH_INFO', $newPath);
        // Reset cached values on the request instance.
        $request->initialize(
            $request->query->all(),
            $request->request->all(),
            $request->attributes->all(),
            $request->cookies->all(),
            $request->files->all(),
            $request->server->all(),
            $request->getContent()
        );

        // 2) Persist locale cookie so subsequent requests without prefix still work.
        cookie()->queue(cookie()->forever('locale', $locale));

        // 3) Force generated URLs to include /{locale}.
        URL::forceRootUrl($request->getSchemeAndHttpHost() . '/' . $locale);

        // Expose for SetLocale middleware.
        $request->attributes->set('url_locale', $locale);
        app()->setLocale($locale);

        return $next($request);
    }
}
