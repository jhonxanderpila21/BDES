<?php

namespace App\Services;

class AssetNeedStatusService
{
    /**
     * Valid status values.
     */
    public const STATUS_MONITORING = 'Monitoring Only';
    public const STATUS_REQUIRES_MAINTENANCE = 'Requires Maintenance';
    public const STATUS_UNDER_MAINTENANCE = 'Under Maintenance';

    /**
     * Derive the asset need status from level values.
     *
     * Business rules:
     *  - current_level == 0                             → Under Maintenance
     *  - current_level < minimum_level (and > 0)        → Requires Maintenance
     *  - current_level >= minimum_level                 → Monitoring Only
     */
    public static function deriveStatus(
        float $currentLevel,
        float $minimumLevel,
        float $maximumCapacity
    ): string {
        if ($currentLevel == 0) {
            return self::STATUS_UNDER_MAINTENANCE;
        }

        if ($currentLevel < $minimumLevel) {
            return self::STATUS_REQUIRES_MAINTENANCE;
        }

        return self::STATUS_MONITORING;
    }

    /**
     * Get all valid status values.
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_MONITORING,
            self::STATUS_REQUIRES_MAINTENANCE,
            self::STATUS_UNDER_MAINTENANCE,
        ];
    }

    /**
     * Get all valid unit values.
     */
    public static function units(): array
    {
        return ['Liters', 'Units'];
    }
}
