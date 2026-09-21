<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Deployment;
use App\Models\Location;
use App\Models\Maintenance;
use App\Models\User;
use App\Services\AssetLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $viewer;
    private AssetCategory $category;
    private Location $location;
    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'      => 'administrator',
            'is_active' => true,
        ]);

        $this->viewer = User::factory()->create([
            'role'      => 'viewer',
            'is_active' => true,
        ]);

        $this->category = AssetCategory::create([
            'name'   => 'Emergency Power',
            'status' => 'active',
        ]);

        $this->location = Location::create([
            'name'     => 'Central Disaster Depot',
            'capacity' => 50,
        ]);

        $this->asset = Asset::create([
            'code'             => 'AST-PWR001',
            'name'             => 'Cummins 5kW Diesel Generator',
            'category_id'      => $this->category->id,
            'location_id'      => $this->location->id,
            'condition'        => 'excellent',
            'status'           => 'available',
            'acquisition_cost' => 85000.00,
        ]);
    }

    public function test_valid_lifecycle_transitions(): void
    {
        // available -> under_maintenance
        $this->assertTrue(AssetLifecycleService::canTransition($this->asset, 'under_maintenance'));
        AssetLifecycleService::transition($this->asset, 'under_maintenance', 'Scheduled preventive checkup.');
        $this->assertEquals('under_maintenance', $this->asset->fresh()->status);

        // under_maintenance -> available
        $this->assertTrue(AssetLifecycleService::canTransition($this->asset, 'available'));
        AssetLifecycleService::transition($this->asset, 'available', 'Repairs completed.');
        $this->assertEquals('available', $this->asset->fresh()->status);

        // available -> deployed
        $this->assertTrue(AssetLifecycleService::canTransition($this->asset, 'deployed'));
        AssetLifecycleService::transition($this->asset, 'deployed', 'Dispatched to flood evacuation shelter.');
        $this->assertEquals('deployed', $this->asset->fresh()->status);
    }

    public function test_invalid_lifecycle_transition_is_rejected_in_service_and_controller(): void
    {
        // deployed directly to retired is invalid without returning first
        $this->asset->update(['status' => 'deployed']);

        $this->assertFalse(AssetLifecycleService::canTransition($this->asset, 'retired'));

        $response = $this->actingAs($this->admin)->put(route('assets.update', $this->asset), [
            'code'             => $this->asset->code,
            'name'             => $this->asset->name,
            'category_id'      => $this->category->id,
            'location_id'      => $this->location->id,
            'condition'        => 'excellent',
            'status'           => 'retired',
            'acquisition_cost' => 85000.00,
        ]);

        $response->assertSessionHasErrors('status');
        $this->assertEquals('deployed', $this->asset->fresh()->status);
    }

    public function test_asset_formal_decommissioning_and_retirement(): void
    {
        $response = $this->actingAs($this->admin)->post(route('assets.retire', $this->asset), [
            'retirement_reason' => 'Beyond economic repair due to saltwater flood submergence.',
        ]);

        $response->assertRedirect();
        $this->asset->refresh();

        $this->assertEquals('retired', $this->asset->status);
        $this->assertNotNull($this->asset->retirement_date);
        $this->assertEquals('Beyond economic repair due to saltwater flood submergence.', $this->asset->retirement_reason);
        $this->assertEquals($this->admin->id, $this->asset->retired_by);
    }

    public function test_safe_delete_permanently_removes_unlinked_asset(): void
    {
        $unlinked = Asset::create([
            'code'             => 'AST-TMP999',
            'name'             => 'Temporary Test Lamp',
            'category_id'      => $this->category->id,
            'location_id'      => $this->location->id,
            'condition'        => 'fair',
            'status'           => 'available',
            'acquisition_cost' => 1500.00,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('assets.destroy', $unlinked));
        $response->assertRedirect();
        $this->assertDatabaseMissing('assets', ['id' => $unlinked->id]);
    }

    public function test_safe_delete_retires_rather_than_deleting_asset_with_linked_history(): void
    {
        // Link a deployment to the asset
        Deployment::create([
            'asset_id'           => $this->asset->id,
            'user_id'            => $this->admin->id,
            'destination'        => 'Evacuation Center Gym',
            'status'             => 'returned',
            'deployment_date'    => now()->subDays(5),
            'expected_return_date'=> now()->subDays(2),
            'purpose'            => 'Emergency lighting',
            'responsible_person' => 'Brgy Tanod Team Alpha',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('assets.destroy', $this->asset));
        $response->assertRedirect();
        $this->assertDatabaseHas('assets', [
            'id'     => $this->asset->id,
            'status' => 'retired',
        ]);
    }

    public function test_viewer_role_is_forbidden_from_deleting_or_updating_assets(): void
    {
        $deleteResponse = $this->actingAs($this->viewer)->delete(route('assets.destroy', $this->asset));
        $deleteResponse->assertForbidden();

        $updateResponse = $this->actingAs($this->viewer)->put(route('assets.update', $this->asset), [
            'code'             => $this->asset->code,
            'name'             => 'Modified Name',
            'category_id'      => $this->category->id,
            'location_id'      => $this->location->id,
            'condition'        => 'good',
            'status'           => 'available',
        ]);
        $updateResponse->assertForbidden();
    }
}
