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

    /**
     * Path segments that must NEVER be locale-prefixed (admin, APIs, webhooks,
     * payment callbacks, storage assets, health checks, locale switcher itself).
     */
    protected array $excluded = [
        'admin', 'api', 'storage', 'build', 'vendor', 'up',
        'locale', 'currency', 'payments', 'livewire', 'broadcasting',
    ];

    public function handle(Request $request, Closure $next)
    {
        $first = $request->segment(1);

        // ── Case A: URL has NO locale prefix ─────────────────────────────────
        if (!$first || !$this->languages->exists($first)) {
            // Only auto-redirect plain GET page requests to /{locale}/...
            if (
                $request->isMethod('GET')
                && !$request->ajax()
                && !$request->expectsJson()
                && !in_array($first, $this->excluded, true)
            ) {
                $locale = $this->resolveLocale($request);
                if ($locale) {
                    $path  = $request->getPathInfo();
                    $qs    = $request->getQueryString();
                    $target = '/' . $locale . ($path === '/' ? '' : $path) . ($qs ? '?' . $qs : '');
                    return redirect($target, 302);
                }
            }
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

        // 3) Force generated URLs to include /{locale}, but keep asset()/Vite
        //    pointing to the un-prefixed host so CSS/JS/images still resolve.
        config(['app.asset_url' => $request->getSchemeAndHttpHost()]);
        URL::forceRootUrl($request->getSchemeAndHttpHost() . '/' . $locale);

        // Expose for SetLocale middleware.
        $request->attributes->set('url_locale', $locale);
        app()->setLocale($locale);

        return $next($request);
    }
}
