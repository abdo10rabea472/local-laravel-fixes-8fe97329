<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index()
    {
        $currencies = Currency::orderBy('sort_order')->orderBy('id')->get();
        $activeTab = 'currencies';
        return view('admin.settings.currencies', compact('currencies', 'activeTab'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['is_default'] = (bool) ($data['is_default'] ?? false);
        $data['is_active']  = (bool) ($data['is_active'] ?? true);
        $data['code'] = strtoupper($data['code']);
        Currency::create($data);
        return back()->with('success', 'Currency created.');
    }

    public function update(Request $request, Currency $currency)
    {
        $data = $this->validateData($request, $currency->id);
        $data['is_default'] = (bool) ($data['is_default'] ?? false);
        $data['is_active']  = (bool) ($data['is_active'] ?? false);
        $data['code'] = strtoupper($data['code']);
        $currency->update($data);
        return back()->with('success', 'Currency updated.');
    }

    public function destroy(Currency $currency)
    {
        if ($currency->is_default) {
            return back()->with('error', 'Cannot delete the default currency.');
        }
        $currency->delete();
        return back()->with('success', 'Currency deleted.');
    }

    public function setDefault(Currency $currency)
    {
        $currency->update(['is_default' => true, 'is_active' => true, 'exchange_rate' => 1]);
        return back()->with('success', 'Default currency updated.');
    }

    protected function validateData(Request $r, ?int $ignoreId = null): array
    {
        return $r->validate([
            'name'                => ['required', 'string', 'max:80'],
            'code'                => ['required', 'string', 'max:10', 'alpha', 'unique:currencies,code' . ($ignoreId ? ",{$ignoreId}" : '')],
            'symbol'              => ['required', 'string', 'max:10'],
            'symbol_position'     => ['required', 'in:before,after'],
            'decimals'            => ['required', 'integer', 'min:0', 'max:6'],
            'decimal_separator'   => ['required', 'string', 'max:4'],
            'thousands_separator' => ['nullable', 'string', 'max:4'],
            'exchange_rate'       => ['required', 'numeric', 'min:0.00000001'],
            'sort_order'          => ['nullable', 'integer', 'min:0'],
            'is_default'          => ['nullable', 'boolean'],
            'is_active'           => ['nullable', 'boolean'],
        ]);
    }
}
