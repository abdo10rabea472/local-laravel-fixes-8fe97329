<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(Request $request): View
    {
        $query = Coupon::withCount('redemptions')->latest();

        if ($request->filled('q')) {
            $query->where('code', 'like', '%' . $request->q . '%');
        }
        if ($request->filled('status')) {
            if ($request->status === 'active') $query->where('is_active', true);
            if ($request->status === 'inactive') $query->where('is_active', false);
        }

        $coupons = $query->paginate(20)->withQueryString();
        return view('admin.coupons.index', compact('coupons'));
    }

    public function create(): View
    {
        $coupon = new Coupon(['type' => 'percent', 'scope' => 'all', 'is_active' => true]);
        return view('admin.coupons.form', [
            'coupon' => $coupon,
            'products' => Product::orderBy('name')->get(['id', 'name']),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'selectedProducts' => [],
            'selectedCategories' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $coupon = Coupon::create($data);
        $this->syncScope($coupon, $request);

        return redirect()->route('admin.coupons.index')->with('success', 'تم إنشاء كود الخصم بنجاح.');
    }

    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.form', [
            'coupon' => $coupon,
            'products' => Product::orderBy('name')->get(['id', 'name']),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'selectedProducts' => $coupon->products()->pluck('products.id')->all(),
            'selectedCategories' => $coupon->categories()->pluck('categories.id')->all(),
        ]);
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $data = $this->validateData($request, $coupon->id);
        $coupon->update($data);
        $this->syncScope($coupon, $request);

        return redirect()->route('admin.coupons.index')->with('success', 'تم تحديث كود الخصم بنجاح.');
    }

    public function toggle(Coupon $coupon): RedirectResponse
    {
        $coupon->update(['is_active' => ! $coupon->is_active]);
        return back()->with('success', 'تم تحديث حالة الكود.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();
        return back()->with('success', 'تم حذف كود الخصم.');
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        $rules = [
            'code' => 'required|string|max:50|unique:coupons,code' . ($ignoreId ? ',' . $ignoreId : ''),
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'min_order_total' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'scope' => 'required|in:all,products,categories',
            'description' => 'nullable|string|max:500',
        ];
        $data = $request->validate($rules);
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->boolean('is_active', true);
        return $data;
    }

    private function syncScope(Coupon $coupon, Request $request): void
    {
        if ($coupon->scope === 'products') {
            $coupon->products()->sync($request->input('product_ids', []));
            $coupon->categories()->sync([]);
        } elseif ($coupon->scope === 'categories') {
            $coupon->categories()->sync($request->input('category_ids', []));
            $coupon->products()->sync([]);
        } else {
            $coupon->products()->sync([]);
            $coupon->categories()->sync([]);
        }
    }
}
