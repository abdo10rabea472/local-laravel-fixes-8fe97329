<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ShippingCarrier;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Generic tracking adapter for shipping carriers.
 *
 * Carriers register their API endpoint + key in the admin panel. We send a
 * GET to {endpoint} with {tracking} substitution and standard headers, then
 * normalize the response into a common shape. The carrier API_KEY is sent
 * as Bearer + X-API-Key for broad compatibility (DHL, Bosta, Aramex, etc.).
 *
 * Map a payload like:
 *   { status: "delivered"|"in_transit"|...,
 *     events: [{ at: iso8601, status: "...", description: "...", location: "..." }] }
 */
class ShippingTrackingService
{
    /** Carrier-status → our order status. */
    private const STATUS_MAP = [
        'delivered'    => 'delivered',
        'in_transit'   => 'shipped',
        'shipped'      => 'shipped',
        'out_for_delivery' => 'shipped',
        'picked_up'    => 'shipped',
        'returned'     => 'refunded',
        'cancelled'    => 'cancelled',
    ];

    /**
     * Fetch tracking from carrier API and update the order.
     *
     * @return array{ok:bool,message:string,status?:string,events?:array}
     */
    public function refresh(Order $order): array
    {
        $order->loadMissing('carrier');
        $carrier = $order->carrier;

        if (!$carrier || !$order->tracking_number) {
            return ['ok' => false, 'message' => 'لا يوجد رقم تتبع أو شركة شحن مرتبطة.'];
        }

        if (!$carrier->api_endpoint) {
            // Fallback: nothing to call, but still flag last_sync so UI knows.
            $order->update(['tracking_last_sync_at' => now()]);
            return ['ok' => false, 'message' => 'شركة الشحن لا تدعم التتبع التلقائي عبر API.'];
        }

        $url = str_replace('{tracking}', urlencode($order->tracking_number), $carrier->api_endpoint);

        try {
            $resp = Http::timeout(10)
                ->acceptJson()
                ->withHeaders(array_filter([
                    'Authorization' => $carrier->api_key ? 'Bearer ' . $carrier->api_key : null,
                    'X-API-Key'     => $carrier->api_key,
                ]))
                ->get($url);
        } catch (Throwable $e) {
            Log::warning('Tracking fetch failed', ['order' => $order->id, 'err' => $e->getMessage()]);
            return ['ok' => false, 'message' => 'تعذر الاتصال بشركة الشحن.'];
        }

        if (!$resp->successful()) {
            return ['ok' => false, 'message' => "خطأ من API: HTTP {$resp->status()}"];
        }

        return $this->applyPayload($order, $resp->json() ?? []);
    }

    /**
     * Apply a normalized tracking payload to the order. Used by both the
     * pull API and the public webhook endpoint.
     */
    public function applyPayload(Order $order, array $payload): array
    {
        $rawStatus = strtolower((string) ($payload['status'] ?? ''));
        $events = $payload['events'] ?? [];

        $order->tracking_status = $rawStatus ?: $order->tracking_status;
        $order->tracking_last_sync_at = now();
        if (is_array($events)) {
            $order->tracking_history = array_values($events);
        }

        // Auto-promote order status only forward in the lifecycle.
        $mapped = self::STATUS_MAP[$rawStatus] ?? null;
        if ($mapped) {
            $current = $order->status;
            $forwardOrder = ['pending'=>1,'paid'=>2,'shipped'=>3,'delivered'=>4,'cancelled'=>5,'refunded'=>5];
            if (($forwardOrder[$mapped] ?? 0) > ($forwardOrder[$current] ?? 0)) {
                $order->status = $mapped;
                if ($mapped === 'shipped' && !$order->shipped_at)   $order->shipped_at = now();
                if ($mapped === 'delivered' && !$order->delivered_at) $order->delivered_at = now();
            }
        }

        $order->save();

        return [
            'ok' => true,
            'message' => 'تم تحديث بيانات التتبع.',
            'status' => $order->tracking_status,
            'order_status' => $order->status,
            'events' => $order->tracking_history,
        ];
    }
}
