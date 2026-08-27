<?php

namespace Tests\Unit;

use App\Services\AssetNeedStatusService;
use PHPUnit\Framework\TestCase;

class AssetNeedStatusServiceTest extends TestCase
{
    /**
     * Full capacity → Monitoring Only
     */
    public function test_fully_stocked_returns_monitoring_only(): void
    {
        $status = AssetNeedStatusService::deriveStatus(
            currentLevel: 100,
            minimumLevel: 20,
            maximumCapacity: 100
        );

        $this->assertEquals('Monitoring Only', $status);
    }

    /**
     * Within safe range (above minimum, below max) → Monitoring Only
     */
    public function test_within_safe_range_returns_monitoring_only(): void
    {
        $status = AssetNeedStatusService::deriveStatus(
            currentLevel: 50,
            minimumLevel: 20,
            maximumCapacity: 100
        );

        $this->assertEquals('Monitoring Only', $status);
    }

    /**
     * Exactly at minimum level → Monitoring Only (still safe)
     */
    public function test_at_minimum_level_returns_monitoring_only(): void
    {
        $status = AssetNeedStatusService::deriveStatus(
            currentLevel: 20,
            minimumLevel: 20,
            maximumCapacity: 100
        );

        $this->assertEquals('Monitoring Only', $status);
    }

    /**
     * Below minimum but above zero → Requires Maintenance
     */
    public function test_below_minimum_returns_requires_maintenance(): void
    {
        $status = AssetNeedStatusService::deriveStatus(
            currentLevel: 15,
            minimumLevel: 20,
            maximumCapacity: 100
        );

        $this->assertEquals('Requires Maintenance', $status);
    }

    /**
     * Just barely below minimum → Requires Maintenance
     */
    public function test_just_below_minimum_returns_requires_maintenance(): void
    {
        $status = AssetNeedStatusService::deriveStatus(
            currentLevel: 19.99,
            minimumLevel: 20,
            maximumCapacity: 100
        );

        $this->assertEquals('Requires Maintenance', $status);
    }

    /**
     * Depleted (zero) → Under Maintenance
     */
    public function test_depleted_returns_under_maintenance(): void
    {
        $status = AssetNeedStatusService::deriveStatus(
            currentLevel: 0,
            minimumLevel: 20,
            maximumCapacity: 100
        );

        $this->assertEquals('Under Maintenance', $status);
    }

    /**
     * Zero current with zero minimum → Under Maintenance (depleted always wins)
     */
    public function test_zero_with_zero_minimum_returns_under_maintenance(): void
    {
        $status = AssetNeedStatusService::deriveStatus(
            currentLevel: 0,
            minimumLevel: 0,
            maximumCapacity: 100
        );

        $this->assertEquals('Under Maintenance', $status);
    }

    /**
     * Units-type scenario: 1 of 4 at min 1 → Monitoring Only
     */
    public function test_units_at_minimum_returns_monitoring_only(): void
    {
        $status = AssetNeedStatusService::deriveStatus(
            currentLevel: 1,
            minimumLevel: 1,
            maximumCapacity: 4
        );

        $this->assertEquals('Monitoring Only', $status);
    }

    /**
     * statuses() returns all three valid values.
     */
    public function test_statuses_returns_all_valid_values(): void
    {
        $statuses = AssetNeedStatusService::statuses();

        $this->assertCount(3, $statuses);
        $this->assertContains('Monitoring Only', $statuses);
        $this->assertContains('Requires Maintenance', $statuses);
        $this->assertContains('Under Maintenance', $statuses);
    }

    /**
     * units() returns Liters and Units.
     */
    public function test_units_returns_valid_values(): void
    {
        $units = AssetNeedStatusService::units();

        $this->assertCount(2, $units);
        $this->assertContains('Liters', $units);
        $this->assertContains('Units', $units);
    }
}
