<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnifiedAssetsTest extends TestCase
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
            'name'        => 'Communication Gear',
            'description' => 'Radios, satellite phones, and base stations',
            'status'      => 'active',
        ]);

        $this->type = AssetType::create([
            'name'                              => 'VHF Handheld Radio',
            'category_id'                       => $this->category->id,
            'serialized'                        => true,
            'borrowable'                        => true,
            'requires_maintenance'              => true,
            'requires_inspection'               => false,
            'has_accessories'                   => true,
            'has_attachments'                   => false,
            'requires_operational_requirements' => false,
        ]);

        $this->location = Location::create([
            'name'     => 'HQ Warehouse Shelf 3',
            'capacity' => 100,
        ]);

        $this->asset = Asset::create([
            'code'          => 'AST-RAD001',
            'name'          => 'Motorola DP4801e',
            'category_id'   => $this->category->id,
            'type_id'       => $this->type->id,
            'serial_number' => 'MOT-984214',
            'manufacturer'  => 'Motorola Solutions',
            'location_id'   => $this->location->id,
            'condition'     => 'excellent',
            'status'        => 'available',
            'notes'         => 'Assigned to Bravo squad',
        ]);
    }

    public function test_assets_index_displays_unified_tabs_and_metrics(): void
    {
        $response = $this->actingAs($this->user)->get(route('assets.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.assets.index');
        $response->assertViewHas('activeTab', 'assets');
        $response->assertViewHas('metrics');
        $response->assertSee('Asset Inventory');
        $response->assertSee('Categories');
        $response->assertSee('Asset Types');
        $response->assertSee('AST-RAD001');
        $response->assertSee('Motorola DP4801e');
    }

    public function test_assets_index_switches_tabs_correctly(): void
    {
        // Categories Tab
        $catResponse = $this->actingAs($this->user)->get(route('assets.index', ['tab' => 'categories']));
        $catResponse->assertStatus(200);
        $catResponse->assertViewHas('activeTab', 'categories');
        $catResponse->assertSee('Communication Gear');
        $catResponse->assertSee('Radios, satellite phones, and base stations');

        // Types Tab
        $typeResponse = $this->actingAs($this->user)->get(route('assets.index', ['tab' => 'types']));
        $typeResponse->assertStatus(200);
        $typeResponse->assertViewHas('activeTab', 'types');
        $typeResponse->assertSee('VHF Handheld Radio');
    }

    public function test_asset_category_index_delegates_to_unified_hub(): void
    {
        $response = $this->actingAs($this->user)->get(route('asset-categories.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.assets.index');
        $response->assertViewHas('activeTab', 'categories');
        $response->assertSee('Communication Gear');
    }

    public function test_asset_type_index_delegates_to_unified_hub(): void
    {
        $response = $this->actingAs($this->user)->get(route('asset-types.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.assets.index');
        $response->assertViewHas('activeTab', 'types');
        $response->assertSee('VHF Handheld Radio');
    }

    public function test_store_asset_creates_new_asset_and_redirects_to_assets_tab(): void
    {
        $payload = [
            'name'          => 'Kenwood Transceiver',
            'category_id'   => $this->category->id,
            'type_id'       => $this->type->id,
            'serial_number' => 'KEN-12345',
            'manufacturer'  => 'Kenwood',
            'location_id'   => $this->location->id,
            'condition'     => 'good',
            'status'        => 'available',
            'notes'         => 'Emergency backup unit',
        ];

        $response = $this->actingAs($this->user)->post(route('assets.store'), $payload);

        $response->assertRedirect(route('assets.index', ['tab' => 'assets']));
        $this->assertDatabaseHas('assets', [
            'name'          => 'Kenwood Transceiver',
            'serial_number' => 'KEN-12345',
            'condition'     => 'good',
        ]);
    }

    public function test_update_asset_updates_asset_and_redirects(): void
    {
        $payload = [
            'code'          => $this->asset->code,
            'name'          => 'Motorola DP4801e Updated',
            'category_id'   => $this->category->id,
            'type_id'       => $this->type->id,
            'serial_number' => $this->asset->serial_number,
            'manufacturer'  => 'Motorola Solutions Inc.',
            'location_id'   => $this->location->id,
            'condition'     => 'fair',
            'status'        => 'under_maintenance',
            'notes'         => 'Battery replaced',
        ];

        $response = $this->actingAs($this->user)->put(route('assets.update', $this->asset), $payload);

        $response->assertRedirect(route('assets.index', ['tab' => 'assets']));
        $this->assertDatabaseHas('assets', [
            'id'        => $this->asset->id,
            'name'      => 'Motorola DP4801e Updated',
            'condition' => 'fair',
            'status'    => 'under_maintenance',
        ]);
    }

    public function test_destroy_asset_deletes_asset(): void
    {
        $response = $this->actingAs($this->user)->delete(route('assets.destroy', $this->asset));

        $response->assertRedirect(route('assets.index', ['tab' => 'assets']));
        $this->assertDatabaseMissing('assets', [
            'id' => $this->asset->id,
        ]);
    }

    public function test_category_crud_redirects_to_categories_tab(): void
    {
        // Store
        $storeResponse = $this->actingAs($this->user)->post(route('asset-categories.store'), [
            'name'        => 'Rescue Watercraft',
            'description' => 'Boats and jet skis',
            'status'      => 'active',
        ]);
        $storeResponse->assertRedirect(route('assets.index', ['tab' => 'categories']));
        $this->assertDatabaseHas('asset_categories', ['name' => 'Rescue Watercraft']);

        $category = AssetCategory::where('name', 'Rescue Watercraft')->first();

        // Update
        $updateResponse = $this->actingAs($this->user)->put(route('asset-categories.update', $category), [
            'name'        => 'Rescue Watercraft Updated',
            'description' => 'Updated boats description',
            'status'      => 'inactive',
        ]);
        $updateResponse->assertRedirect(route('assets.index', ['tab' => 'categories']));
        $this->assertDatabaseHas('asset_categories', [
            'id'     => $category->id,
            'name'   => 'Rescue Watercraft Updated',
            'status' => 'inactive',
        ]);

        // Destroy
        $destroyResponse = $this->actingAs($this->user)->delete(route('asset-categories.destroy', $category));
        $destroyResponse->assertRedirect(route('assets.index', ['tab' => 'categories']));
        $this->assertSoftDeleted($category);
    }

    public function test_type_crud_redirects_to_types_tab(): void
    {
        // Store
        $storeResponse = $this->actingAs($this->user)->post(route('asset-types.store'), [
            'name'                              => 'Inflatable Boat',
            'category_id'                       => $this->category->id,
            'serialized'                        => 1,
            'borrowable'                        => 1,
            'requires_maintenance'              => 1,
            'requires_inspection'               => 1,
            'has_accessories'                   => 0,
            'has_attachments'                   => 0,
            'requires_operational_requirements' => 0,
        ]);
        $storeResponse->assertRedirect(route('assets.index', ['tab' => 'types']));
        $this->assertDatabaseHas('asset_types', [
            'name'                 => 'Inflatable Boat',
            'requires_maintenance' => 1,
        ]);

        $type = AssetType::where('name', 'Inflatable Boat')->first();

        // Update
        $updateResponse = $this->actingAs($this->user)->put(route('asset-types.update', $type), [
            'name'                              => 'Inflatable Rescue Boat',
            'category_id'                       => $this->category->id,
            'serialized'                        => 1,
            'borrowable'                        => 1,
            'requires_maintenance'              => 0,
            'requires_inspection'               => 1,
            'has_accessories'                   => 1,
            'has_attachments'                   => 0,
            'requires_operational_requirements' => 0,
        ]);
        $updateResponse->assertRedirect(route('assets.index', ['tab' => 'types']));
        $this->assertDatabaseHas('asset_types', [
            'id'                   => $type->id,
            'name'                 => 'Inflatable Rescue Boat',
            'requires_maintenance' => 0,
            'has_accessories'      => 1,
        ]);

        // Destroy
        $destroyResponse = $this->actingAs($this->user)->delete(route('asset-types.destroy', $type));
        $destroyResponse->assertRedirect(route('assets.index', ['tab' => 'types']));
        $this->assertDatabaseMissing('asset_types', ['id' => $type->id]);
    }
}
