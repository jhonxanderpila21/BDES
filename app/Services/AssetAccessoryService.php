<?php

namespace App\Services;

class AssetAccessoryService
{
    public const STATUS_AVAILABLE = 'Available';
    public const STATUS_NOT_AVAILABLE = 'Not Available';

    /**
     * Derive the status based on the available quantity of the accessory.
     *
     * @param int $quantity
     * @return string
     */
    public static function deriveStatus(int $quantity): string
    {
        return $quantity > 0 ? self::STATUS_AVAILABLE : self::STATUS_NOT_AVAILABLE;
    }

    /**
     * Get all valid statuses.
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_AVAILABLE,
            self::STATUS_NOT_AVAILABLE,
        ];
    }
}
