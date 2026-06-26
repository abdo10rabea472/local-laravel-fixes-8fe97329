<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
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

    /**
     * Return current stock for the requested product IDs.
     * Used by the checkout page to cap quantity controls.
     */
    public function stocks(Request $request): JsonResponse
    {
        $ids = collect(explode(',', (string) $request->query('ids', '')))
            ->map(fn ($v) => (int) trim($v))
            ->filter(fn ($v) => $v > 0)
            ->unique()
            ->take(100)
            ->values();

        if ($ids->isEmpty()) {
            return response()->json(['stocks' => (object) [], 'prices' => (object) []]);
        }

        $products = Product::whereIn('id', $ids)->get(['id', 'stock', 'price', 'sale_price']);
        $stocks = $products->pluck('stock', 'id');
        $prices = $products->mapWithKeys(fn ($p) => [$p->id => (float) ($p->sale_price ?? $p->price)]);

        return response()->json(['stocks' => $stocks, 'prices' => $prices]);
    }


    public function applyCoupon(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => 'required|string|max:50',
            'cart' => 'required|array',
            'cart.*.id' => 'required|integer',
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

        // Rebuild cart using DB-trusted prices (ignore client-sent prices).
        $serverCart = $this->buildServerCart($data['cart']);
        if (empty($serverCart)) {
            return response()->json(['ok' => false, 'message' => 'السلة غير صالحة.'], 200);
        }

        $userId = Auth::id();
        $result = $coupon->validateFor($serverCart, $userId, $data['email'] ?? null, $data['phone'] ?? null);

        AuditLog::record($result['ok'] ? 'checkout.coupon.applied' : 'checkout.coupon.rejected', [
            'code' => $coupon->code,
            'items' => count($serverCart),
            'discount' => $result['discount'] ?? 0,
            'reason' => $result['ok'] ? null : ($result['message'] ?? null),
        ], $userId ? 'user' : 'guest', $userId);

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
                    ->get(['id', 'name', 'stock', 'price', 'sale_price']);

                if ($products->count() !== count($requested)) {
                    throw new \RuntimeException('One or more products are no longer available.');
                }

                foreach ($products as $product) {
                    $need = $requested[$product->id];
                    if ((int) $product->stock < $need) {
                        throw new \RuntimeException("Insufficient stock for: {$product->name}.");
                    }
                }

                // Atomic decrement (avoids brittle DB::raw string concat).
                foreach ($products as $product) {
                    Product::where('id', $product->id)
                        ->where('stock', '>=', $requested[$product->id])
                        ->decrement('stock', (int) $requested[$product->id]);
                }

                // Coupon redemption — rebuild cart with DB-trusted prices, never client prices.
                if (! empty($data['code'])) {
                    $coupon = Coupon::where('code', strtoupper($data['code']))->first();
                    if ($coupon) {
                        $serverCart = $products->map(fn ($p) => [
                            'id' => (int) $p->id,
                            'price' => (float) ($p->sale_price ?? $p->price),
                            'quantity' => (int) $requested[$p->id],
                        ])->all();

                        $result = $coupon->validateFor($serverCart, Auth::id(), $data['email'], $data['phone'] ?? null);
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
            AuditLog::record('checkout.order.failed', [
                'items' => array_sum($requested),
                'distinct' => count($requested),
                'coupon' => ! empty($data['code']) ? strtoupper($data['code']) : null,
                'reason' => $e->getMessage(),
            ], Auth::id() ? 'user' : 'guest', Auth::id());
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }

        AuditLog::record('checkout.order.placed', [
            'items' => array_sum($requested),
            'distinct' => count($requested),
            'coupon' => ! empty($data['code']) ? strtoupper($data['code']) : null,
        ], Auth::id() ? 'user' : 'guest', Auth::id());

        return response()->json([
            'ok' => true,
            'redirect' => route('pages.payment-success'),
        ]);
    }

    /**
     * Rebuild the cart using prices read from the database, ignoring any
     * client-supplied price values. Returns rows shaped for Coupon::validateFor.
     */
    private function buildServerCart(array $clientCart): array
    {
        $quantities = [];
        foreach ($clientCart as $line) {
            $pid = (int) ($line['id'] ?? 0);
            $qty = (int) ($line['quantity'] ?? 0);
            if ($pid <= 0 || $qty <= 0) {
                continue;
            }
            $quantities[$pid] = ($quantities[$pid] ?? 0) + $qty;
        }

        if (empty($quantities)) {
            return [];
        }

        return Product::whereIn('id', array_keys($quantities))
            ->get(['id', 'price', 'sale_price'])
            ->map(fn ($p) => [
                'id' => (int) $p->id,
                'price' => (float) ($p->sale_price ?? $p->price),
                'quantity' => (int) $quantities[$p->id],
            ])
            ->all();
    }
}
