<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ShippingCarrier;
use App\Services\ShippingTrackingService;
use Illuminate\Http\Request;

class ShippingWebhookController extends Controller
{
    public function __construct(private ShippingTrackingService $tracker) {}

    /**
     * Public webhook receiver. Carriers POST status updates here.
     * URL: /api/shipping/{carrierCode}/webhook?secret=...
     * Body: { tracking_number, status, events:[...] }
     */
    public function __invoke(Request $request, string $code)
    {
        $carrier = ShippingCarrier::where('code', $code)->where('is_active', true)->first();
        abort_unless($carrier, 404);

        if ($carrier->webhook_secret) {
            $provided = $request->header('X-Webhook-Secret') ?? $request->query('secret');
            if (!hash_equals($carrier->webhook_secret, (string) $provided)) {
                return response()->json(['ok' => false, 'message' => 'invalid signature'], 401);
            }
        }

        $data = $request->validate([
            'tracking_number' => ['required','string','max:120'],
            'status' => ['nullable','string','max:60'],
            'events' => ['nullable','array'],
        ]);

        $order = Order::where('shipping_carrier_id', $carrier->id)
            ->where('tracking_number', $data['tracking_number'])
            ->first();

        abort_unless($order, 404, 'order not found');

        $result = $this->tracker->applyPayload($order, $data);
        return response()->json($result);
    }
}
