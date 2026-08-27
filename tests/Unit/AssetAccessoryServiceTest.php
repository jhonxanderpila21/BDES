<?php

namespace Tests\Unit;

use App\Services\AssetAccessoryService;
use PHPUnit\Framework\TestCase;

class AssetAccessoryServiceTest extends TestCase
{
    public function test_derive_status_available_when_quantity_greater_than_zero()
    {
        $this->assertEquals(
            AssetAccessoryService::STATUS_AVAILABLE,
            AssetAccessoryService::deriveStatus(5)
        );

        $this->assertEquals(
            AssetAccessoryService::STATUS_AVAILABLE,
            AssetAccessoryService::deriveStatus(1)
        );
    }

    public function test_derive_status_not_available_when_quantity_is_zero()
    {
        $this->assertEquals(
            AssetAccessoryService::STATUS_NOT_AVAILABLE,
            AssetAccessoryService::deriveStatus(0)
        );
    }

    public function test_derive_status_not_available_when_quantity_is_negative()
    {
        // Even though validation blocks this, the service should handle it safely
        $this->assertEquals(
            AssetAccessoryService::STATUS_NOT_AVAILABLE,
            AssetAccessoryService::deriveStatus(-5)
        );
    }

    public function test_statuses_returns_all_valid_statuses()
    {
        $statuses = AssetAccessoryService::statuses();
        
        $this->assertContains(AssetAccessoryService::STATUS_AVAILABLE, $statuses);
        $this->assertContains(AssetAccessoryService::STATUS_NOT_AVAILABLE, $statuses);
        $this->assertCount(2, $statuses);
    }
}
