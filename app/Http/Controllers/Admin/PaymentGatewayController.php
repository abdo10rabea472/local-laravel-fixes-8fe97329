<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentGatewayController extends Controller
{
    public function index(): View
    {
        $gateways = PaymentGateway::orderBy('position')->get();
        return view('admin.settings.payment-gateways.index', compact('gateways'));
    }

    public function edit(PaymentGateway $gateway): View
    {
        return view('admin.settings.payment-gateways.edit', compact('gateway'));
    }

    public function update(Request $request, PaymentGateway $gateway): RedirectResponse
    {
        $data = $request->validate([
            'name'              => 'required|string|max:150',
            'description'       => 'nullable|string|max:255',
            'logo'              => 'nullable|string|max:255',
            'sandbox'           => 'nullable|boolean',
            'extra_fees'        => 'nullable|numeric|min:0',
            'allowed_countries' => 'nullable|string|max:500',
            'config'            => 'nullable|array',
            'config.*'          => 'nullable|string|max:500',
        ]);

        $allowed = collect(explode(',', (string) ($data['allowed_countries'] ?? '')))
            ->map(fn ($c) => strtoupper(trim($c)))
            ->filter()->values()->all();

        $gateway->update([
            'name'              => $data['name'],
            'description'       => $data['description'] ?? null,
            'logo'              => $data['logo'] ?? null,
            'sandbox'           => (bool) ($data['sandbox'] ?? false),
            'extra_fees'        => (float) ($data['extra_fees'] ?? 0),
            'allowed_countries' => $allowed ?: null,
            'config'            => array_filter($data['config'] ?? [], fn ($v) => $v !== null && $v !== ''),
        ]);

        return back()->with('success', 'تم حفظ إعدادات البوابة.');
    }

    public function toggle(PaymentGateway $gateway): RedirectResponse
    {
        $gateway->update(['is_active' => ! $gateway->is_active]);
        return back()->with('success', $gateway->is_active ? 'تم تفعيل البوابة.' : 'تم تعطيل البوابة.');
    }

    public function test(PaymentGateway $gateway, PaymentService $service): JsonResponse
    {
        return response()->json($service->testConnection($gateway));
    }
}
