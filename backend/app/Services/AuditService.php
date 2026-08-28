<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Write an audit log entry. Never log secrets.
     */
    public static function log(
        string $action,
        array $options = []
    ): AuditLog {
        return AuditLog::create([
            'organization_id' => $options['organization_id'] ?? null,
            'actor_type' => $options['actor_type'] ?? 'user',
            'actor_id' => $options['actor_id'] ?? null,
            'action' => $action,
            'entity_type' => $options['entity_type'] ?? null,
            'entity_id' => $options['entity_id'] ?? null,
            'metadata' => self::sanitize($options['metadata'] ?? null),
            'ip_address' => $options['ip'] ?? Request::ip(),
            'result' => $options['result'] ?? 'success',
        ]);
    }

    protected static function sanitize(?array $metadata): ?array
    {
        if (!$metadata) {
            return null;
        }

        $blocked = ['password', 'token', 'secret', 'api_key', 'card', 'cvv', 'authorization'];
        foreach ($blocked as $key) {
            unset($metadata[$key]);
            foreach (array_keys($metadata) as $k) {
                if (stripos((string) $k, $key) !== false) {
                    unset($metadata[$k]);
                }
            }
        }

        return $metadata;
    }
}
