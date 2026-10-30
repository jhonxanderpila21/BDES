<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssetReadOnlyStaffTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private AssetCategory $category;
    private AssetType $type;
    private Location $location;
    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'      => 'administrator',
            'is_active' => true,
        ]);

        $this->staff = User::factory()->create([
            'role'      => 'viewer',
            'is_active' => true,
        ]);

        $this->category = AssetCategory::create([
            'name'        => 'Emergency Rescue Equipment',
            'description' => 'Heavy duty rescue equipment for flood and disaster response',
            'status'      => 'active',
        ]);

        $this->type = AssetType::create([
            'name'                              => 'Rescue Inflatable Boat',
            'category_id'                       => $this->category->id,
            'serialized'                        => true,
            'borrowable'                        => true,
            'requires_maintenance'              => true,
            'requires_inspection'               => true,
            'has_accessories'                   => false,
            'has_attachments'                   => false,
            'requires_operational_requirements' => false,
        ]);

        $this->location = Location::create([
            'name'     => 'Central Disaster Depot Bay 1',
            'capacity' => 50,
        ]);

        $this->asset = Asset::create([
            'code'             => 'AST-BOAT01',
            'name'             => 'Zodiac Milpro Inflatable',
            'category_id'      => $this->category->id,
            'type_id'          => $this->type->id,
            'serial_number'    => 'SN-ZODIAC-8812',
            'manufacturer'     => 'Zodiac Milpro',
            'location_id'      => $this->location->id,
            'condition'        => 'excellent',
            'status'           => 'available',
            'acquisition_cost' => 125000.00,
            'purchase_date'    => '2025-01-15',
            'notes'            => 'Assigned to water rescue team',
        ]);
    }

    /**
     * Requirement 1: Staff Members can view all three tabs and asset details.
     */
    public function test_staff_member_can_view_all_three_tabs_and_asset_details(): void
    {
        // 1. Asset Inventory Tab
        $resAssets = $this->actingAs($this->staff)->get(route('assets.index', ['tab' => 'assets']));
        $resAssets->assertOk();
        $resAssets->assertSee('Asset Inventory');
        $resAssets->assertSee('AST-BOAT01');
        $resAssets->assertSee('Zodiac Milpro Inflatable');

        // 2. Categories Tab
        $resCategories = $this->actingAs($this->staff)->get(route('assets.index', ['tab' => 'categories']));
        $resCategories->assertOk();
        $resCategories->assertSee('Emergency Rescue Equipment');

        // 3. Asset Types Tab
        $resTypes = $this->actingAs($this->staff)->get(route('assets.index', ['tab' => 'types']));
        $resTypes->assertOk();
        $resTypes->assertSee('Rescue Inflatable Boat');

        // 4. Asset Details / Dossier
        $resShow = $this->actingAs($this->staff)->get(route('assets.show', $this->asset));
        $resShow->assertOk();
        $resShow->assertSee('Zodiac Milpro Inflatable');
        $resShow->assertSee('AST-BOAT01');
        $resShow->assertSee('SN-ZODIAC-8812');
        $resShow->assertSee('Central Disaster Depot Bay 1');
    }

    /**
     * Requirement 1 & 6: Staff Members can browse, search, filter, and sort inventory records.
     */
    public function test_staff_member_can_search_filter_and_sort_inventory(): void
    {
        // Search by keyword
        $resSearch = $this->actingAs($this->staff)->get(route('assets.index', [
            'tab'    => 'assets',
            'search' => 'Zodiac',
        ]));
        $resSearch->assertOk();
        $resSearch->assertSee('AST-BOAT01');

        // Filter by category
        $resFilter = $this->actingAs($this->staff)->get(route('assets.index', [
            'tab'         => 'assets',
            'category_id' => $this->category->id,
        ]));
        $resFilter->assertOk();
        $resFilter->assertSee('AST-BOAT01');

        // Sort by code
        $resSort = $this->actingAs($this->staff)->get(route('assets.index', [
            'tab'      => 'assets',
            'sort_by'  => 'code',
            'sort_dir' => 'asc',
        ]));
        $resSort->assertOk();
        $resSort->assertSee('AST-BOAT01');
    }

    /**
     * Requirement 4: Interface requirements - Hide Add Asset, Edit, and Delete action icons for Staff Members.
     */
    public function test_interface_hides_action_buttons_and_edit_delete_icons_for_staff(): void
    {
        $response = $this->actingAs($this->staff)->get(route('assets.index', ['tab' => 'assets']));
        $response->assertOk();

        // Add Asset button should NOT be shown
        $response->assertDontSee('openAddAssetModal()');
        $response->assertDontSee('Add Asset</button>', false);

        // Edit and Delete icons should NOT be shown for assets
        $response->assertDontSee('openEditAssetModal');
        $response->assertDontSee('title="Edit Asset"', false);
        $response->assertDontSee('title="Delete"', false);

        // Quick View button SHOULD be shown
        $response->assertSee('title="Quick View"', false);

        // Check Categories tab
        $catResponse = $this->actingAs($this->staff)->get(route('assets.index', ['tab' => 'categories']));
        $catResponse->assertOk();
        $catResponse->assertDontSee('openAddCategoryModal()');
        $catResponse->assertDontSee('openEditCategoryModal');
        $catResponse->assertDontSee('title="Edit Category"', false);
        $catResponse->assertDontSee('title="Delete Category"', false);
        $catResponse->assertSee('Read-only');

        // Check Types tab
        $typeResponse = $this->actingAs($this->staff)->get(route('assets.index', ['tab' => 'types']));
        $typeResponse->assertOk();
        $typeResponse->assertDontSee('openAddTypeModal()');
        $typeResponse->assertDontSee('openEditTypeModal');
        $typeResponse->assertDontSee('title="Edit Type"', false);
        $typeResponse->assertDontSee('title="Delete Type"', false);
        $typeResponse->assertSee('Read-only');

        // Check modifying modals are not in DOM
        $response->assertDontSee('id="edit-asset-modal"', false);
        $response->assertDontSee('id="retire-asset-modal"', false);
        $response->assertDontSee('id="add-category-modal"', false);
        $response->assertDontSee('id="edit-category-modal"', false);
        $response->assertDontSee('id="add-type-modal"', false);
        $response->assertDontSee('id="edit-type-modal"', false);

        // View details modal MUST be present
        $response->assertSee('id="view-asset-modal"', false);
    }

    /**
     * Requirement 4 & 5: Administrators retain existing action buttons and permissions in the interface.
     */
    public function test_interface_retains_action_buttons_for_administrators(): void
    {
        // Assets Tab
        $response = $this->actingAs($this->admin)->get(route('assets.index', ['tab' => 'assets']));
        $response->assertOk();
        $response->assertSee('Add Asset');
        $response->assertSee('openAddAssetModal()');
        $response->assertSee('openEditAssetModal');
        $response->assertSee('title="Quick View"', false);
        $response->assertSee('title="Edit Asset"', false);
        $response->assertSee('title="Delete"', false);
        $response->assertSee('id="edit-asset-modal"', false);

        // Categories Tab
        $catResponse = $this->actingAs($this->admin)->get(route('assets.index', ['tab' => 'categories']));
        $catResponse->assertOk();
        $catResponse->assertSee('Add Category');
        $catResponse->assertSee('openAddCategoryModal()');
        $catResponse->assertSee('title="Edit Category"', false);
        $catResponse->assertSee('title="Delete Category"', false);

        // Types Tab
        $typeResponse = $this->actingAs($this->admin)->get(route('assets.index', ['tab' => 'types']));
        $typeResponse->assertOk();
        $typeResponse->assertSee('Add Asset Type');
        $typeResponse->assertSee('openAddTypeModal()');
        $typeResponse->assertSee('title="Edit Type"', false);
        $typeResponse->assertSee('title="Delete Type"', false);
    }

    /**
     * Requirement 5: Direct server-side requests to restricted Asset operations are rejected for Staff Members (403).
     */
    public function test_direct_requests_to_asset_write_operations_rejected_for_staff(): void
    {
        // 1. GET /assets/create
        $resCreate = $this->actingAs($this->staff)->get(route('assets.create'));
        $resCreate->assertForbidden();

        // 2. POST /assets (store)
        $resStore = $this->actingAs($this->staff)->post(route('assets.store'), [
            'name'             => 'Unauthorized Stored Asset',
            'category_id'      => $this->category->id,
            'type_id'          => $this->type->id,
            'location_id'      => $this->location->id,
            'condition'        => 'good',
            'status'           => 'available',
            'acquisition_cost' => 5000,
        ]);
        $resStore->assertForbidden();

        // 3. GET /assets/{asset}/edit
        $resEdit = $this->actingAs($this->staff)->get(route('assets.edit', $this->asset));
        $resEdit->assertForbidden();

        // 4. PUT /assets/{asset} (update info, change condition/status)
        $resUpdate = $this->actingAs($this->staff)->put(route('assets.update', $this->asset), [
            'code'             => $this->asset->code,
            'name'             => 'Illegally Modified Name',
            'category_id'      => $this->category->id,
            'location_id'      => $this->location->id,
            'condition'        => 'poor',
            'status'           => 'out_of_service',
            'acquisition_cost' => 125000.00,
        ]);
        $resUpdate->assertForbidden();

        // Confirm database was NOT modified
        $this->assertDatabaseHas('assets', [
            'id'        => $this->asset->id,
            'name'      => 'Zodiac Milpro Inflatable',
            'condition' => 'excellent',
            'status'    => 'available',
        ]);

        // 5. POST /assets/{asset}/retire
        $resRetire = $this->actingAs($this->staff)->post(route('assets.retire', $this->asset), [
            'retirement_reason' => 'Unauthorized retirement attempt',
        ]);
        $resRetire->assertForbidden();

        // 6. DELETE /assets/{asset}
        $resDestroy = $this->actingAs($this->staff)->delete(route('assets.destroy', $this->asset));
        $resDestroy->assertForbidden();

        // 7. POST /assets/{asset}/documents
        Storage::fake('public');
        $file = UploadedFile::fake()->create('manual.pdf', 500, 'application/pdf');
        $resDoc = $this->actingAs($this->staff)->post(route('assets.documents.store', $this->asset), [
            'title'         => 'Test Document',
            'document_type' => 'manual',
            'document_file' => $file,
        ]);
        $resDoc->assertForbidden();
    }

    /**
     * Requirement 2 & 5: Direct server-side requests to Category operations rejected for Staff Members (403).
     */
    public function test_direct_requests_to_category_operations_rejected_for_staff(): void
    {
        // 1. GET /asset-categories/create
        $resCreate = $this->actingAs($this->staff)->get(route('asset-categories.create'));
        $resCreate->assertForbidden();

        // 2. POST /asset-categories
        $resStore = $this->actingAs($this->staff)->post(route('asset-categories.store'), [
            'name'        => 'Unauthorized Category',
            'description' => 'Should fail',
            'status'      => 'active',
        ]);
        $resStore->assertForbidden();
        $this->assertDatabaseMissing('asset_categories', ['name' => 'Unauthorized Category']);

        // 3. GET /asset-categories/{category}/edit
        $resEdit = $this->actingAs($this->staff)->get(route('asset-categories.edit', $this->category));
        $resEdit->assertForbidden();

        // 4. PUT /asset-categories/{category}
        $resUpdate = $this->actingAs($this->staff)->put(route('asset-categories.update', $this->category), [
            'name'   => 'Hacked Category Name',
            'status' => 'inactive',
        ]);
        $resUpdate->assertForbidden();
        $this->assertDatabaseHas('asset_categories', [
            'id'   => $this->category->id,
            'name' => 'Emergency Rescue Equipment',
        ]);

        // 5. DELETE /asset-categories/{category}
        $resDelete = $this->actingAs($this->staff)->delete(route('asset-categories.destroy', $this->category));
        $resDelete->assertForbidden();
        $this->assertDatabaseHas('asset_categories', ['id' => $this->category->id]);
    }

    /**
     * Requirement 3 & 5: Direct server-side requests to Asset Type operations rejected for Staff Members (403).
     */
    public function test_direct_requests_to_asset_type_operations_rejected_for_staff(): void
    {
        // 1. GET /asset-types/create
        $resCreate = $this->actingAs($this->staff)->get(route('asset-types.create'));
        $resCreate->assertForbidden();

        // 2. POST /asset-types
        $resStore = $this->actingAs($this->staff)->post(route('asset-types.store'), [
            'name'        => 'Unauthorized Type',
            'category_id' => $this->category->id,
        ]);
        $resStore->assertForbidden();
        $this->assertDatabaseMissing('asset_types', ['name' => 'Unauthorized Type']);

        // 3. GET /asset-types/{type}/edit
        $resEdit = $this->actingAs($this->staff)->get(route('asset-types.edit', $this->type));
        $resEdit->assertForbidden();

        // 4. PUT /asset-types/{type}
        $resUpdate = $this->actingAs($this->staff)->put(route('asset-types.update', $this->type), [
            'name'        => 'Hacked Type Name',
            'category_id' => $this->category->id,
        ]);
        $resUpdate->assertForbidden();
        $this->assertDatabaseHas('asset_types', [
            'id'   => $this->type->id,
            'name' => 'Rescue Inflatable Boat',
        ]);

        // 5. DELETE /asset-types/{type}
        $resDelete = $this->actingAs($this->staff)->delete(route('asset-types.destroy', $this->type));
        $resDelete->assertForbidden();
        $this->assertDatabaseHas('asset_types', ['id' => $this->type->id]);
    }

    /**
     * Requirement 5: Administrators retain full create, update, and delete permissions.
     */
    public function test_administrator_retains_full_operational_permissions(): void
    {
        // Admin creates Category
        $resCat = $this->actingAs($this->admin)->post(route('asset-categories.store'), [
            'name'        => 'Logistics Vehicles',
            'description' => 'Disaster logistics transport',
            'status'      => 'active',
        ]);
        $resCat->assertRedirect();
        $newCategory = AssetCategory::where('name', 'Logistics Vehicles')->first();
        $this->assertNotNull($newCategory);

        // Admin updates Category
        $resCatUpdate = $this->actingAs($this->admin)->put(route('asset-categories.update', $newCategory), [
            'name'        => 'Updated Logistics Vehicles',
            'description' => 'Updated desc',
            'status'      => 'active',
        ]);
        $resCatUpdate->assertRedirect();
        $this->assertDatabaseHas('asset_categories', ['name' => 'Updated Logistics Vehicles']);

        // Admin creates Type
        $resType = $this->actingAs($this->admin)->post(route('asset-types.store'), [
            'name'        => 'Utility Truck',
            'category_id' => $newCategory->id,
            'borrowable'  => true,
        ]);
        $resType->assertRedirect();
        $newType = AssetType::where('name', 'Utility Truck')->first();
        $this->assertNotNull($newType);

        // Admin updates Type
        $resTypeUpdate = $this->actingAs($this->admin)->put(route('asset-types.update', $newType), [
            'name'        => 'Heavy Utility Truck',
            'category_id' => $newCategory->id,
            'borrowable'  => true,
        ]);
        $resTypeUpdate->assertRedirect();
        $this->assertDatabaseHas('asset_types', ['name' => 'Heavy Utility Truck']);

        // Admin registers Asset
        $resAsset = $this->actingAs($this->admin)->post(route('assets.store'), [
            'name'             => 'Isuzu Elf Logistics Truck',
            'category_id'      => $newCategory->id,
            'type_id'          => $newType->id,
            'serial_number'    => 'ENG-998877',
            'manufacturer'     => 'Isuzu',
            'location_id'      => $this->location->id,
            'condition'        => 'excellent',
            'status'           => 'available',
            'acquisition_cost' => 850000.00,
            'purchase_date'    => '2025-03-01',
        ]);
        $resAsset->assertRedirect();
        $newAsset = Asset::where('name', 'Isuzu Elf Logistics Truck')->first();
        $this->assertNotNull($newAsset);

        // Admin updates Asset
        $resAssetUpdate = $this->actingAs($this->admin)->put(route('assets.update', $newAsset), [
            'code'             => $newAsset->code,
            'name'             => 'Isuzu Elf Logistics Truck (Updated)',
            'category_id'      => $newCategory->id,
            'type_id'          => $newType->id,
            'location_id'      => $this->location->id,
            'condition'        => 'good',
            'status'           => 'available',
            'acquisition_cost' => 850000.00,
        ]);
        $resAssetUpdate->assertRedirect();
        $this->assertDatabaseHas('assets', ['name' => 'Isuzu Elf Logistics Truck (Updated)']);

        // Admin deletes Asset
        $resAssetDelete = $this->actingAs($this->admin)->delete(route('assets.destroy', $newAsset));
        $resAssetDelete->assertRedirect();

        // Admin deletes Type
        $resTypeDelete = $this->actingAs($this->admin)->delete(route('asset-types.destroy', $newType));
        $resTypeDelete->assertRedirect();
        $this->assertDatabaseMissing('asset_types', ['id' => $newType->id]);

        // Admin deletes Category
        $resCatDelete = $this->actingAs($this->admin)->delete(route('asset-categories.destroy', $newCategory));
        $resCatDelete->assertRedirect();
        $this->assertSoftDeleted('asset_categories', ['id' => $newCategory->id]);
    }
}
