<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductDiscount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductDiscountController extends Controller
{
    public function index(Request $request): View
    {
        $query = ProductDiscount::with('product')->latest();

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'expired') {
                $query->whereNotNull('ends_at')->where('ends_at', '<', now());
            }
        }

        $discounts = $query->paginate(20)->withQueryString();
        $products = Product::orderBy('name')->get(['id', 'name', 'price']);

        return view('admin.discounts.products.index', compact('discounts', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        ProductDiscount::create($data);

        return back()->with('success', 'تم إضافة الخصم بنجاح.');
    }

    public function update(Request $request, ProductDiscount $discount): RedirectResponse
    {
        $data = $this->validateData($request);
        $discount->update($data);

        return back()->with('success', 'تم تحديث الخصم بنجاح.');
    }

    public function toggle(ProductDiscount $discount): RedirectResponse
    {
        $discount->update(['is_active' => ! $discount->is_active]);
        return back()->with('success', 'تم تحديث حالة الخصم.');
    }

    public function destroy(ProductDiscount $discount): RedirectResponse
    {
        $discount->delete();
        return back()->with('success', 'تم حذف الخصم.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        return $data;
    }
}
