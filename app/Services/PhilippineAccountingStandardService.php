<?php

namespace App\Services;

use App\Models\Asset;
use Carbon\Carbon;

class PhilippineAccountingStandardService
{
    /**
     * Statutory capitalization threshold for tangible items under COA Circular No. 2022-004
     * (Amending COA Circular No. 2005-002 and aligned with GAM for LGUs Volume I).
     * Items below this threshold are classified as Semi-Expendable Property and are non-depreciable.
     */
    public const CAPITALIZATION_THRESHOLD = 50000.00;

    /**
     * Standard residual / salvage rate (10%) mandated by COA Circular No. 2003-007
     * and Government Accounting Manual (GAM) for Local Government Units.
     */
    public const DEFAULT_RESIDUAL_RATE = 0.10;

    /**
     * Default useful life (in years) by asset category / type under COA GAM for LGUs.
     */
    public const USEFUL_LIFE_SCHEDULE = [
        'Transportation'         => 7, // Emergency Ambulances, Rescue Patrol Vehicles, Disaster Response Pickups
        'Emergency Equipment'    => 7, // Heavy Generator Sets, Flood Pumping Systems, Water Filtration Units
        'Rescue Equipment'       => 5, // Rescue Boats, Chainsaws, Hydraulic Cutters, Extrication Tools
        'Communication Devices'  => 5, // VHF/UHF Two-Way Radios, Repeaters, Satellite Base Stations
        'Medical Supplies'       => 5, // Automated External Defibrillators (AED), Emergency Monitors
        'Information Technology' => 5, // Disaster Ops Monitoring Laptops, Server Units, CCTV Hubs
        'Default PPE'            => 5, // Standard Tangible Plant, Property, and Equipment
    ];

    /**
     * Check if the asset's acquisition cost qualifies as Capitalized Property, Plant, and Equipment (PPE).
     */
    public static function isCapitalizedPpe(?float $acquisitionCost): bool
    {
        return $acquisitionCost !== null && $acquisitionCost >= self::CAPITALIZATION_THRESHOLD;
    }

    /**
     * Check if the asset's acquisition cost qualifies as Semi-Expendable Property (< ₱50,000.00).
     */
    public static function isSemiExpendable(?float $acquisitionCost): bool
    {
        return $acquisitionCost !== null && $acquisitionCost > 0 && $acquisitionCost < self::CAPITALIZATION_THRESHOLD;
    }

    /**
     * Get the statutory classification label under Philippine COA standards.
     */
    public static function getClassification(?float $acquisitionCost): string
    {
        if (self::isCapitalizedPpe($acquisitionCost)) {
            return 'Capitalized PPE (₱50,000+)';
        }

        if (self::isSemiExpendable($acquisitionCost)) {
            return 'Semi-Expendable Property (< ₱50,000)';
        }

        return 'Unclassified / Zero Cost';
    }

    /**
     * Determine standard useful life in years based on category name or type name.
     */
    public static function getStandardUsefulLife(?string $categoryName = null, ?string $typeName = null): int
    {
        $haystack = strtolower(($categoryName ?? '') . ' ' . ($typeName ?? ''));

        if (str_contains($haystack, 'transport') || str_contains($haystack, 'vehicle') || str_contains($haystack, 'ambulance') || str_contains($haystack, 'truck')) {
            return self::USEFUL_LIFE_SCHEDULE['Transportation'];
        }

        if (str_contains($haystack, 'generator') || str_contains($haystack, 'power') || str_contains($haystack, 'pump') || str_contains($haystack, 'heavy')) {
            return self::USEFUL_LIFE_SCHEDULE['Emergency Equipment'];
        }

        if (str_contains($haystack, 'boat') || str_contains($haystack, 'rescue') || str_contains($haystack, 'cutter') || str_contains($haystack, 'saw')) {
            return self::USEFUL_LIFE_SCHEDULE['Rescue Equipment'];
        }

        if (str_contains($haystack, 'radio') || str_contains($haystack, 'communication') || str_contains($haystack, 'base') || str_contains($haystack, 'repeater')) {
            return self::USEFUL_LIFE_SCHEDULE['Communication Devices'];
        }

        if (str_contains($haystack, 'medical') || str_contains($haystack, 'aed') || str_contains($haystack, 'defibrillator') || str_contains($haystack, 'first aid')) {
            return self::USEFUL_LIFE_SCHEDULE['Medical Supplies'];
        }

        if (str_contains($haystack, 'computer') || str_contains($haystack, 'laptop') || str_contains($haystack, 'server') || str_contains($haystack, 'cctv')) {
            return self::USEFUL_LIFE_SCHEDULE['Information Technology'];
        }

        return self::USEFUL_LIFE_SCHEDULE['Default PPE'];
    }

    /**
     * Compute statutory salvage value.
     * Enforces COA limit: Items < ₱50,000 cannot have a salvage value (returns 0.00).
     */
    public static function calculateSalvageValue(?float $acquisitionCost, ?float $customSalvage = null): float
    {
        if (!self::isCapitalizedPpe($acquisitionCost)) {
            return 0.0;
        }

        if ($customSalvage !== null && $customSalvage > 0) {
            return round(min($customSalvage, $acquisitionCost), 2);
        }

        return round($acquisitionCost * self::DEFAULT_RESIDUAL_RATE, 2);
    }

    /**
     * Compute depreciable amount under Philippine COA Straight-Line method.
     * Enforces COA limit: Items < ₱50,000 cannot have depreciation (returns 0.00).
     */
    public static function calculateDepreciableAmount(?float $acquisitionCost, ?float $salvageValue = null): float
    {
        if (!self::isCapitalizedPpe($acquisitionCost)) {
            return 0.0;
        }

        $salvage = $salvageValue ?? self::calculateSalvageValue($acquisitionCost);
        return max(0.0, round($acquisitionCost - $salvage, 2));
    }

    /**
     * Automatically populate COA depreciation and salvage parameters for a newly stored asset.
     */
    public static function applyCoaAccountingParameters(array $attributes, ?string $categoryName = null, ?string $typeName = null): array
    {
        $cost = isset($attributes['acquisition_cost']) ? (float) $attributes['acquisition_cost'] : 0.0;

        if ($cost < self::CAPITALIZATION_THRESHOLD) {
            // Semi-Expendable Property: Non-depreciable, ₱0.00 salvage value
            $attributes['salvage_value'] = 0.00;
            $attributes['salvage_notes'] = $attributes['salvage_notes'] 
                ?? 'Semi-Expendable Property (< ₱50,000.00 statutory threshold under COA Circular No. 2022-004 / GAM for LGUs). Non-depreciable; residual value set to ₱0.00.';
            
            // Set useful life to standard lifecycle or existing value, but depreciation accessors evaluate to 0
            if (empty($attributes['useful_life_years'])) {
                $attributes['useful_life_years'] = self::getStandardUsefulLife($categoryName, $typeName);
            }
        } else {
            // Capitalized PPE (₱50,000.00+)
            if (empty($attributes['salvage_value']) || (float) $attributes['salvage_value'] <= 0) {
                $attributes['salvage_value'] = round($cost * self::DEFAULT_RESIDUAL_RATE, 2);
                $attributes['salvage_notes'] = $attributes['salvage_notes'] 
                    ?? 'Standard 10% residual value applied per COA Circular No. 2003-007 / GAM for LGUs.';
            }

            if (empty($attributes['useful_life_years']) || (int) $attributes['useful_life_years'] <= 0) {
                $attributes['useful_life_years'] = self::getStandardUsefulLife($categoryName, $typeName);
            }
        }

        return $attributes;
    }
}

