<?php

namespace App\Http\Middleware;

use App\Services\LanguageService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;

class SetLocale
{
    public function __construct(protected LanguageService $languages) {}

    public function handle(Request $request, Closure $next)
    {
        $code = null;

        // 1) URL prefix /{locale}/...
        $first = $request->segment(1);
        if ($first && $this->languages->exists($first)) {
            $code = $first;
        }

        // 2) ?lang= override (persists in cookie)
        if (!$code && ($q = $request->query('lang')) && $this->languages->exists($q)) {
            $code = $q;
            cookie()->queue(cookie()->forever('locale', $code));
        }

        // 3) Cookie
        if (!$code && ($c = $request->cookie('locale')) && $this->languages->exists($c)) {
            $code = $c;
        }

        // 4) Default
        if (!$code) {
            $code = optional($this->languages->default())->code ?? config('app.locale');
        }

        App::setLocale($code);
        $language = $this->languages->find($code);

        View::share('currentLocale', $code);
        View::share('currentLanguage', $language);
        View::share('availableLanguages', $this->languages->all());

        return $next($request);
    }
}
