<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingCountry;
use App\Models\ShippingRegion;
use App\Models\SiteSetting;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShippingRateController extends Controller
{
    public function __construct(private ImageService $imageService)
    {
    }

    public function index(): View
    {
        $countries = ShippingCountry::with('regions')->orderBy('position')->orderBy('name')->get();
        $freeShippingEnabled = SiteSetting::get('free_shipping_enabled', '1') === '1';
        $freeThreshold = SiteSetting::get('free_shipping_threshold', '2000');

        return view('admin.settings.shipping', [
            'countries' => $countries,
            'freeShippingEnabled' => $freeShippingEnabled,
            'freeThreshold' => $freeThreshold,
            'freeShippingShowInHeader' => SiteSetting::get('free_shipping_show_in_header', '1') === '1',
            'freeShippingPopupEnabled' => SiteSetting::get('free_shipping_popup_enabled', '0') === '1',
            'freeShippingPopupTitle' => SiteSetting::get('free_shipping_popup_title', 'Free Shipping Available!'),
            'freeShippingPopupMessage' => SiteSetting::get('free_shipping_popup_message', 'Enjoy free shipping on all orders above our minimum threshold.'),
            'activeTab' => 'shipping',
        ]);
    }


    // ───── Countries ─────
    public function storeCountry(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:shipping_countries,name',
            'cost' => 'nullable|numeric|min:0',
            'position' => 'nullable|integer|min:0',
        ]);
        $data['status'] = $request->boolean('status', true);
        $data['position'] = $data['position'] ?? 0;

        ShippingCountry::create($data);
        SiteSetting::clearCache();

        return back()->with('success', 'تم إضافة الدولة بنجاح.');
    }

    public function updateCountry(Request $request, ShippingCountry $country): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:shipping_countries,name,' . $country->id,
            'cost' => 'nullable|numeric|min:0',
            'position' => 'nullable|integer|min:0',
        ]);
        $data['status'] = $request->boolean('status', true);

        $country->update($data);
        SiteSetting::clearCache();

        return back()->with('success', 'تم تحديث الدولة بنجاح.');
    }

    public function destroyCountry(ShippingCountry $country): RedirectResponse
    {
        $country->delete();
        SiteSetting::clearCache();

        return back()->with('success', 'تم حذف الدولة بنجاح.');
    }

    // ───── Regions ─────
    public function storeRegion(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'country_id' => 'required|exists:shipping_countries,id',
            'name' => 'required|string|max:100',
            'cost' => 'nullable|numeric|min:0',
            'position' => 'nullable|integer|min:0',
        ]);
        $data['status'] = $request->boolean('status', true);
        $data['position'] = $data['position'] ?? 0;

        ShippingRegion::create($data);
        SiteSetting::clearCache();

        return back()->with('success', 'تم إضافة المنطقة بنجاح.');
    }

    public function updateRegion(Request $request, ShippingRegion $region): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'cost' => 'nullable|numeric|min:0',
            'position' => 'nullable|integer|min:0',
        ]);
        $data['status'] = $request->boolean('status', true);

        $region->update($data);
        SiteSetting::clearCache();

        return back()->with('success', 'تم تحديث المنطقة بنجاح.');
    }

    public function destroyRegion(ShippingRegion $region): RedirectResponse
    {
        $region->delete();
        SiteSetting::clearCache();

        return back()->with('success', 'تم حذف المنطقة بنجاح.');
    }

    // ───── Free shipping settings ─────
    public function updateThreshold(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'free_shipping_popup_title' => 'nullable|string|max:150',
            'free_shipping_popup_message' => 'nullable|string|max:500',
            'free_shipping_popup_image' => 'nullable|image|max:4096',
        ]);

        $this->saveSetting('free_shipping_enabled', $request->boolean('free_shipping_enabled') ? '1' : '0', 'تفعيل الشحن المجاني');
        $this->saveSetting('free_shipping_threshold', (string) ($data['free_shipping_threshold'] ?? '0'), 'حد الشحن المجاني');
        $this->saveSetting('free_shipping_show_in_header', $request->boolean('free_shipping_show_in_header') ? '1' : '0', 'إظهار الشحن المجاني في الهيدر');
        $this->saveSetting('free_shipping_popup_enabled', $request->boolean('free_shipping_popup_enabled') ? '1' : '0', 'تفعيل نموذج الشحن المجاني');
        $this->saveSetting('free_shipping_popup_title', (string) ($data['free_shipping_popup_title'] ?? ''), 'عنوان نموذج الشحن المجاني');
        $this->saveSetting('free_shipping_popup_message', (string) ($data['free_shipping_popup_message'] ?? ''), 'رسالة نموذج الشحن المجاني');

        // Image handling
        $imageSetting = SiteSetting::firstOrNew(['key' => 'free_shipping_popup_image']);
        if ($request->hasFile('free_shipping_popup_image')) {
            if ($imageSetting->value) {
                $this->imageService->deletePaths($imageSetting->value);
            }
            $imageSetting->value = $this->imageService->storeSettingImage($request->file('free_shipping_popup_image'), 'free_shipping_popup_image');
            $imageSetting->type = 'image';
            $imageSetting->label = $imageSetting->label ?: 'صورة نموذج الشحن المجاني';
            $imageSetting->group = 'shipping';
            $imageSetting->save();
        } elseif ($request->boolean('remove_free_shipping_popup_image') && $imageSetting->value) {
            $this->imageService->deletePaths($imageSetting->value);
            $imageSetting->value = null;
            $imageSetting->save();
        }

        SiteSetting::clearCache();

        return back()->with('success', 'تم تحديث إعدادات الشحن المجاني بنجاح.');
    }

    private function saveSetting(string $key, string $value, string $label): void
    {
        $setting = SiteSetting::firstOrNew(['key' => $key]);
        $setting->value = $value;
        $setting->label = $setting->label ?: $label;
        $setting->group = 'shipping';
        $setting->save();
    }
}

