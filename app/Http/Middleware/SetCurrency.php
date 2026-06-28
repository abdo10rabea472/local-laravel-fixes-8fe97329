<?php

namespace App\Http\Middleware;

use App\Services\CurrencyService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class SetCurrency
{
    public function __construct(protected CurrencyService $currencies) {}

    public function handle(Request $request, Closure $next)
    {
        $code = null;

        if (($q = $request->query('currency')) && $this->currencies->find($q)) {
            $code = strtoupper($q);
            cookie()->queue(cookie()->forever('currency', $code));
        }

        if (!$code && ($c = $request->cookie('currency')) && $this->currencies->find($c)) {
            $code = strtoupper($c);
        }

        $currency = $code ? $this->currencies->find($code) : $this->currencies->default();
        $this->currencies->setCurrent($currency);

        View::share('currentCurrency', $currency);
        View::share('availableCurrencies', $this->currencies->all());

        return $next($request);
    }
}
