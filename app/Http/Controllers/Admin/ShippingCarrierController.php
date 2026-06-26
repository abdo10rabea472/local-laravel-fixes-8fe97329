<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingCarrier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShippingCarrierController extends Controller
{
    public function index()
    {
        $carriers = ShippingCarrier::orderBy('sort_order')->orderBy('name')->get();
        return view('admin.shipping-carriers.index', compact('carriers'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        ShippingCarrier::create($data);
        return back()->with('success', 'تم إضافة شركة الشحن.');
    }

    public function update(Request $request, ShippingCarrier $carrier)
    {
        $data = $this->validateData($request, $carrier->id);
        $carrier->update($data);
        return back()->with('success', 'تم تحديث شركة الشحن.');
    }

    public function destroy(ShippingCarrier $carrier)
    {
        $carrier->delete();
        return back()->with('success', 'تم حذف شركة الشحن.');
    }

    public function toggle(ShippingCarrier $carrier)
    {
        $carrier->update(['is_active' => !$carrier->is_active]);
        return back();
    }

    private function validateData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => ['required','string','max:120'],
            'code' => ['required','string','max:50', Rule::unique('shipping_carriers','code')->ignore($id)],
            'tracking_url_template' => ['nullable','string','max:500'],
            'contact_phone' => ['nullable','string','max:30'],
            'contact_email' => ['nullable','email','max:120'],
            'default_cost' => ['nullable','numeric','min:0'],
            'is_active' => ['nullable','boolean'],
            'sort_order' => ['nullable','integer','min:0'],
        ]);
    }
}
