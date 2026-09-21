<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditTrailService
{
    /**
     * Record an audit trail log entry.
     *
     * @param string $event
     * @param Model $auditable
     * @param string|null $description
     * @param array|null $oldValues
     * @param array|null $newValues
     * @param int|null $userId
     * @return AuditLog
     */
    public static function log(
        string $event,
        Model $auditable,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null
    ): AuditLog {
        return AuditLog::create([
            'user_id'        => $userId ?? Auth::id(),
            'auditable_type' => get_class($auditable),
            'auditable_id'   => $auditable->getKey(),
            'event'          => $event,
            'description'    => $description ?? static::buildDefaultDescription($event, $auditable),
            'old_values'     => $oldValues,
            'new_values'     => $newValues,
            'ip_address'     => Request::ip(),
            'user_agent'     => substr(Request::userAgent() ?? '', 0, 255),
            'created_at'     => now(),
        ]);
    }

    /**
     * Build an intelligent default description for the event.
     */
    protected static function buildDefaultDescription(string $event, Model $auditable): string
    {
        $entityName = class_basename($auditable);
        $identifier = $auditable->code ?? $auditable->name ?? "#{$auditable->getKey()}";

        return match ($event) {
            'created'               => "New {$entityName} {$identifier} registered in system.",
            'updated'               => "{$entityName} {$identifier} details updated.",
            'status_changed'        => "{$entityName} {$identifier} status changed to {$auditable->status}.",
            'deployed'              => "Asset {$identifier} deployed to destination.",
            'returned'              => "Asset {$identifier} returned to inventory.",
            'maintenance_started'   => "Work order opened for asset {$identifier}.",
            'maintenance_completed' => "Maintenance completed for asset {$identifier}.",
            'inspected'             => "Inspection recorded for asset {$identifier}.",
            'retired'               => "Asset {$identifier} decommissioned and retired.",
            'lost'                  => "Asset {$identifier} flagged as lost/missing.",
            'deleted'               => "{$entityName} {$identifier} archived or removed.",
            default                 => ucfirst(str_replace('_', ' ', $event)) . " on {$entityName} {$identifier}.",
        };
    }
}

