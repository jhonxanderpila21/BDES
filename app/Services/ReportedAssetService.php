<?php

namespace App\Services;

class ReportedAssetService
{
    // --- Status Constants ---
    public const STATUS_PENDING = 'Pending';
    public const STATUS_FOR_REVIEW = 'For Review';
    public const STATUS_ONGOING = 'Ongoing';
    public const STATUS_ESCALATED = 'Escalated';
    public const STATUS_DONE = 'Done';
    public const STATUS_CLOSED = 'Closed';

    // --- Reported Type Constants ---
    public const TYPE_DAMAGE = 'Damage';
    public const TYPE_MISSING = 'Missing';
    public const TYPE_MALFUNCTION = 'Malfunction';
    public const TYPE_MAINTENANCE = 'Maintenance';
    public const TYPE_REPLACEMENT = 'Replacement';
    public const TYPE_OTHER = 'Other';

    /**
     * Valid status transition map.
     * Each key maps to an array of statuses it can transition TO.
     */
    public const VALID_TRANSITIONS = [
        self::STATUS_PENDING     => [self::STATUS_FOR_REVIEW, self::STATUS_ONGOING],
        self::STATUS_FOR_REVIEW  => [self::STATUS_ONGOING],
        self::STATUS_ONGOING     => [self::STATUS_ESCALATED, self::STATUS_DONE],
        self::STATUS_ESCALATED   => [self::STATUS_ONGOING, self::STATUS_DONE],
        self::STATUS_DONE        => [self::STATUS_CLOSED],
        self::STATUS_CLOSED      => [], // Terminal state
    ];

    /**
     * Check if a status transition is valid.
     */
    public static function isValidTransition(string $from, string $to): bool
    {
        $allowed = self::VALID_TRANSITIONS[$from] ?? [];
        return in_array($to, $allowed, true);
    }

    /**
     * Get the list of statuses a given status can transition to.
     */
    public static function allowedNextStatuses(string $currentStatus): array
    {
        return self::VALID_TRANSITIONS[$currentStatus] ?? [];
    }

    /**
     * Get all valid reported types.
     */
    public static function reportedTypes(): array
    {
        return [
            self::TYPE_DAMAGE,
            self::TYPE_MISSING,
            self::TYPE_MALFUNCTION,
            self::TYPE_MAINTENANCE,
            self::TYPE_REPLACEMENT,
            self::TYPE_OTHER,
        ];
    }

    /**
     * Get all valid statuses.
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_FOR_REVIEW,
            self::STATUS_ONGOING,
            self::STATUS_ESCALATED,
            self::STATUS_DONE,
            self::STATUS_CLOSED,
        ];
    }

    /**
     * Statuses that indicate an "open" (active/unresolved) report.
     */
    public static function openStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_FOR_REVIEW,
            self::STATUS_ONGOING,
            self::STATUS_ESCALATED,
        ];
    }

    /**
     * Map a status to a badge variant for the UI.
     */
    public static function statusBadgeVariant(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDING     => 'warning',
            self::STATUS_FOR_REVIEW  => 'info',
            self::STATUS_ONGOING     => 'primary',
            self::STATUS_ESCALATED   => 'danger',
            self::STATUS_DONE        => 'success',
            self::STATUS_CLOSED      => 'secondary',
            default                  => 'secondary',
        };
    }

    /**
     * Map a reported type to a badge variant for the UI.
     */
    public static function typeBadgeVariant(string $type): string
    {
        return match ($type) {
            self::TYPE_DAMAGE       => 'danger',
            self::TYPE_MISSING      => 'warning',
            self::TYPE_MALFUNCTION  => 'danger',
            self::TYPE_MAINTENANCE  => 'info',
            self::TYPE_REPLACEMENT  => 'warning',
            self::TYPE_OTHER        => 'secondary',
            default                 => 'secondary',
        };
    }
}
