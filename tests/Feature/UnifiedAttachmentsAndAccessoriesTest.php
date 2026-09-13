<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetAccessory;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\Attachment;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnifiedAttachmentsAndAccessoriesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private AssetCategory $category;
    private AssetType $type;
    private Location $location;
    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role'      => 'administrator',
            'is_active' => true,
        ]);

        $this->category = AssetCategory::create([
            'name'        => 'Firefighting Equipment',
            'description' => 'Fire safety and suppression tools',
            'status'      => 'active',
        ]);

        $this->type = AssetType::create([
            'name'        => 'Fire Pump Engine',
            'category_id' => $this->category->id,
        ]);

        $this->location = Location::create([
            'name'     => 'Central Station Bay 1',
            'capacity' => 20,
        ]);

        $this->asset = Asset::create([
            'code'        => 'AST-PMP01',
            'name'        => 'Portable High-Pressure Pump',
            'category_id' => $this->category->id,
            'type_id'     => $this->type->id,
            'location_id' => $this->location->id,
            'condition'   => 'good',
            'status'      => 'available',
        ]);
    }

    public function test_attachments_index_displays_unified_tabs_and_metrics(): void
    {
        Attachment::create([
            'name'          => 'High-Pressure Fire Hose 30m',
            'type'          => 'Hose',
            'asset_type_id' => $this->type->id,
            'asset_id'      => $this->asset->id,
            'quantity'      => 4,
            'condition'     => 'excellent',
        ]);

        AssetAccessory::create([
            'asset_id'       => $this->asset->id,
            'asset_type'     => 'Fire Pump Engine',
            'accessory_name' => 'Spare Spark Plug Kit',
            'quantity'       => 10,
            'unit_cost'      => 150.00,
        ]);

        $response = $this->actingAs($this->user)->get(route('attachments.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.attachments.index');
        $response->assertViewHas('activeTab', 'attachments');
        $response->assertViewHas('metrics');
        $response->assertSee('Attachments and Accessories');
        $response->assertSee('Asset Attachments');
        $response->assertSee('Asset Accessories');
        $response->assertSee('High-Pressure Fire Hose 30m');
    }

    public function test_tab_switching_between_attachments_and_accessories(): void
    {
        Attachment::create([
            'name'          => 'Heavy Duty Suction Hose',
            'type'          => 'Hose',
            'asset_type_id' => $this->type->id,
            'asset_id'      => $this->asset->id,
            'quantity'      => 2,
            'condition'     => 'good',
        ]);

        AssetAccessory::create([
            'asset_id'       => $this->asset->id,
            'asset_type'     => 'Fire Pump Engine',
            'accessory_name' => 'Oil Filter Replacement',
            'quantity'       => 5,
            'unit_cost'      => 250.00,
        ]);

        // Attachments tab
        $attResponse = $this->actingAs($this->user)->get(route('attachments.index', ['tab' => 'attachments']));
        $attResponse->assertStatus(200);
        $attResponse->assertViewHas('activeTab', 'attachments');
        $attResponse->assertSee('Heavy Duty Suction Hose');

        // Accessories tab
        $accResponse = $this->actingAs($this->user)->get(route('attachments.index', ['tab' => 'accessories']));
        $accResponse->assertStatus(200);
        $accResponse->assertViewHas('activeTab', 'accessories');
        $accResponse->assertSee('Oil Filter Replacement');
        $accResponse->assertSee('1,250.00'); // 5 * 250 = 1250 total value
    }

    public function test_asset_accessories_route_delegates_to_unified_hub_with_accessories_tab(): void
    {
        AssetAccessory::create([
            'asset_id'       => $this->asset->id,
            'asset_type'     => 'Fire Pump Engine',
            'accessory_name' => 'Carburetor Gasket',
            'quantity'       => 3,
            'unit_cost'      => 100.00,
        ]);

        $response = $this->actingAs($this->user)->get(route('asset-accessories.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.attachments.index');
        $response->assertViewHas('activeTab', 'accessories');
        $response->assertSee('Carburetor Gasket');
    }

    public function test_attachment_crud_operations(): void
    {
        // 1. Create
        $storeResponse = $this->actingAs($this->user)->post(route('attachments.store'), [
            'name'          => 'Brass Nozzle 2-inch',
            'type'          => 'Nozzle',
            'asset_type_id' => $this->type->id,
            'asset_id'      => $this->asset->id,
            'quantity'      => 3,
            'condition'     => 'excellent',
        ]);

        $storeResponse->assertRedirect(route('attachments.index', ['tab' => 'attachments']));
        $this->assertDatabaseHas('attachments', [
            'name'      => 'Brass Nozzle 2-inch',
            'type'      => 'Nozzle',
            'quantity'  => 3,
            'condition' => 'excellent',
        ]);

        $attachment = Attachment::where('name', 'Brass Nozzle 2-inch')->first();

        // 2. Update
        $updateResponse = $this->actingAs($this->user)->put(route('attachments.update', $attachment), [
            'name'          => 'Brass Nozzle 2-inch (Heavy Duty)',
            'type'          => 'Nozzle',
            'asset_type_id' => $this->type->id,
            'asset_id'      => $this->asset->id,
            'quantity'      => 5,
            'condition'     => 'good',
        ]);

        $updateResponse->assertRedirect(route('attachments.index', ['tab' => 'attachments']));
        $this->assertDatabaseHas('attachments', [
            'id'        => $attachment->id,
            'name'      => 'Brass Nozzle 2-inch (Heavy Duty)',
            'quantity'  => 5,
            'condition' => 'good',
        ]);

        // 3. Delete
        $deleteResponse = $this->actingAs($this->user)->delete(route('attachments.destroy', $attachment));
        $deleteResponse->assertRedirect(route('attachments.index', ['tab' => 'attachments']));
        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
    }

    public function test_accessory_crud_and_adjust_quantity_in_unified_hub(): void
    {
        // 1. Store accessory
        $storeResponse = $this->actingAs($this->user)->post(route('asset-accessories.store'), [
            'asset_id'       => $this->asset->id,
            'accessory_name' => 'Water Seal O-Ring',
            'quantity'       => 8,
            'unit_cost'      => 25.00,
        ]);

        $storeResponse->assertRedirect(route('asset-accessories.index'));
        $this->assertDatabaseHas('asset_accessories', [
            'accessory_name' => 'Water Seal O-Ring',
            'quantity'       => 8,
            'status'         => 'Available',
        ]);

        $accessory = AssetAccessory::where('accessory_name', 'Water Seal O-Ring')->first();

        // 2. Adjust Quantity (Restock)
        $restockResponse = $this->actingAs($this->user)->post(route('asset-accessories.adjust-quantity', $accessory), [
            'action' => 'add',
            'amount' => 4,
        ]);
        $restockResponse->assertRedirect(route('asset-accessories.index'));
        $accessory->refresh();
        $this->assertEquals(12, $accessory->quantity);

        // 3. Adjust Quantity (Consume)
        $consumeResponse = $this->actingAs($this->user)->post(route('asset-accessories.adjust-quantity', $accessory), [
            'action' => 'subtract',
            'amount' => 12,
        ]);
        $consumeResponse->assertRedirect(route('asset-accessories.index'));
        $accessory->refresh();
        $this->assertEquals(0, $accessory->quantity);
        $this->assertEquals('Not Available', $accessory->status);

        // 4. Update
        $updateResponse = $this->actingAs($this->user)->put(route('asset-accessories.update', $accessory), [
            'asset_id'       => $this->asset->id,
            'accessory_name' => 'Water Seal O-Ring Premium',
            'quantity'       => 15,
            'unit_cost'      => 30.00,
        ]);
        $updateResponse->assertRedirect(route('asset-accessories.index'));
        $accessory->refresh();
        $this->assertEquals('Water Seal O-Ring Premium', $accessory->accessory_name);
        $this->assertEquals(15, $accessory->quantity);
        $this->assertEquals('Available', $accessory->status);

        // 5. Delete
        $deleteResponse = $this->actingAs($this->user)->delete(route('asset-accessories.destroy', $accessory));
        $deleteResponse->assertRedirect(route('asset-accessories.index'));
        $this->assertDatabaseMissing('asset_accessories', ['id' => $accessory->id]);
    }
}

