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

        // A webhook secret is mandatory. Carriers without a configured secret
        // cannot receive webhooks — otherwise anyone could spoof delivery
        // updates for any tracked order.
        if (empty($carrier->webhook_secret)) {
            return response()->json(['ok' => false, 'message' => 'webhook not configured'], 501);
        }
        $provided = $request->header('X-Webhook-Secret') ?? $request->query('secret');
        if (!is_string($provided) || !hash_equals($carrier->webhook_secret, $provided)) {
            return response()->json(['ok' => false, 'message' => 'invalid signature'], 401);
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
