<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssetLifecycleService
{
    /**
     * Valid lifecycle statuses.
     */
    public const STATUS_AVAILABLE         = 'available';
    public const STATUS_DEPLOYED          = 'deployed';
    public const STATUS_UNDER_MAINTENANCE = 'under_maintenance';
    public const STATUS_OUT_OF_SERVICE    = 'out_of_service';
    public const STATUS_LOST              = 'lost';
    public const STATUS_RETIRED           = 'retired';

    /**
     * All recognized statuses.
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_AVAILABLE         => 'Available & Ready',
            self::STATUS_DEPLOYED          => 'Deployed in Field',
            self::STATUS_UNDER_MAINTENANCE => 'Under Maintenance',
            self::STATUS_OUT_OF_SERVICE    => 'Out of Service',
            self::STATUS_LOST              => 'Lost / Missing',
            self::STATUS_RETIRED           => 'Retired / Decommissioned',
        ];
    }

    /**
     * Allowed status transitions matrix.
     * key = fromStatus, value = array of allowed toStatuses.
     */
    protected static array $allowedTransitions = [
        self::STATUS_AVAILABLE => [
            self::STATUS_DEPLOYED,
            self::STATUS_UNDER_MAINTENANCE,
            self::STATUS_OUT_OF_SERVICE,
            self::STATUS_LOST,
            self::STATUS_RETIRED,
        ],
        self::STATUS_DEPLOYED => [
            self::STATUS_AVAILABLE,         // Normal return
            self::STATUS_UNDER_MAINTENANCE, // Returned with damage requiring repair
            self::STATUS_OUT_OF_SERVICE,    // Returned defective
            self::STATUS_LOST,              // Lost during field operation
        ],
        self::STATUS_UNDER_MAINTENANCE => [
            self::STATUS_AVAILABLE,         // Repair completed successfully
            self::STATUS_OUT_OF_SERVICE,    // Deemed unrepairable
            self::STATUS_RETIRED,           // Beyond economic repair -> retired
            self::STATUS_LOST,
        ],
        self::STATUS_OUT_OF_SERVICE => [
            self::STATUS_UNDER_MAINTENANCE, // Sent for servicing/overhaul
            self::STATUS_AVAILABLE,         // Recertified/fixed
            self::STATUS_RETIRED,           // Formal decommissioning
            self::STATUS_LOST,
        ],
        self::STATUS_LOST => [
            self::STATUS_AVAILABLE,         // Found/recovered in good order
            self::STATUS_OUT_OF_SERVICE,    // Recovered damaged
            self::STATUS_RETIRED,           // Written off after investigation
        ],
        self::STATUS_RETIRED => [
            // Terminal state; exceptional reactivation requires explicit override
            self::STATUS_OUT_OF_SERVICE,
        ],
    ];

    /**
     * Check if a status transition is permitted.
     */
    public static function canTransition(Asset $asset, string $newStatus): bool
    {
        if ($asset->status === $newStatus) {
            return true;
        }

        $allowed = static::$allowedTransitions[$asset->status] ?? [];
        return in_array($newStatus, $allowed, true);
    }

    /**
     * Perform a validated status transition with audit logging.
     *
     * @throws ValidationException
     */
    public static function transition(Asset $asset, string $newStatus, ?string $reason = null, ?User $user = null): Asset
    {
        if (!static::canTransition($asset, $newStatus)) {
            $fromLabel = static::statuses()[$asset->status] ?? $asset->status;
            $toLabel   = static::statuses()[$newStatus] ?? $newStatus;
            throw ValidationException::withMessages([
                'status' => "Invalid asset lifecycle transition: Cannot change status from [{$fromLabel}] to [{$toLabel}].",
            ]);
        }

        if ($asset->status === $newStatus) {
            return $asset;
        }

        return DB::transaction(function () use ($asset, $newStatus, $reason, $user) {
            $oldStatus = $asset->status;
            $oldValues = ['status' => $oldStatus];

            $asset->status = $newStatus;

            if ($newStatus === self::STATUS_RETIRED) {
                $asset->retirement_date   = now()->toDateString();
                $asset->retirement_reason = $reason ?? 'Decommissioned by authorized personnel.';
                $asset->retired_by        = $user?->id ?? auth()->id();
            }

            $asset->save();

            $newValues = [
                'status'            => $newStatus,
                'retirement_date'   => $asset->retirement_date,
                'retirement_reason' => $asset->retirement_reason,
            ];

            $desc = "Lifecycle transition: changed from [{$oldStatus}] to [{$newStatus}]." . ($reason ? " Reason: {$reason}" : '');
            AuditTrailService::log('status_changed', $asset, $desc, $oldValues, $newValues, $user?->id);

            return $asset;
        });
    }

    /**
     * Gracefully retire an asset.
     */
    public static function retire(Asset $asset, string $reason, ?User $user = null): Asset
    {
        if ($asset->status === self::STATUS_DEPLOYED) {
            throw ValidationException::withMessages([
                'status' => 'Cannot retire an asset that is currently deployed in the field. Please complete its return first.',
            ]);
        }

        return static::transition($asset, self::STATUS_RETIRED, $reason, $user);
    }

    /**
     * Flag an asset as lost/missing.
     */
    public static function markLost(Asset $asset, string $reason, ?User $user = null): Asset
    {
        return static::transition($asset, self::STATUS_LOST, $reason, $user);
    }

    /**
     * Handle safe deletion/retirement for assets with history.
     */
    public static function safeDeleteOrRetire(Asset $asset, string $reason = 'Asset deleted via management interface.', ?User $user = null): bool
    {
        $hasHistory = $asset->deployments()->exists()
            || $asset->maintenance()->exists()
            || $asset->inspections()->exists()
            || $asset->returns()->exists();

        if ($hasHistory) {
            static::retire($asset, "Retired in lieu of deletion due to linked historical operational records: {$reason}", $user);
            return false; // Not hard-deleted; preserved as retired
        }

        DB::transaction(function () use ($asset, $user) {
            AuditTrailService::log('deleted', $asset, "Asset {$asset->code} permanently removed from system (no operational history).", $asset->toArray(), null, $user?->id);
            $asset->attachments()->delete();
            $asset->accessories()->delete();
            $asset->delete();
        });

        return true;
    }
}

