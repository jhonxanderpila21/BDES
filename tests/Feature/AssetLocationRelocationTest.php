<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AuditLog;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetLocationRelocationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $viewer;
    protected Location $locationA;
    protected Location $locationB;
    protected AssetCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'administrator',
        ]);

        $this->viewer = User::factory()->create([
            'role' => 'viewer',
        ]);

        $this->locationA = Location::create([
            'name' => 'Depot Alpha',
            'type' => 'depot',
            'address' => '123 North St',
            'storage_capacity' => 50,
        ]);

        $this->locationB = Location::create([
            'name' => 'Depot Bravo',
            'type' => 'depot',
            'address' => '456 South St',
            'storage_capacity' => 100,
        ]);

        $this->category = AssetCategory::create([
            'name' => 'Emergency Vehicles',
            'description' => 'Rescue and transport vehicles',
        ]);
    }

    protected function createAsset(array $attributes = []): Asset
    {
        static $counter = 100;
        $counter++;

        return Asset::create(array_merge([
            'code'             => "AST-RELOC-{$counter}",
            'name'             => "Asset {$counter}",
            'category_id'      => $this->category->id,
            'location_id'      => $this->locationA->id,
            'condition'        => 'good',
            'status'           => 'available',
            'acquisition_cost' => 15000.00,
        ], $attributes));
    }

    public function test_locations_index_page_loads_with_all_locations(): void
    {
        $response = $this->actingAs($this->admin)->get(route('locations.index'));

        $response->assertStatus(200);
        $response->assertViewHas('allLocations');
        $response->assertSee('Depot Alpha');
        $response->assertSee('Depot Bravo');
    }

    public function test_authorized_user_can_relocate_single_asset(): void
    {
        $asset = $this->createAsset([
            'code' => 'AST-RELOC-001',
            'name' => 'Rescue Boat 1',
        ]);

        $response = $this->actingAs($this->admin)->post(route('locations.relocate-asset'), [
            'asset_id' => $asset->id,
            'location_id' => $this->locationB->id,
            'notes' => 'Prepositioned for flood season',
        ]);

        $response->assertRedirect(route('locations.index', ['view_location' => $this->locationB->id]));
        $response->assertSessionHas('success');

        $this->assertEquals($this->locationB->id, $asset->fresh()->location_id);
    }

    public function test_authorized_user_can_batch_relocate_multiple_assets(): void
    {
        $asset1 = $this->createAsset([
            'code' => 'AST-BATCH-001',
            'name' => 'First Aid Kit A',
        ]);

        $asset2 = $this->createAsset([
            'code' => 'AST-BATCH-002',
            'name' => 'First Aid Kit B',
        ]);

        $response = $this->actingAs($this->admin)->post(route('locations.relocate-asset'), [
            'asset_ids' => [$asset1->id, $asset2->id],
            'location_id' => $this->locationB->id,
            'notes' => 'Consolidated storage',
        ]);

        $response->assertRedirect(route('locations.index', ['view_location' => $this->locationB->id]));
        $response->assertSessionHas('success');

        $this->assertEquals($this->locationB->id, $asset1->fresh()->location_id);
        $this->assertEquals($this->locationB->id, $asset2->fresh()->location_id);
    }

    public function test_relocation_records_location_changed_in_audit_logs(): void
    {
        $asset = $this->createAsset([
            'code' => 'AST-AUDIT-001',
            'name' => 'Generator 5kW',
        ]);

        $this->actingAs($this->admin)->post(route('locations.relocate-asset'), [
            'asset_id' => $asset->id,
            'location_id' => $this->locationB->id,
            'notes' => 'Moved for maintenance readiness',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Asset::class,
            'auditable_id' => $asset->id,
            'event' => 'location_changed',
            'user_id' => $this->admin->id,
        ]);

        $log = AuditLog::where('auditable_id', $asset->id)->where('event', 'location_changed')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('Depot Alpha', $log->description);
        $this->assertStringContainsString('Depot Bravo', $log->description);
        $this->assertStringContainsString('Moved for maintenance readiness', $log->description);
        $this->assertEquals($this->locationA->id, $log->old_values['location_id']);
        $this->assertEquals($this->locationB->id, $log->new_values['location_id']);
    }

    public function test_viewer_cannot_relocate_assets(): void
    {
        $asset = $this->createAsset([
            'code' => 'AST-VIEWER-001',
            'name' => 'Chainsaw Pro',
        ]);

        $response = $this->actingAs($this->viewer)->post(route('locations.relocate-asset'), [
            'asset_id' => $asset->id,
            'location_id' => $this->locationB->id,
        ]);

        $response->assertStatus(403);
        $this->assertEquals($this->locationA->id, $asset->fresh()->location_id);
    }

    public function test_relocation_with_return_to_source_flag_redirects_to_source_location(): void
    {
        $asset = $this->createAsset([
            'code' => 'AST-RETURN-001',
            'name' => 'Water Pump X',
        ]);

        $response = $this->actingAs($this->admin)->post(route('locations.relocate-asset'), [
            'asset_id' => $asset->id,
            'location_id' => $this->locationB->id,
            'return_to_source' => 1,
        ]);

        $response->assertRedirect(route('locations.index', ['view_location' => $this->locationA->id]));
    }

    public function test_relocation_fails_validation_for_non_existent_location(): void
    {
        $asset = $this->createAsset([
            'code' => 'AST-FAIL-001',
            'name' => 'Radio Unit',
        ]);

        $response = $this->actingAs($this->admin)->post(route('locations.relocate-asset'), [
            'asset_id' => $asset->id,
            'location_id' => 999999,
        ]);

        $response->assertSessionHasErrors('location_id');
        $this->assertEquals($this->locationA->id, $asset->fresh()->location_id);
    }
}
