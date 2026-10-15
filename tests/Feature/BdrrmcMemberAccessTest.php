<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BdrrmcMemberAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $member;
    private User $inventoryOfficer;
    private User $maintenanceOfficer;
    private User $admin;
    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->member = User::factory()->create([
            'role'      => 'inventory_officer',
            'is_active' => true,
        ]);

        $this->inventoryOfficer = $this->member;

        $this->maintenanceOfficer = User::factory()->create([
            'role'      => 'maintenance_officer',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'role'      => 'administrator',
            'is_active' => true,
        ]);

        $category = AssetCategory::create([
            'name'        => 'Rescue Equipment',
            'description' => 'Disaster tools',
            'status'      => 'active',
        ]);

        $type = AssetType::create([
            'category_id' => $category->id,
            'name'        => 'Rescue Chainsaw',
        ]);

        $location = Location::create([
            'name'             => 'Purok Central Storage',
            'storage_capacity' => 50,
        ]);

        $this->asset = Asset::create([
            'code'              => 'TEST-AST-01',
            'name'              => 'Heavy Chainsaw',
            'category_id'       => $category->id,
            'type_id'           => $type->id,
            'location_id'       => $location->id,
            'condition'         => 'good',
            'status'            => 'available',
            'acquisition_cost'  => 15000.00,
            'useful_life_years' => 5,
        ]);
    }

    public function test_member_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->member)->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_member_can_access_asset_management_modules(): void
    {
        // Asset Registry
        $this->actingAs($this->member)->get(route('assets.index'))->assertOk();

        // Asset Accessories & Attachments
        $this->actingAs($this->member)->get(route('attachments.index'))->assertOk();

        // Asset Needs / Operational Requirements
        $this->actingAs($this->member)->get(route('operational-requirements.index'))->assertOk();

        // Borrowed / Return (Deployments)
        $this->actingAs($this->member)->get(route('deployment.index'))->assertOk();
    }

    public function test_member_can_access_operations_modules(): void
    {
        // Maintenance
        $this->actingAs($this->member)->get(route('maintenance.index'))->assertOk();

        // Monitoring / Reported Assets
        $this->actingAs($this->member)->get(route('reported-assets.index'))->assertOk();
    }

    public function test_member_can_access_tracking_asset_history(): void
    {
        $this->actingAs($this->member)->get(route('asset-history.index'))->assertOk();
    }

    public function test_member_can_access_account_settings(): void
    {
        $this->actingAs($this->member)->get(route('profile.edit'))->assertOk();
    }

    public function test_member_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->member)->get(route('users.index'));
        $response->assertForbidden();

        $rolesResponse = $this->actingAs($this->member)->get(route('roles.index'));
        $rolesResponse->assertForbidden();
    }

    public function test_member_cannot_access_depreciation_tracking(): void
    {
        $response = $this->actingAs($this->member)->get(route('financials.depreciation'));
        $response->assertForbidden();

        $exportResponse = $this->actingAs($this->member)->get(route('financials.depreciation.export'));
        $exportResponse->assertForbidden();
    }

    public function test_member_cannot_access_salvage_value_records(): void
    {
        $response = $this->actingAs($this->member)->get(route('financials.salvage'));
        $response->assertForbidden();

        $exportResponse = $this->actingAs($this->member)->get(route('financials.salvage.export'));
        $exportResponse->assertForbidden();
    }

    public function test_member_cannot_access_reports_center(): void
    {
        $response = $this->actingAs($this->member)->get(route('reports.index'));
        $response->assertForbidden();

        $exportResponse = $this->actingAs($this->member)->get(route('reports.export'));
        $exportResponse->assertForbidden();
    }

    public function test_member_cannot_delete_asset(): void
    {
        $response = $this->actingAs($this->member)->delete(route('assets.destroy', $this->asset));
        $response->assertForbidden();

        $this->assertDatabaseHas('assets', ['id' => $this->asset->id]);
    }

    public function test_member_cannot_modify_locations(): void
    {
        $response = $this->actingAs($this->member)->post(route('locations.store'), [
            'name' => 'Unauthorized Location',
        ]);
        $response->assertForbidden();
    }

    public function test_member_cannot_escalate_role_via_profile_update(): void
    {
        $response = $this->actingAs($this->member)->patch(route('profile.update'), [
            'name'  => 'Member Attempt',
            'email' => $this->member->email,
            'role'  => 'administrator',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $this->member->refresh();

        $this->assertEquals('inventory_officer', $this->member->role);
        $this->assertFalse($this->member->isAdmin());
    }

    public function test_inventory_officer_cannot_create_maintenance_work_orders(): void
    {
        // Inventory officer manages all equipment works aside from maintenance
        $response = $this->actingAs($this->inventoryOfficer)->post(route('maintenance.store'), [
            'asset_id'       => $this->asset->id,
            'type'           => 'repair',
            'status'         => 'in_progress',
            'scheduled_date' => now()->toDateString(),
        ]);

        $response->assertForbidden();
    }

    public function test_inventory_officer_cannot_record_inspections(): void
    {
        $response = $this->actingAs($this->inventoryOfficer)->post(route('inspection.store'), [
            'asset_id'        => $this->asset->id,
            'inspection_date' => now()->toDateString(),
            'result'          => 'passed',
        ]);

        $response->assertForbidden();
    }

    public function test_maintenance_officer_can_manage_maintenance_and_inspections(): void
    {
        // Maintenance officer can create work order
        $response = $this->actingAs($this->maintenanceOfficer)->post(route('maintenance.store'), [
            'asset_id'       => $this->asset->id,
            'type'           => 'repair',
            'priority'       => 'medium',
            'status'         => 'scheduled',
            'scheduled_date' => now()->toDateString(),
            'notes'          => 'Engine spark plug check',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('maintenances', [
            'asset_id' => $this->asset->id,
            'type'     => 'repair',
        ]);

        // Maintenance officer can record physical inspection
        $inspResponse = $this->actingAs($this->maintenanceOfficer)->post(route('inspection.store'), [
            'asset_id'        => $this->asset->id,
            'inspection_date' => now()->toDateString(),
            'result'          => 'passed',
            'findings'        => 'Unit operates within specifications.',
        ]);

        $inspResponse->assertRedirect();
        $this->assertDatabaseHas('inspections', [
            'asset_id' => $this->asset->id,
            'result'   => 'passed',
        ]);
    }

    public function test_maintenance_officer_cannot_create_assets_or_deployments(): void
    {
        // Maintenance officer cannot register new assets
        $assetResponse = $this->actingAs($this->maintenanceOfficer)->post(route('assets.store'), [
            'name'             => 'Unauthorized Asset Registration',
            'category_id'      => $this->asset->category_id,
            'location_id'      => $this->asset->location_id,
            'condition'        => 'good',
            'status'           => 'available',
            'acquisition_cost' => 60000.00,
        ]);

        $assetResponse->assertForbidden();

        // Maintenance officer cannot create deployments
        $deployResponse = $this->actingAs($this->maintenanceOfficer)->post(route('deployment.store'), [
            'asset_id'        => $this->asset->id,
            'deployed_to'     => 'Rescue Squad Alpha',
            'location'        => 'Purok 1 Flood Zone',
            'deployment_date' => now()->toDateString(),
            'purpose'         => 'Disaster Rescue Drill',
            'status'          => 'deployed',
        ]);

        $deployResponse->assertForbidden();
    }

    public function test_admin_can_access_all_restricted_modules(): void
    {
        $this->actingAs($this->admin)->get(route('users.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('financials.depreciation'))->assertOk();
        $this->actingAs($this->admin)->get(route('financials.salvage'))->assertOk();
        $this->actingAs($this->admin)->get(route('reports.index'))->assertOk();
    }
}

