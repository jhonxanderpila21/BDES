<?php

namespace Tests\Unit;

use App\Services\ReportedAssetService;
use PHPUnit\Framework\TestCase;

class ReportedAssetServiceTest extends TestCase
{
    // ─── Valid Transitions ───

    public function test_pending_can_transition_to_for_review(): void
    {
        $this->assertTrue(ReportedAssetService::isValidTransition('Pending', 'For Review'));
    }

    public function test_pending_can_transition_to_ongoing(): void
    {
        $this->assertTrue(ReportedAssetService::isValidTransition('Pending', 'Ongoing'));
    }

    public function test_for_review_can_transition_to_ongoing(): void
    {
        $this->assertTrue(ReportedAssetService::isValidTransition('For Review', 'Ongoing'));
    }

    public function test_ongoing_can_transition_to_done(): void
    {
        $this->assertTrue(ReportedAssetService::isValidTransition('Ongoing', 'Done'));
    }

    // ─── Invalid Transitions ───

    public function test_pending_cannot_transition_to_done(): void
    {
        $this->assertFalse(ReportedAssetService::isValidTransition('Pending', 'Done'));
    }

    public function test_done_is_terminal_cannot_transition_to_anything(): void
    {
        foreach (ReportedAssetService::statuses() as $status) {
            $this->assertFalse(
                ReportedAssetService::isValidTransition('Done', $status),
                "Done should not transition to {$status}"
            );
        }
    }

    public function test_done_cannot_transition_to_ongoing(): void
    {
        $this->assertFalse(ReportedAssetService::isValidTransition('Done', 'Ongoing'));
    }

    public function test_for_review_cannot_transition_to_done(): void
    {
        $this->assertFalse(ReportedAssetService::isValidTransition('For Review', 'Done'));
    }

    // ─── Enum Lists ───

    public function test_reported_types_returns_all_types(): void
    {
        $types = ReportedAssetService::reportedTypes();
        $this->assertContains('Damage', $types);
        $this->assertContains('Missing', $types);
        $this->assertContains('Malfunction', $types);
        $this->assertContains('Maintenance', $types);
        $this->assertContains('Replacement', $types);
        $this->assertContains('Other', $types);
        $this->assertCount(6, $types);
    }

    public function test_statuses_returns_all_statuses(): void
    {
        $statuses = ReportedAssetService::statuses();
        $this->assertContains('Pending', $statuses);
        $this->assertContains('For Review', $statuses);
        $this->assertContains('Ongoing', $statuses);
        $this->assertContains('Done', $statuses);
        $this->assertCount(4, $statuses);
    }

    public function test_open_statuses_excludes_done(): void
    {
        $open = ReportedAssetService::openStatuses();
        $this->assertContains('Pending', $open);
        $this->assertContains('For Review', $open);
        $this->assertContains('Ongoing', $open);
        $this->assertNotContains('Done', $open);
        $this->assertCount(3, $open);
    }

    public function test_allowed_next_statuses(): void
    {
        $this->assertEquals(['For Review', 'Ongoing'], ReportedAssetService::allowedNextStatuses('Pending'));
        $this->assertEquals(['Ongoing'], ReportedAssetService::allowedNextStatuses('For Review'));
        $this->assertEquals(['Done'], ReportedAssetService::allowedNextStatuses('Ongoing'));
        $this->assertEquals([], ReportedAssetService::allowedNextStatuses('Done'));
    }
}
