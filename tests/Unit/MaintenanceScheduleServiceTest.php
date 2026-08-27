<?php

namespace Tests\Unit;

use App\Services\MaintenanceScheduleService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class MaintenanceScheduleServiceTest extends TestCase
{
    public function test_calculate_next_due_date()
    {
        $lastDate = Carbon::create(2026, 1, 1);
        $nextDue = MaintenanceScheduleService::calculateNextDueDate($lastDate, 30);
        
        $this->assertEquals('2026-01-31', $nextDue->format('Y-m-d'));
    }

    public function test_derive_status_monitoring_only()
    {
        $nextDue = Carbon::create(2026, 1, 15);
        $today = Carbon::create(2026, 1, 1); // 14 days before due

        $status = MaintenanceScheduleService::deriveStatus($nextDue, $today);
        $this->assertEquals(MaintenanceScheduleService::STATUS_MONITORING, $status);
    }

    public function test_derive_status_maintenance_due_on_exact_date()
    {
        $nextDue = Carbon::create(2026, 1, 15);
        $today = Carbon::create(2026, 1, 15); // Exactly on due date

        $status = MaintenanceScheduleService::deriveStatus($nextDue, $today);
        $this->assertEquals(MaintenanceScheduleService::STATUS_DUE, $status);
    }

    public function test_derive_status_maintenance_due_within_grace_period()
    {
        $nextDue = Carbon::create(2026, 1, 15);
        $today = Carbon::create(2026, 1, 20); // 5 days late, grace is 7

        $status = MaintenanceScheduleService::deriveStatus($nextDue, $today);
        $this->assertEquals(MaintenanceScheduleService::STATUS_DUE, $status);
    }

    public function test_derive_status_overdue_after_grace_period()
    {
        $nextDue = Carbon::create(2026, 1, 15);
        $today = Carbon::create(2026, 1, 23); // 8 days late, grace is 7

        $status = MaintenanceScheduleService::deriveStatus($nextDue, $today);
        $this->assertEquals(MaintenanceScheduleService::STATUS_OVERDUE, $status);
    }

    public function test_get_default_days_for_interval()
    {
        $this->assertEquals(30, MaintenanceScheduleService::getDefaultDaysForInterval('Monthly'));
        $this->assertEquals(90, MaintenanceScheduleService::getDefaultDaysForInterval('Quarterly'));
        $this->assertEquals(365, MaintenanceScheduleService::getDefaultDaysForInterval('Yearly'));
        $this->assertEquals(30, MaintenanceScheduleService::getDefaultDaysForInterval('Unknown')); // fallback
    }
}
