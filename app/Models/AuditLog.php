<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['action', 'actor_type', 'actor_id', 'ip', 'user_agent', 'context', 'created_at'];

    protected $casts = [
        'context' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Keys that must never be persisted to the audit log.
     * Anything that could leak credentials or PII beyond what we explicitly capture.
     */
    private const SENSITIVE_KEYS = [
        'password', 'password_confirmation', 'current_password', 'new_password',
        'token', 'api_key', 'secret', 'authorization', 'remember_token',
        'card', 'card_number', 'cvv', 'cvc', 'pin',
    ];

    /**
     * Persist an audit event. Sensitive keys are stripped from $context defensively.
     */
    public static function record(string $action, array $context = [], ?string $actorType = null, ?int $actorId = null): void
    {
        try {
            $request = request();
            self::create([
                'action'     => $action,
                'actor_type' => $actorType,
                'actor_id'   => $actorId,
                'ip'         => $request instanceof Request ? $request->ip() : null,
                'user_agent' => $request instanceof Request ? substr((string) $request->userAgent(), 0, 255) : null,
                'context'    => self::scrub($context),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Never let auditing break the request flow.
            Log::warning('AuditLog write failed', ['action' => $action, 'error' => $e->getMessage()]);
        }
    }

    private static function scrub(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array(strtolower((string) $key), self::SENSITIVE_KEYS, true)) {
                unset($data[$key]);
                continue;
            }
            if (is_array($value)) {
                $data[$key] = self::scrub($value);
            }
        }
        return $data;
    }
}
