<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductDiscount;
use App\Models\CouponRedemption;
use App\Models\ShippingCountry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(): View
    {
        $seo = [
            'seo_title' => 'Checkout | UNI-LAB MARKET',
            'seo_description' => 'Complete your order securely.',
            'canonical_url' => route('checkout'),
        ];

        $shippingCountries = ShippingCountry::active()
            ->with(['regions' => fn ($q) => $q->where('status', true)])
            ->orderBy('position')
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'cost' => $c->cost !== null ? (float) $c->cost : null,
                'regions' => $c->regions->map(fn ($r) => [
                    'id' => $r->id,
                    'name' => $r->name,
                    'cost' => $r->cost !== null ? (float) $r->cost : null,
                ])->values(),
            ])->values();

        // Currently discounted product IDs (so the UI can warn before user tries a coupon)
        $discountedProductIds = ProductDiscount::active()->pluck('product_id')->map(fn ($id) => (int) $id)->values();

        return view('checkout.index', compact('seo', 'shippingCountries', 'discountedProductIds'));
    }

    public function applyCoupon(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => 'required|string|max:50',
            'cart' => 'required|array',
            'cart.*.id' => 'required|integer',
            'cart.*.price' => 'required|numeric',
            'cart.*.quantity' => 'required|integer|min:1',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ]);

        $coupon = Coupon::where('code', strtoupper($data['code']))->first();
        if (! $coupon) {
            return response()->json(['ok' => false, 'message' => 'كود الخصم غير موجود.'], 200);
        }

        // Prevent stacking: if any cart item already has an active product discount, block the coupon.
        $cartIds = collect($data['cart'])->pluck('id')->map(fn ($i) => (int) $i)->all();
        $discountedInCart = ProductDiscount::active()->whereIn('product_id', $cartIds)->exists();
        if ($discountedInCart) {
            return response()->json([
                'ok' => false,
                'message' => 'لديك بالفعل منتجات عليها خصم في السلة، لا يمكن استخدام كود الخصم مع منتجات مخفّضة.',
            ], 200);
        }

        $userId = Auth::id();
        $result = $coupon->validateFor($data['cart'], $userId, $data['email'] ?? null, $data['phone'] ?? null);

        return response()->json(array_merge($result, [
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => (float) $coupon->value,
        ]));
    }

    public function placeOrder(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'code' => 'nullable|string|max:50',
            'cart' => 'required|array|min:1',
            'cart.*.id' => 'required|integer',
            'cart.*.price' => 'required|numeric',
            'cart.*.quantity' => 'required|integer|min:1',
            'email' => 'required|email',
            'phone' => 'nullable|string',
        ]);

        // Aggregate quantities per product (defends against duplicate lines in cart)
        $requested = [];
        foreach ($data['cart'] as $line) {
            $pid = (int) $line['id'];
            $qty = (int) $line['quantity'];
            if ($pid <= 0 || $qty <= 0) {
                continue;
            }
            $requested[$pid] = ($requested[$pid] ?? 0) + $qty;
        }

        if (empty($requested)) {
            return response()->json(['ok' => false, 'message' => 'Cart is empty.'], 422);
        }

        try {
            DB::transaction(function () use ($requested, $data) {
                // Lock product rows to prevent race conditions / overselling
                $products = Product::whereIn('id', array_keys($requested))
                    ->lockForUpdate()
                    ->get(['id', 'name', 'stock']);

                if ($products->count() !== count($requested)) {
                    throw new \RuntimeException('One or more products are no longer available.');
                }

                foreach ($products as $product) {
                    $need = $requested[$product->id];
                    if ((int) $product->stock < $need) {
                        throw new \RuntimeException("Insufficient stock for: {$product->name}.");
                    }
                }

                // Atomic decrement
                foreach ($products as $product) {
                    Product::where('id', $product->id)
                        ->where('stock', '>=', $requested[$product->id])
                        ->update(['stock' => DB::raw('stock - ' . (int) $requested[$product->id])]);
                }

                // Coupon redemption (kept inside same transaction for consistency)
                if (! empty($data['code'])) {
                    $coupon = Coupon::where('code', strtoupper($data['code']))->first();
                    if ($coupon) {
                        $result = $coupon->validateFor($data['cart'], Auth::id(), $data['email'], $data['phone'] ?? null);
                        if (! $result['ok']) {
                            throw new \RuntimeException($result['message']);
                        }
                        CouponRedemption::create([
                            'coupon_id' => $coupon->id,
                            'user_id' => Auth::id(),
                            'email' => strtolower(trim($data['email'])),
                            'phone' => $data['phone'] ?? null,
                            'order_total' => $result['subtotal'] ?? 0,
                            'discount_amount' => $result['discount'] ?? 0,
                            'used_at' => now(),
                        ]);
                        $coupon->increment('used_count');
                    }
                }
            });
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }


        return response()->json([
            'ok' => true,
            'redirect' => route('pages.payment-success'),
        ]);
    }
}
