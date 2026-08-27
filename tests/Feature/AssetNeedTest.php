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
    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role'      => 'administrator',
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
}

