<?php

namespace App\Services;

use Carbon\Carbon;

class MaintenanceScheduleService
{
    public const STATUS_MONITORING = 'Monitoring Only';
    public const STATUS_DUE = 'Maintenance Due';
    public const STATUS_OVERDUE = 'Overdue';

    public const INTERVAL_MONTHLY = 'Monthly';
    public const INTERVAL_QUARTERLY = 'Quarterly';
    public const INTERVAL_YEARLY = 'Yearly';

    /**
     * Number of grace period days before an asset becomes Overdue.
     */
    public const GRACE_PERIOD_DAYS = 7;

    /**
     * Calculate the next due date based on the last maintenance date and interval.
     */
    public static function calculateNextDueDate(Carbon $lastMaintenanceDate, int $intervalDays): Carbon
    {
        return $lastMaintenanceDate->copy()->addDays($intervalDays);
    }

    /**
     * Derive the status based on the next due date compared to a reference date (usually today).
     */
    public static function deriveStatus(Carbon $nextDueDate, ?Carbon $referenceDate = null): string
    {
        $today = $referenceDate ? $referenceDate->copy()->startOfDay() : Carbon::now()->startOfDay();
        $due = $nextDueDate->copy()->startOfDay();

        if ($today->lt($due)) {
            return self::STATUS_MONITORING;
        }

        $overdueDate = $due->copy()->addDays(self::GRACE_PERIOD_DAYS);

        if ($today->gte($overdueDate)) {
            return self::STATUS_OVERDUE;
        }

        return self::STATUS_DUE;
    }

    /**
     * Get default interval days for interval types.
     */
    public static function getDefaultDaysForInterval(string $intervalType): int
    {
        return match ($intervalType) {
            self::INTERVAL_MONTHLY => 30,
            self::INTERVAL_QUARTERLY => 90,
            self::INTERVAL_YEARLY => 365,
            default => 30,
        };
    }

    /**
     * Get all valid interval types.
     */
    public static function intervalTypes(): array
    {
        return [
            self::INTERVAL_MONTHLY,
            self::INTERVAL_QUARTERLY,
            self::INTERVAL_YEARLY,
        ];
    }

    /**
     * Get all valid statuses.
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_MONITORING,
            self::STATUS_DUE,
            self::STATUS_OVERDUE,
        ];
    }
}
