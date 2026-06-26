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

        $discountedProductIds = ProductDiscount::active()->pluck('product_id')->map(fn ($id) => (int) $id)->values();

        // Pre-fill from the authenticated user's profile.
        $user = Auth::user();
        $profile = [
            'customer_name'     => $user->name,
            'email'             => $user->email,
            'phone'             => $user->phone,
            'shipping_country'  => $user->shipping_country,
            'shipping_region'   => $user->shipping_region,
            'shipping_city'     => $user->shipping_city,
            'shipping_address'  => $user->shipping_address,
            'shipping_postcode' => $user->shipping_postcode,
        ];

        // Only enabled gateways; filter by user's country if set.
        $paymentGateways = \App\Models\PaymentGateway::activeFor($user->shipping_country);

        return view('checkout.index', compact(
            'seo', 'shippingCountries', 'discountedProductIds', 'profile', 'paymentGateways'
        ));
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
            'customer_name' => 'nullable|string|max:150',
            'shipping_country' => 'nullable|string|max:100',
            'shipping_region' => 'nullable|string|max:100',
            'shipping_address' => 'nullable|string|max:255',
            'shipping_city' => 'nullable|string|max:100',
            'shipping_postcode' => 'nullable|string|max:20',
            'shipping_cost' => 'nullable|numeric|min:0',
            'shipping_carrier_id' => 'nullable|integer|exists:shipping_carriers,id',
            'notes' => 'nullable|string|max:1000',
            'payment_gateway' => 'required|string|exists:payment_gateways,code',
        ]);

        // Persist shipping defaults onto the user's profile so subsequent
        // orders pre-fill automatically (no need to re-enter every time).
        if ($user = Auth::user()) {
            $user->forceFill(array_filter([
                'name'              => $data['customer_name'] ?? $user->name,
                'phone'             => $data['phone'] ?? $user->phone,
                'shipping_country'  => $data['shipping_country'] ?? null,
                'shipping_region'   => $data['shipping_region'] ?? null,
                'shipping_city'     => $data['shipping_city'] ?? null,
                'shipping_address'  => $data['shipping_address'] ?? null,
                'shipping_postcode' => $data['shipping_postcode'] ?? null,
            ], fn ($v) => $v !== null && $v !== ''))->save();
        }


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

        $createdOrder = null;

        try {
            $createdOrder = DB::transaction(function () use ($requested, $data) {
                $products = Product::whereIn('id', array_keys($requested))
                    ->lockForUpdate()
                    ->get(['id', 'name', 'stock', 'price', 'sale_price']);

                if ($products->count() !== count($requested)) {
                    throw new \RuntimeException('One or more products are no longer available.');
                }

                foreach ($products as $product) {
                    if ((int) $product->stock < $requested[$product->id]) {
                        throw new \RuntimeException("Insufficient stock for: {$product->name}.");
                    }
                }

                $stockService = app(\App\Services\StockService::class);
                foreach ($products as $product) {
                    $stockService->apply(
                        $product,
                        -(int) $requested[$product->id],
                        'order',
                        'Order',
                        null,
                        'طلب جديد'
                    );
                }

                // Build server-trusted items + totals
                $items = [];
                $subtotal = 0.0;
                foreach ($products as $p) {
                    $price = (float) ($p->sale_price ?? $p->price);
                    $qty = (int) $requested[$p->id];
                    $line = round($price * $qty, 2);
                    $subtotal += $line;
                    $items[] = [
                        'product_id' => $p->id,
                        'product_name' => $p->name,
                        'unit_price' => $price,
                        'quantity' => $qty,
                        'line_total' => $line,
                    ];
                }

                $discount = 0.0;
                $couponCode = null;
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
                        $discount = (float) ($result['discount'] ?? 0);
                        $couponCode = $coupon->code;

                        CouponRedemption::create([
                            'coupon_id' => $coupon->id,
                            'user_id' => Auth::id(),
                            'email' => strtolower(trim($data['email'])),
                            'phone' => $data['phone'] ?? null,
                            'order_total' => $result['subtotal'] ?? 0,
                            'discount_amount' => $discount,
                            'used_at' => now(),
                        ]);
                        $coupon->increment('used_count');
                    }
                }

                $shipping = (float) ($data['shipping_cost'] ?? 0);

                // Include payment-gateway extra fees (e.g. COD surcharge).
                $gateway = \App\Models\PaymentGateway::where('code', $data['payment_gateway'])->first();
                $payFees = $gateway ? (float) $gateway->extra_fees : 0.0;

                $total = max(0, round($subtotal - $discount + $shipping + $payFees, 2));

                $order = \App\Models\Order::create([
                    'order_number' => \App\Models\Order::generateNumber(),
                    'user_id' => Auth::id(),
                    'customer_name' => $data['customer_name'] ?? null,
                    'email' => strtolower(trim($data['email'])),
                    'phone' => $data['phone'] ?? null,
                    'shipping_country' => $data['shipping_country'] ?? null,
                    'shipping_region' => $data['shipping_region'] ?? null,
                    'shipping_address' => $data['shipping_address'] ?? null,
                    'shipping_city' => $data['shipping_city'] ?? null,
                    'shipping_postcode' => $data['shipping_postcode'] ?? null,
                    'shipping_carrier_id' => $data['shipping_carrier_id'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discount,
                    'coupon_code' => $couponCode,
                    'shipping_cost' => $shipping,
                    'payment_fees' => $payFees,
                    'payment_gateway' => $data['payment_gateway'],
                    'total' => $total,
                    'currency' => 'EGP',
                    'status' => 'pending',
                    'payment_status' => 'unpaid',
                    'shipping_status' => !empty($data['shipping_carrier_id']) ? 'queued' : 'no_carrier',
                ]);


                foreach ($items as $it) {
                    $order->items()->create($it);
                }

                \App\Models\OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'from_status' => null,
                    'to_status' => 'pending',
                    'note' => 'Order placed',
                    'changed_by_type' => Auth::id() ? 'user' : 'guest',
                    'changed_by_id' => Auth::id(),
                ]);

                // Clear cart
                \App\Models\CartItem::query()
                    ->when(Auth::id(), fn ($q) => $q->where('user_id', Auth::id()))
                    ->when(! Auth::id(), fn ($q) => $q->where('session_id', session()->getId()))
                    ->delete();

                return $order;
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

        cache()->forget('admin.orders.stats');

        AuditLog::record('checkout.order.placed', [
            'order_id' => $createdOrder->id,
            'order_number' => $createdOrder->order_number,
            'items' => array_sum($requested),
            'total' => (float) $createdOrder->total,
            'coupon' => $createdOrder->coupon_code,
        ], Auth::id() ? 'user' : 'guest', Auth::id());

        return response()->json([
            'ok' => true,
            'order_id' => $createdOrder->id,
            'order_number' => $createdOrder->order_number,
            'redirect' => route('checkout.pay', ['order' => $createdOrder->id]) . '?gateway=' . urlencode($data['payment_gateway']),
            'pay_url'  => route('checkout.pay', ['order' => $createdOrder->id]),
            'gateway'  => $data['payment_gateway'],
        ]);
    }

    /**
     * Calculate shipping rate via Aramex API for the user's current address & cart.
     * POST /checkout/aramex-rate
     */
    public function aramexRate(Request $request, \App\Services\AramexService $aramex): JsonResponse
    {
        $data = $request->validate([
            'country_code' => 'required|string|size:2',
            'city' => 'required|string|max:100',
            'line1' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'cart' => 'required|array|min:1',
            'cart.*.id' => 'required|integer',
            'cart.*.quantity' => 'required|integer|min:1',
        ]);

        if (!$aramex->isConfigured()) {
            return response()->json(['ok' => false, 'message' => 'Aramex غير مهيّأ بعد. أضف بيانات الاعتماد في config/aramex.php'], 422);
        }

        $totalQty = array_sum(array_column($data['cart'], 'quantity'));
        $weight = max(0.5, $totalQty * 0.5); // افتراض: 0.5 كجم لكل قطعة

        $origin = config('aramex.shipper_address', [
            'line1' => 'Origin Address',
            'city'  => 'Cairo',
            'country_code' => 'EG',
        ]);
        $destination = [
            'line1' => $data['line1'],
            'city'  => $data['city'],
            'country_code' => strtoupper($data['country_code']),
            'postal_code'  => $data['postal_code'] ?? '',
        ];

        $res = $aramex->calculateRate($origin, $destination, [
            'weight' => $weight,
            'number_of_pieces' => $totalQty,
        ], 'EGP');

        return response()->json($res, $res['ok'] ? 200 : 422);
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
