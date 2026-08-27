<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetAccessory;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetAccessoryTest extends TestCase
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

    public function test_index_displays_accessories_and_budget_summary(): void
    {
        AssetAccessory::create([
            'asset_id'       => $this->asset->id,
            'asset_type'     => 'Generator',
            'accessory_name' => 'Fuel Can',
            'quantity'       => 2,
            'unit_cost'      => 300.00,
        ]);

        $response = $this->actingAs($this->user)->get(route('asset-accessories.index'));

        $response->assertStatus(200);
        $response->assertSee('Fuel Can');
        $response->assertSee('Generator');
        $response->assertSee('600.00'); // 2 * 300 = 600 total budget
    }

    public function test_store_creates_accessory_with_auto_status(): void
    {
        $response = $this->actingAs($this->user)->post(route('asset-accessories.store'), [
            'asset_id'       => $this->asset->id,
            'accessory_name' => 'Spare Key',
            'quantity'       => 1,
            'unit_cost'      => 50.00,
        ]);

        $response->assertRedirect(route('asset-accessories.index'));
        $this->assertDatabaseHas('asset_accessories', [
            'accessory_name' => 'Spare Key',
            'asset_type'     => 'Generator',
            'quantity'       => 1,
            'status'         => 'Available',
        ]);
    }

    public function test_update_modifies_accessory_and_recomputes_status(): void
    {
        $accessory = AssetAccessory::create([
            'asset_id'       => $this->asset->id,
            'asset_type'     => 'Generator',
            'accessory_name' => 'Fuel Can',
            'quantity'       => 2,
            'unit_cost'      => 300.00,
        ]);

        $this->assertEquals('Available', $accessory->status);

        $response = $this->actingAs($this->user)->put(route('asset-accessories.update', $accessory), [
            'asset_id'       => $this->asset->id,
            'accessory_name' => 'Fuel Can',
            'quantity'       => 0, // Deplete stock
            'unit_cost'      => 300.00,
        ]);

        $response->assertRedirect(route('asset-accessories.index'));
        
        $accessory->refresh();
        $this->assertEquals(0, $accessory->quantity);
        $this->assertEquals('Not Available', $accessory->status);
    }

    public function test_destroy_deletes_accessory(): void
    {
        $accessory = AssetAccessory::create([
            'asset_id'       => $this->asset->id,
            'asset_type'     => 'Generator',
            'accessory_name' => 'Fuel Can',
            'quantity'       => 2,
            'unit_cost'      => 300.00,
        ]);

        $response = $this->actingAs($this->user)->delete(route('asset-accessories.destroy', $accessory));

        $response->assertRedirect(route('asset-accessories.index'));
        $this->assertDatabaseMissing('asset_accessories', ['id' => $accessory->id]);
    }

    public function test_adjust_quantity_add(): void
    {
        $accessory = AssetAccessory::create([
            'asset_id'       => $this->asset->id,
            'asset_type'     => 'Generator',
            'accessory_name' => 'Fuel Can',
            'quantity'       => 2,
            'unit_cost'      => 300.00,
        ]);

        $response = $this->actingAs($this->user)->post(route('asset-accessories.adjust-quantity', $accessory), [
            'action' => 'add',
            'amount' => 3,
        ]);

        $response->assertRedirect(route('asset-accessories.index'));
        $accessory->refresh();
        $this->assertEquals(5, $accessory->quantity);
        $this->assertEquals('Available', $accessory->status);
    }

    public function test_adjust_quantity_subtract(): void
    {
        $accessory = AssetAccessory::create([
            'asset_id'       => $this->asset->id,
            'asset_type'     => 'Generator',
            'accessory_name' => 'Fuel Can',
            'quantity'       => 2,
            'unit_cost'      => 300.00,
        ]);

        $response = $this->actingAs($this->user)->post(route('asset-accessories.adjust-quantity', $accessory), [
            'action' => 'subtract',
            'amount' => 2,
        ]);

        $response->assertRedirect(route('asset-accessories.index'));
        $accessory->refresh();
        $this->assertEquals(0, $accessory->quantity);
        $this->assertEquals('Not Available', $accessory->status);
    }

    public function test_adjust_quantity_subtract_fails_if_insufficient(): void
    {
        $accessory = AssetAccessory::create([
            'asset_id'       => $this->asset->id,
            'asset_type'     => 'Generator',
            'accessory_name' => 'Fuel Can',
            'quantity'       => 2,
            'unit_cost'      => 300.00,
        ]);

        $response = $this->actingAs($this->user)->post(route('asset-accessories.adjust-quantity', $accessory), [
            'action' => 'subtract',
            'amount' => 5, // more than current
        ]);

        $response->assertSessionHasErrors('amount');
        $accessory->refresh();
        $this->assertEquals(2, $accessory->quantity); // No change
    }

    public function test_validation_rejects_negative_quantity_and_cost(): void
    {
        $response = $this->actingAs($this->user)->post(route('asset-accessories.store'), [
            'asset_id'       => $this->asset->id,
            'accessory_name' => 'Spare Key',
            'quantity'       => -1,
            'unit_cost'      => -50.00,
        ]);

        $response->assertSessionHasErrors(['quantity', 'unit_cost']);
    }
}
