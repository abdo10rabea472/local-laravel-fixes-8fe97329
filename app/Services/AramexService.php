<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Thin wrapper around octw/aramex SDK.
 * Methods return ['ok' => bool, 'message' => string, 'data' => mixed]
 * so callers don't need to know about the SDK's shape (or whether it's installed).
 */
class AramexService
{
    public function isInstalled(): bool
    {
        return class_exists(\Octw\Aramex\Aramex::class);
    }

    public function isConfigured(): bool
    {
        if (!$this->isInstalled()) return false;
        // The SDK reads from config/aramex.php after vendor:publish.
        $cfg = config('aramex');
        return is_array($cfg) && !empty($cfg['ClientInfo']['UserName'] ?? null);
    }

    /** Shipping rate for checkout. */
    public function calculateRate(array $origin, array $destination, array $shipment, string $currency = 'EGP'): array
    {
        if (!$this->isConfigured()) {
            return ['ok' => false, 'message' => 'Aramex غير مهيّأ. أكمل التثبيت أولاً.'];
        }
        try {
            $res = \Octw\Aramex\Aramex::calculateRate($origin, $destination, $shipment, $currency);
            if (!empty($res->error)) {
                return ['ok' => false, 'message' => is_array($res->errors ?? null) ? json_encode($res->errors) : (string)($res->errors ?? 'Aramex error')];
            }
            $amount = (float) ($res->TotalAmount->Value ?? 0);
            return ['ok' => true, 'message' => 'OK', 'data' => ['amount' => $amount, 'currency' => $res->TotalAmount->CurrencyCode ?? $currency, 'raw' => $res]];
        } catch (Throwable $e) {
            Log::warning('Aramex calculateRate failed', ['err' => $e->getMessage()]);
            return ['ok' => false, 'message' => 'تعذر الاتصال بـ Aramex: ' . $e->getMessage()];
        }
    }

    /** Schedule a pickup (used for returns). */
    public function createPickup(array $params): array
    {
        if (!$this->isConfigured()) {
            return ['ok' => false, 'message' => 'Aramex غير مهيّأ.'];
        }
        try {
            $res = \Octw\Aramex\Aramex::createPickup($params);
            if (!empty($res->error)) {
                return ['ok' => false, 'message' => is_array($res->errors ?? null) ? json_encode($res->errors) : (string)($res->errors ?? 'Aramex error')];
            }
            return ['ok' => true, 'message' => 'تم جدولة الاستلام', 'data' => [
                'pickup_guid' => $res->pickupGUID ?? null,
                'pickup_id'   => $res->pickupID ?? null,
                'raw' => $res,
            ]];
        } catch (Throwable $e) {
            Log::warning('Aramex createPickup failed', ['err' => $e->getMessage()]);
            return ['ok' => false, 'message' => 'تعذر إنشاء الاستلام: ' . $e->getMessage()];
        }
    }

    /** Create shipment for an outgoing order. */
    public function createShipment(array $params): array
    {
        if (!$this->isConfigured()) {
            return ['ok' => false, 'message' => 'Aramex غير مهيّأ.'];
        }
        try {
            $res = \Octw\Aramex\Aramex::createShipment($params);
            if (!empty($res->error) || !empty($res->HasErrors)) {
                return ['ok' => false, 'message' => json_encode($res->errors ?? $res->Notifications ?? [])];
            }
            $shipment = $res->Shipments->ProcessedShipment ?? null;
            return ['ok' => true, 'message' => 'تم إنشاء الشحنة', 'data' => [
                'shipment_id' => $shipment->ID ?? null,
                'label_url'   => $shipment->ShipmentLabel->LabelURL ?? null,
                'raw' => $res,
            ]];
        } catch (Throwable $e) {
            Log::warning('Aramex createShipment failed', ['err' => $e->getMessage()]);
            return ['ok' => false, 'message' => 'تعذر إنشاء الشحنة: ' . $e->getMessage()];
        }
    }

    public function trackShipments(array $ids): array
    {
        if (!$this->isConfigured()) {
            return ['ok' => false, 'message' => 'Aramex غير مهيّأ.'];
        }
        try {
            $res = \Octw\Aramex\Aramex::trackShipments($ids);
            if (!empty($res->error)) {
                return ['ok' => false, 'message' => 'tracking error'];
            }
            return ['ok' => true, 'message' => 'OK', 'data' => $res];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }
}
