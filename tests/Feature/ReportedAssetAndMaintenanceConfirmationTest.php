<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\Location;
use App\Models\Maintenance;
use App\Models\ReportedAsset;
use App\Models\User;
use App\Services\ReportedAssetService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportedAssetAndMaintenanceConfirmationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $officer1;
    protected User $officer2;
    protected User $staff;
    protected Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'      => 'administrator',
            'is_active' => true,
        ]);

        $this->officer1 = User::factory()->create([
            'role'      => 'maintenance_officer',
            'is_active' => true,
        ]);

        $this->officer2 = User::factory()->create([
            'role'      => 'inventory_officer',
            'is_active' => true,
        ]);

        $this->staff = User::factory()->create([
            'role'      => 'viewer',
            'is_active' => true,
        ]);

        $category = AssetCategory::create([
            'name'        => 'Emergency Equipment',
            'description' => 'Test category',
        ]);

        $type = AssetType::create([
            'name'        => 'Generator',
            'category_id' => $category->id,
        ]);

        $location = Location::create([
            'name'     => 'Barangay Hall',
            'capacity' => 100,
        ]);

        $this->asset = Asset::create([
            'code'        => 'GEN-TEST-001',
            'name'        => 'Heavy Duty Generator',
            'category_id' => $category->id,
            'type_id'     => $type->id,
            'location_id' => $location->id,
            'condition'   => 'good',
            'status'      => 'available',
        ]);
    }

    protected function createReport(string $status = ReportedAssetService::STATUS_PENDING): ReportedAsset
    {
        return ReportedAsset::create([
            'asset_id'       => $this->asset->id,
            'asset_category' => 'Emergency Equipment',
            'asset_name'     => 'Heavy Duty Generator',
            'reported_by'    => $this->admin->id,
            'reported_type'  => 'Defect',
            'status'         => $status,
            'location'       => 'Barangay Hall',
            'description'    => 'Oil leak and rattling sound',
            'reported_date'  => Carbon::today(),
        ]);
    }

    public function test_can_confirm_reported_asset_and_assign_multiple_personnel(): void
    {
        $report = $this->createReport(ReportedAssetService::STATUS_PENDING);

        $this->assertFalse($report->isConfirmed());
        $this->assertCount(0, $report->assignedPersonnel);

        $response = $this->actingAs($this->admin)->post(route('reported-assets.confirm', $report), [
            'personnel' => [$this->officer1->id, $this->officer2->id],
            'notes'     => 'Verified damage in field. Immediate fix assigned.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $report->refresh();
        $this->assertTrue($report->isConfirmed());
        $this->assertEquals($this->admin->id, $report->confirmed_by);
        $this->assertNotNull($report->confirmed_at);
        $this->assertEquals(ReportedAssetService::STATUS_ONGOING, $report->status);

        $this->assertCount(2, $report->assignedPersonnel);
        $this->assertTrue($report->assignedPersonnel->contains($this->officer1->id));
        $this->assertTrue($report->assignedPersonnel->contains($this->officer2->id));

        // Check history log
        $this->assertDatabaseHas('reported_asset_status_histories', [
            'reported_asset_id' => $report->id,
            'to_status'         => ReportedAssetService::STATUS_ONGOING,
            'changed_by'        => $this->admin->id,
        ]);
    }

    public function test_can_update_assigned_personnel_for_reported_asset(): void
    {
        $report = $this->createReport(ReportedAssetService::STATUS_ONGOING);
        $report->update([
            'confirmed_at' => now(),
            'confirmed_by' => $this->admin->id,
        ]);

        $report->assignedPersonnel()->sync([$this->officer1->id]);
        $this->assertCount(1, $report->fresh()->assignedPersonnel);

        $response = $this->actingAs($this->admin)->post(route('reported-assets.assign-personnel', $report), [
            'personnel' => [$this->officer2->id],
            'notes'     => 'Reassigned to inventory officer.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $report->refresh();
        $this->assertCount(1, $report->assignedPersonnel);
        $this->assertTrue($report->assignedPersonnel->contains($this->officer2->id));
        $this->assertFalse($report->assignedPersonnel->contains($this->officer1->id));
    }

    public function test_read_only_staff_cannot_confirm_or_assign_reported_asset(): void
    {
        $report = $this->createReport(ReportedAssetService::STATUS_PENDING);

        $response1 = $this->actingAs($this->staff)->post(route('reported-assets.confirm', $report), [
            'personnel' => [$this->officer1->id],
        ]);
        $response1->assertStatus(403);

        $response2 = $this->actingAs($this->staff)->post(route('reported-assets.assign-personnel', $report), [
            'personnel' => [$this->officer1->id],
        ]);
        $response2->assertStatus(403);
    }

    public function test_can_create_maintenance_with_multiple_personnel(): void
    {
        $response = $this->actingAs($this->admin)->post(route('maintenance.store'), [
            'asset_id'            => $this->asset->id,
            'type'                => 'repair',
            'priority'            => 'high',
            'status'              => 'scheduled',
            'technician'          => 'In-house Team',
            'scheduled_date'      => now()->format('Y-m-d'),
            'assigned_personnel'  => [$this->officer1->id, $this->officer2->id],
            'confirm_immediately' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $maintenance = Maintenance::latest('id')->first();
        $this->assertNotNull($maintenance);
        $this->assertTrue($maintenance->isConfirmed());
        $this->assertEquals($this->admin->id, $maintenance->confirmed_by);
        $this->assertCount(2, $maintenance->assignedPersonnel);
        $this->assertTrue($maintenance->assignedPersonnel->contains($this->officer1->id));
        $this->assertTrue($maintenance->assignedPersonnel->contains($this->officer2->id));
        // Backward compatibility
        $this->assertEquals($this->officer1->id, $maintenance->assigned_to);
    }

    public function test_can_confirm_maintenance_and_assign_multiple_personnel(): void
    {
        $maintenance = Maintenance::create([
            'asset_id'       => $this->asset->id,
            'type'           => 'repair',
            'priority'       => 'medium',
            'status'         => 'scheduled',
            'technician'     => 'AutoCare',
            'scheduled_date' => now()->format('Y-m-d'),
        ]);

        $this->assertFalse($maintenance->isConfirmed());
        $this->assertCount(0, $maintenance->assignedPersonnel);

        $response = $this->actingAs($this->admin)->post(route('maintenance.confirm', $maintenance), [
            'personnel' => [$this->officer1->id, $this->officer2->id],
            'notes'     => 'Authorized for emergency repair.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $maintenance->refresh();
        $this->assertTrue($maintenance->isConfirmed());
        $this->assertEquals($this->admin->id, $maintenance->confirmed_by);
        $this->assertNotNull($maintenance->confirmed_at);
        $this->assertCount(2, $maintenance->assignedPersonnel);
        $this->assertTrue($maintenance->assignedPersonnel->contains($this->officer1->id));
        $this->assertTrue($maintenance->assignedPersonnel->contains($this->officer2->id));
    }

    public function test_can_update_maintenance_assigned_personnel(): void
    {
        $maintenance = Maintenance::create([
            'asset_id'       => $this->asset->id,
            'type'           => 'repair',
            'priority'       => 'medium',
            'status'         => 'in_progress',
            'technician'     => 'AutoCare',
            'scheduled_date' => now()->format('Y-m-d'),
        ]);

        $maintenance->assignedPersonnel()->sync([$this->officer1->id]);

        $response = $this->actingAs($this->admin)->post(route('maintenance.assign-personnel', $maintenance), [
            'personnel' => [$this->officer1->id, $this->officer2->id],
            'notes'     => 'Added inventory officer for parts verification.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $maintenance->refresh();
        $this->assertCount(2, $maintenance->assignedPersonnel);
    }

    public function test_read_only_staff_cannot_confirm_or_assign_maintenance(): void
    {
        $maintenance = Maintenance::create([
            'asset_id'       => $this->asset->id,
            'type'           => 'routine',
            'priority'       => 'low',
            'status'         => 'scheduled',
            'technician'     => 'AutoCare',
            'scheduled_date' => now()->format('Y-m-d'),
        ]);

        $response1 = $this->actingAs($this->staff)->post(route('maintenance.confirm', $maintenance), [
            'personnel' => [$this->officer1->id],
        ]);
        $response1->assertStatus(403);

        $response2 = $this->actingAs($this->staff)->post(route('maintenance.assign-personnel', $maintenance), [
            'personnel' => [$this->officer1->id],
        ]);
        $response2->assertStatus(403);
    }
}
