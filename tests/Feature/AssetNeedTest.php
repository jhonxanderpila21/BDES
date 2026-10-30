<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetNeed;
use App\Models\AssetType;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetNeedTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $staff;
    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role'      => 'administrator',
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
            'code'        => 'GEN-TEST',
            'name'        => 'Test Generator',
            'category_id' => $category->id,
            'type_id'     => $type->id,
            'location_id' => $location->id,
            'condition'   => 'good',
            'status'      => 'available',
        ]);
    }

    // ── CRUD ────────────────────────────────────────

    public function test_index_displays_asset_needs(): void
    {
        AssetNeed::create([
            'asset_id'         => $this->asset->id,
            'asset_type'       => 'Generator',
            'requirement'      => 'Diesel Fuel',
            'unit'             => 'Liters',
            'maximum_capacity' => 100,
            'minimum_level'    => 20,
            'current_level'    => 50,
        ]);

        $response = $this->actingAs($this->user)->get(route('asset-needs.index'));

        $response->assertStatus(200);
        $response->assertSee('Diesel Fuel');
        $response->assertSee('Generator');
    }

    public function test_store_creates_asset_need(): void
    {
        $response = $this->actingAs($this->user)->post(route('asset-needs.store'), [
            'asset_id'         => $this->asset->id,
            'requirement'      => 'Engine Oil',
            'unit'             => 'Liters',
            'maximum_capacity' => 5,
            'minimum_level'    => 1,
            'current_level'    => 3,
        ]);

        $response->assertRedirect(route('asset-needs.index'));
        $this->assertDatabaseHas('asset_needs', [
            'requirement'  => 'Engine Oil',
            'asset_type'   => 'Generator', // auto-synced from asset's type
            'current_level' => 3.00,
        ]);
    }

    public function test_update_modifies_asset_need(): void
    {
        $need = AssetNeed::create([
            'asset_id'         => $this->asset->id,
            'asset_type'       => 'Generator',
            'requirement'      => 'Diesel Fuel',
            'unit'             => 'Liters',
            'maximum_capacity' => 100,
            'minimum_level'    => 20,
            'current_level'    => 50,
        ]);

        $response = $this->actingAs($this->user)->put(route('asset-needs.update', $need), [
            'asset_id'         => $this->asset->id,
            'requirement'      => 'Diesel Fuel',
            'unit'             => 'Liters',
            'maximum_capacity' => 100,
            'minimum_level'    => 20,
            'current_level'    => 10,
        ]);

        $response->assertRedirect(route('asset-needs.index'));
        $need->refresh();
        $this->assertEquals(10.00, (float) $need->current_level);
    }

    public function test_destroy_deletes_asset_need(): void
    {
        $need = AssetNeed::create([
            'asset_id'         => $this->asset->id,
            'asset_type'       => 'Generator',
            'requirement'      => 'Diesel Fuel',
            'unit'             => 'Liters',
            'maximum_capacity' => 100,
            'minimum_level'    => 20,
            'current_level'    => 50,
        ]);

        $response = $this->actingAs($this->user)->delete(route('asset-needs.destroy', $need));

        $response->assertRedirect(route('asset-needs.index'));
        $this->assertDatabaseMissing('asset_needs', ['id' => $need->id]);
    }

    // ── Status Auto-Derivation ──────────────────────

    public function test_status_auto_derived_monitoring_only_on_create(): void
    {
        $need = AssetNeed::create([
            'asset_id'         => $this->asset->id,
            'asset_type'       => 'Generator',
            'requirement'      => 'Diesel Fuel',
            'unit'             => 'Liters',
            'maximum_capacity' => 100,
            'minimum_level'    => 20,
            'current_level'    => 50,
        ]);

        $this->assertEquals('Monitoring Only', $need->status);
    }

    public function test_status_auto_derived_requires_maintenance_on_create(): void
    {
        $need = AssetNeed::create([
            'asset_id'         => $this->asset->id,
            'asset_type'       => 'Generator',
            'requirement'      => 'Diesel Fuel',
            'unit'             => 'Liters',
            'maximum_capacity' => 100,
            'minimum_level'    => 20,
            'current_level'    => 15,
        ]);

        $this->assertEquals('Requires Maintenance', $need->status);
    }

    public function test_status_auto_derived_under_maintenance_on_create(): void
    {
        $need = AssetNeed::create([
            'asset_id'         => $this->asset->id,
            'asset_type'       => 'Generator',
            'requirement'      => 'Diesel Fuel',
            'unit'             => 'Liters',
            'maximum_capacity' => 100,
            'minimum_level'    => 20,
            'current_level'    => 0,
        ]);

        $this->assertEquals('Under Maintenance', $need->status);
    }

    public function test_status_recomputes_on_update(): void
    {
        $need = AssetNeed::create([
            'asset_id'         => $this->asset->id,
            'asset_type'       => 'Generator',
            'requirement'      => 'Diesel Fuel',
            'unit'             => 'Liters',
            'maximum_capacity' => 100,
            'minimum_level'    => 20,
            'current_level'    => 50,
        ]);

        $this->assertEquals('Monitoring Only', $need->status);

        // Drop below minimum
        $need->update(['current_level' => 10]);
        $this->assertEquals('Requires Maintenance', $need->fresh()->status);

        // Deplete completely
        $need->update(['current_level' => 0]);
        $this->assertEquals('Under Maintenance', $need->fresh()->status);
    }

    // ── Replenish ───────────────────────────────────

    public function test_replenish_increases_level_and_recomputes_status(): void
    {
        $need = AssetNeed::create([
            'asset_id'         => $this->asset->id,
            'asset_type'       => 'Generator',
            'requirement'      => 'Diesel Fuel',
            'unit'             => 'Liters',
            'maximum_capacity' => 100,
            'minimum_level'    => 20,
            'current_level'    => 0,
        ]);

        $this->assertEquals('Under Maintenance', $need->status);

        $response = $this->actingAs($this->user)->post(route('asset-needs.replenish', $need), [
            'amount' => 80,
        ]);

        $response->assertRedirect(route('asset-needs.index'));
        $need->refresh();
        $this->assertEquals(80.00, (float) $need->current_level);
        $this->assertEquals('Monitoring Only', $need->status);
    }

    public function test_replenish_caps_at_maximum_capacity(): void
    {
        $need = AssetNeed::create([
            'asset_id'         => $this->asset->id,
            'asset_type'       => 'Generator',
            'requirement'      => 'Diesel Fuel',
            'unit'             => 'Liters',
            'maximum_capacity' => 100,
            'minimum_level'    => 20,
            'current_level'    => 80,
        ]);

        $need->replenish(50); // would exceed max
        $need->refresh();

        $this->assertEquals(100.00, (float) $need->current_level);
    }

    // ── Validation ──────────────────────────────────

    public function test_rejects_current_level_exceeding_maximum(): void
    {
        $response = $this->actingAs($this->user)->post(route('asset-needs.store'), [
            'asset_id'         => $this->asset->id,
            'requirement'      => 'Diesel Fuel',
            'unit'             => 'Liters',
            'maximum_capacity' => 100,
            'minimum_level'    => 20,
            'current_level'    => 150, // exceeds max
        ]);

        $response->assertSessionHasErrors('current_level');
    }

    public function test_rejects_negative_current_level(): void
    {
        $response = $this->actingAs($this->user)->post(route('asset-needs.store'), [
            'asset_id'         => $this->asset->id,
            'requirement'      => 'Diesel Fuel',
            'unit'             => 'Liters',
            'maximum_capacity' => 100,
            'minimum_level'    => 20,
            'current_level'    => -5,
        ]);

        $response->assertSessionHasErrors('current_level');
    }

    public function test_rejects_minimum_level_gte_maximum(): void
    {
        $response = $this->actingAs($this->user)->post(route('asset-needs.store'), [
            'asset_id'         => $this->asset->id,
            'requirement'      => 'Diesel Fuel',
            'unit'             => 'Liters',
            'maximum_capacity' => 100,
            'minimum_level'    => 100, // not less than max
            'current_level'    => 50,
        ]);

        $response->assertSessionHasErrors('minimum_level');
    }

    public function test_rejects_invalid_unit(): void
    {
        $response = $this->actingAs($this->user)->post(route('asset-needs.store'), [
            'asset_id'         => $this->asset->id,
            'requirement'      => 'Diesel Fuel',
            'unit'             => 'Gallons', // not in enum
            'maximum_capacity' => 100,
            'minimum_level'    => 20,
            'current_level'    => 50,
        ]);

        $response->assertSessionHasErrors('unit');
    }

    public function test_rejects_missing_requirement(): void
    {
        $response = $this->actingAs($this->user)->post(route('asset-needs.store'), [
            'asset_id'         => $this->asset->id,
            'requirement'      => '',
            'unit'             => 'Liters',
            'maximum_capacity' => 100,
            'minimum_level'    => 20,
            'current_level'    => 50,
        ]);

        $response->assertSessionHasErrors('requirement');
    }

    // ── Filters ─────────────────────────────────────

    public function test_filter_by_status(): void
    {
        $monitoring = AssetNeed::create([
            'asset_id'         => $this->asset->id,
            'asset_type'       => 'Generator',
            'requirement'      => 'Diesel Fuel',
            'unit'             => 'Liters',
            'maximum_capacity' => 100,
            'minimum_level'    => 20,
            'current_level'    => 50,
        ]);

        $underMaint = AssetNeed::create([
            'asset_id'         => $this->asset->id,
            'asset_type'       => 'Generator',
            'requirement'      => 'Engine Oil',
            'unit'             => 'Liters',
            'maximum_capacity' => 5,
            'minimum_level'    => 1,
            'current_level'    => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('asset-needs.index', ['status' => 'Under Maintenance']));

        $response->assertStatus(200);
        $response->assertViewHas('assetNeeds', function ($assetNeeds) use ($underMaint, $monitoring) {
            $ids = $assetNeeds->pluck('id')->toArray();
            return in_array($underMaint->id, $ids) && !in_array($monitoring->id, $ids);
        });
    }

    // ── Staff Read-Only Tests ───────────────────────

    /**
     * Requirement 1: Staff can view all existing asset need records and details.
     */
    public function test_staff_member_can_view_all_existing_asset_needs_and_details(): void
    {
        $need = AssetNeed::create([
            'asset_id'         => $this->asset->id,
            'asset_type'       => 'Generator',
            'requirement'      => 'High-Octane Generator Fuel',
            'unit'             => 'Liters',
            'maximum_capacity' => 120.00,
            'minimum_level'    => 25.00,
            'current_level'    => 60.00,
        ]);

        $response = $this->actingAs($this->staff)->get(route('asset-needs.index'));
        $response->assertOk();
        $response->assertSee('Asset Needs');
        $response->assertSee('High-Octane Generator Fuel');
        $response->assertSee('Test Generator');
        $response->assertSee('GEN-TEST');
        $response->assertSee('Generator');
        $response->assertSee('60');
        $response->assertSee('Liters');
        $response->assertSee('Capacity: 120 Liters (Min: 25)');
        $response->assertSee('Monitoring Only');

        // View Details modal and trigger must be present
        $response->assertSee('title="View Details"', false);
        $response->assertSee('openViewModal');
        $response->assertSee('id="view-asset-need-modal"', false);
    }

    /**
     * Requirement 2: Staff can search by requirement, asset type, or asset name, and use all three filters.
     */
    public function test_staff_member_can_search_and_use_all_three_filters(): void
    {
        // Create second asset & type for filter contrast
        $boatType = AssetType::create([
            'name'        => 'Rescue Boat',
            'category_id' => $this->asset->category_id,
        ]);
        $boatAsset = Asset::create([
            'code'        => 'BOAT-001',
            'name'        => 'Inflatable Zodiac',
            'category_id' => $this->asset->category_id,
            'type_id'     => $boatType->id,
            'location_id' => $this->asset->location_id,
            'condition'   => 'good',
            'status'      => 'available',
        ]);

        $genNeed = AssetNeed::create([
            'asset_id'         => $this->asset->id,
            'asset_type'       => 'Generator',
            'requirement'      => 'Synthetic Motor Oil',
            'unit'             => 'Liters',
            'maximum_capacity' => 10.00,
            'minimum_level'    => 2.00,
            'current_level'    => 8.00,
        ]);

        $boatNeed = AssetNeed::create([
            'asset_id'         => $boatAsset->id,
            'asset_type'       => 'Rescue Boat',
            'requirement'      => 'Marine Spark Plugs',
            'unit'             => 'Units',
            'maximum_capacity' => 20.00,
            'minimum_level'    => 5.00,
            'current_level'    => 2.00, // triggers Requires Maintenance
        ]);

        // 1. Search by requirement
        $resSearchReq = $this->actingAs($this->staff)->get(route('asset-needs.index', ['search' => 'Synthetic']));
        $resSearchReq->assertOk();
        $resSearchReq->assertSee('Synthetic Motor Oil');
        $resSearchReq->assertDontSee('Marine Spark Plugs');

        // 2. Search by asset type
        $resSearchType = $this->actingAs($this->staff)->get(route('asset-needs.index', ['search' => 'Rescue Boat']));
        $resSearchType->assertOk();
        $resSearchType->assertSee('Marine Spark Plugs');
        $resSearchType->assertDontSee('Synthetic Motor Oil');

        // 3. Search by asset name
        $resSearchName = $this->actingAs($this->staff)->get(route('asset-needs.index', ['search' => 'Zodiac']));
        $resSearchName->assertOk();
        $resSearchName->assertSee('Marine Spark Plugs');
        $resSearchName->assertDontSee('Synthetic Motor Oil');

        // 4. Filter by asset_type
        $resFilterType = $this->actingAs($this->staff)->get(route('asset-needs.index', ['asset_type' => 'Generator']));
        $resFilterType->assertOk();
        $resFilterType->assertSee('Synthetic Motor Oil');
        $resFilterType->assertDontSee('Marine Spark Plugs');

        // 5. Filter by unit
        $resFilterUnit = $this->actingAs($this->staff)->get(route('asset-needs.index', ['unit' => 'Units']));
        $resFilterUnit->assertOk();
        $resFilterUnit->assertSee('Marine Spark Plugs');
        $resFilterUnit->assertDontSee('Synthetic Motor Oil');

        // 6. Filter by status
        $resFilterStatus = $this->actingAs($this->staff)->get(route('asset-needs.index', ['status' => 'Requires Maintenance']));
        $resFilterStatus->assertOk();
        $resFilterStatus->assertSee('Marine Spark Plugs');
        $resFilterStatus->assertDontSee('Synthetic Motor Oil');
    }

    /**
     * Requirement 3: Target Asset details are visible but not clickable for Staff.
     */
    public function test_target_asset_details_are_visible_but_not_clickable_for_staff(): void
    {
        AssetNeed::create([
            'asset_id'         => $this->asset->id,
            'asset_type'       => 'Generator',
            'requirement'      => 'Fuel Filter Assembly',
            'unit'             => 'Units',
            'maximum_capacity' => 10.00,
            'minimum_level'    => 2.00,
            'current_level'    => 5.00,
        ]);

        $response = $this->actingAs($this->staff)->get(route('asset-needs.index'));
        $response->assertOk();

        // Target asset name, code, and type are visible
        $response->assertSee('Test Generator');
        $response->assertSee('GEN-TEST');
        $response->assertSee('Generator');

        // Non-clickable: MUST NOT link to asset registry
        $response->assertDontSee(route('assets.show', $this->asset));

        // For Administrator: MUST be clickable to asset registry
        $adminResponse = $this->actingAs($this->user)->get(route('asset-needs.index'));
        $adminResponse->assertOk();
        $adminResponse->assertSee(route('assets.show', $this->asset));
    }

    /**
     * Requirement 4: Add Asset Need, Edit, and Delete actions are unavailable to Staff in the interface.
     */
    public function test_interface_hides_action_buttons_for_staff(): void
    {
        AssetNeed::create([
            'asset_id'         => $this->asset->id,
            'asset_type'       => 'Generator',
            'requirement'      => 'Engine Air Filter',
            'unit'             => 'Units',
            'maximum_capacity' => 15.00,
            'minimum_level'    => 3.00,
            'current_level'    => 8.00,
        ]);

        $response = $this->actingAs($this->staff)->get(route('asset-needs.index'));
        $response->assertOk();

        // Add Asset Need button MUST be hidden
        $response->assertDontSee('openAddModal()');
        $response->assertDontSee('Add Asset Need</button>', false);
        $response->assertSee('Read-Only Staff Access');

        // Edit, Delete, and Replenish actions MUST be hidden
        $response->assertDontSee('openEditModal');
        $response->assertDontSee('title="Edit"', false);
        $response->assertDontSee('title="Delete"', false);
        $response->assertDontSee('onsubmit="return confirm(\'Delete this asset need?\');"', false);
        $response->assertDontSee('openReplenishModal');

        // Modifying modals MUST NOT exist in DOM
        $response->assertDontSee('id="add-asset-need-modal"', false);
        $response->assertDontSee('id="edit-asset-need-modal"', false);
        $response->assertDontSee('id="replenish-modal"', false);

        // Search bar, filters, and View Details modal MUST remain available
        $response->assertSee('name="search"', false);
        $response->assertSee('Asset Type');
        $response->assertSee('Unit');
        $response->assertSee('Status');
        $response->assertSee('title="View Details"', false);
        $response->assertSee('id="view-asset-need-modal"', false);
    }

    /**
     * Requirement 5: Restricted backend operations are rejected for Staff Members (403 Forbidden).
     */
    public function test_restricted_backend_operations_rejected_for_staff(): void
    {
        $need = AssetNeed::create([
            'asset_id'         => $this->asset->id,
            'asset_type'       => 'Generator',
            'requirement'      => 'Heavy Duty Battery',
            'unit'             => 'Units',
            'maximum_capacity' => 5.00,
            'minimum_level'    => 1.00,
            'current_level'    => 3.00,
        ]);

        // 1. GET /asset-needs/create -> 403
        $this->actingAs($this->staff)->get(route('asset-needs.create'))->assertForbidden();

        // 2. POST /asset-needs (store) -> 403
        $this->actingAs($this->staff)->post(route('asset-needs.store'), [
            'asset_id'         => $this->asset->id,
            'requirement'      => 'Unauthorized Need',
            'unit'             => 'Units',
            'maximum_capacity' => 10,
            'minimum_level'    => 2,
            'current_level'    => 5,
        ])->assertForbidden();

        // 3. GET /asset-needs/{id}/edit -> 403
        $this->actingAs($this->staff)->get(route('asset-needs.edit', $need))->assertForbidden();

        // 4. PUT /asset-needs/{id} (update) -> 403
        $this->actingAs($this->staff)->put(route('asset-needs.update', $need), [
            'asset_id'         => $this->asset->id,
            'requirement'      => 'Illegally Updated Need',
            'unit'             => 'Units',
            'maximum_capacity' => 10,
            'minimum_level'    => 2,
            'current_level'    => 5,
        ])->assertForbidden();

        // 5. POST /asset-needs/{id}/replenish -> 403
        $this->actingAs($this->staff)->post(route('asset-needs.replenish', $need), [
            'amount' => 2,
        ])->assertForbidden();

        // 6. DELETE /asset-needs/{id} -> 403
        $this->actingAs($this->staff)->delete(route('asset-needs.destroy', $need))->assertForbidden();

        // Database must remain unaltered
        $this->assertDatabaseHas('asset_needs', [
            'id'            => $need->id,
            'requirement'   => 'Heavy Duty Battery',
            'current_level' => 3.00,
        ]);
    }

    /**
     * Requirement 6: Administrator retains full functionality and action capabilities.
     */
    public function test_administrator_retains_full_action_capabilities(): void
    {
        $need = AssetNeed::create([
            'asset_id'         => $this->asset->id,
            'asset_type'       => 'Generator',
            'requirement'      => 'Admin Managed Coolant',
            'unit'             => 'Liters',
            'maximum_capacity' => 40.00,
            'minimum_level'    => 10.00,
            'current_level'    => 20.00,
        ]);

        // UI checks for Administrator
        $response = $this->actingAs($this->user)->get(route('asset-needs.index'));
        $response->assertOk();
        $response->assertSee('Add Asset Need');
        $response->assertSee('openAddModal()');
        $response->assertSee('openEditModal');
        $response->assertSee('title="Edit"', false);
        $response->assertSee('title="Delete"', false);
        $response->assertSee('id="add-asset-need-modal"', false);
        $response->assertSee('id="edit-asset-need-modal"', false);
        $response->assertSee('id="replenish-modal"', false);

        // Update
        $resUpdate = $this->actingAs($this->user)->put(route('asset-needs.update', $need), [
            'asset_id'         => $this->asset->id,
            'requirement'      => 'Admin Renamed Coolant',
            'unit'             => 'Liters',
            'maximum_capacity' => 50.00,
            'minimum_level'    => 10.00,
            'current_level'    => 25.00,
        ]);
        $resUpdate->assertRedirect(route('asset-needs.index'));
        $this->assertDatabaseHas('asset_needs', [
            'id'               => $need->id,
            'requirement'      => 'Admin Renamed Coolant',
            'maximum_capacity' => 50.00,
            'current_level'    => 25.00,
        ]);

        // Replenish
        $resReplenish = $this->actingAs($this->user)->post(route('asset-needs.replenish', $need), [
            'amount' => 10.00,
        ]);
        $resReplenish->assertRedirect(route('asset-needs.index'));
        $this->assertEquals(35.00, $need->fresh()->current_level);

        // Delete
        $resDelete = $this->actingAs($this->user)->delete(route('asset-needs.destroy', $need));
        $resDelete->assertRedirect(route('asset-needs.index'));
        $this->assertDatabaseMissing('asset_needs', ['id' => $need->id]);
    }
}

