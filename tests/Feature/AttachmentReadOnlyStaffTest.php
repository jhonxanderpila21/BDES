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

class AttachmentReadOnlyStaffTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private AssetCategory $category;
    private AssetType $type;
    private Location $location;
    private Asset $asset;
    private Attachment $attachment;
    private AssetAccessory $accessory;

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
            'name'        => 'Emergency Response Equipment',
            'description' => 'Heavy duty rescue equipment',
            'status'      => 'active',
        ]);

        $this->type = AssetType::create([
            'name'                              => 'Rescue Inflatable Boat',
            'category_id'                       => $this->category->id,
            'serialized'                        => true,
            'borrowable'                        => true,
            'requires_maintenance'              => true,
            'requires_inspection'               => true,
            'has_accessories'                   => true,
            'has_attachments'                   => true,
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

        $this->attachment = Attachment::create([
            'name'          => 'Heavy Duty Tow Rope (30m)',
            'type'          => 'Rope',
            'asset_type_id' => $this->type->id,
            'asset_id'      => $this->asset->id,
            'quantity'      => 3,
            'unit_cost'     => 1500.00,
            'condition'     => 'good',
        ]);

        $this->accessory = AssetAccessory::create([
            'asset_id'       => $this->asset->id,
            'asset_type'     => 'Rescue Inflatable Boat',
            'accessory_name' => 'High-Pressure Foot Air Pump',
            'quantity'       => 2,
            'unit_cost'      => 2500.00,
        ]);
    }

    /**
     * Requirement 1: Staff Members can access both tabs and view records.
     */
    public function test_staff_member_can_access_both_tabs_and_view_records(): void
    {
        // 1. Tab: Asset Attachments
        $resAttach = $this->actingAs($this->staff)->get(route('attachments.index', ['tab' => 'attachments']));
        $resAttach->assertOk();
        $resAttach->assertViewHas('activeTab', 'attachments');
        $resAttach->assertSee('Heavy Duty Tow Rope (30m)');
        $resAttach->assertSee('Rope');
        $resAttach->assertSee('Rescue Inflatable Boat');
        $resAttach->assertSee('AST-BOAT01');
        $resAttach->assertSee('Zodiac Milpro Inflatable');
        $resAttach->assertSee('3 Available');
        $resAttach->assertSee('Good');

        // 2. Tab: Asset Accessories
        $resAccessory = $this->actingAs($this->staff)->get(route('attachments.index', ['tab' => 'accessories']));
        $resAccessory->assertOk();
        $resAccessory->assertViewHas('activeTab', 'accessories');
        $resAccessory->assertSee('High-Pressure Foot Air Pump');
        $resAccessory->assertSee('Rescue Inflatable Boat');
        $resAccessory->assertSee('AST-BOAT01');
        $resAccessory->assertSee('Zodiac Milpro Inflatable');
        $resAccessory->assertSee('2 Available');
    }

    /**
     * Requirement 1: Staff can search, filter, and sort records.
     */
    public function test_staff_member_can_search_filter_and_sort_attachments_and_accessories(): void
    {
        // Search attachments
        $resSearchAttach = $this->actingAs($this->staff)->get(route('attachments.index', [
            'tab'    => 'attachments',
            'search' => 'Tow Rope',
        ]));
        $resSearchAttach->assertOk();
        $resSearchAttach->assertSee('Heavy Duty Tow Rope (30m)');

        // Filter attachments by type and condition
        $resFilterAttach = $this->actingAs($this->staff)->get(route('attachments.index', [
            'tab'       => 'attachments',
            'type'      => 'Rope',
            'condition' => 'good',
        ]));
        $resFilterAttach->assertOk();
        $resFilterAttach->assertSee('Heavy Duty Tow Rope (30m)');

        // Sort attachments
        $resSortAttach = $this->actingAs($this->staff)->get(route('attachments.index', [
            'tab'      => 'attachments',
            'sort_by'  => 'name',
            'sort_dir' => 'asc',
        ]));
        $resSortAttach->assertOk();
        $resSortAttach->assertSee('Heavy Duty Tow Rope (30m)');

        // Search accessories
        $resSearchAcc = $this->actingAs($this->staff)->get(route('attachments.index', [
            'tab'    => 'accessories',
            'search' => 'Foot Air Pump',
        ]));
        $resSearchAcc->assertOk();
        $resSearchAcc->assertSee('High-Pressure Foot Air Pump');

        // Filter accessories by status and asset_id
        $resFilterAcc = $this->actingAs($this->staff)->get(route('attachments.index', [
            'tab'      => 'accessories',
            'status'   => 'Available',
            'asset_id' => $this->asset->id,
        ]));
        $resFilterAcc->assertOk();
        $resFilterAcc->assertSee('High-Pressure Foot Air Pump');

        // Sort accessories
        $resSortAcc = $this->actingAs($this->staff)->get(route('attachments.index', [
            'tab'      => 'accessories',
            'sort_by'  => 'accessory_name',
            'sort_dir' => 'asc',
        ]));
        $resSortAcc->assertOk();
        $resSortAcc->assertSee('High-Pressure Foot Air Pump');
    }

    /**
     * Requirement 2: Linked asset is displayed but not clickable for Staff.
     */
    public function test_linked_asset_is_displayed_but_not_clickable_for_staff(): void
    {
        // 1. Attachments Tab
        $resAttach = $this->actingAs($this->staff)->get(route('attachments.index', ['tab' => 'attachments']));
        $resAttach->assertOk();
        // Displays asset name and code
        $resAttach->assertSee('Zodiac Milpro Inflatable');
        $resAttach->assertSee('AST-BOAT01');
        // Does NOT link to asset registry
        $resAttach->assertDontSee(route('assets.show', $this->asset));

        // 2. Accessories Tab
        $resAcc = $this->actingAs($this->staff)->get(route('attachments.index', ['tab' => 'accessories']));
        $resAcc->assertOk();
        // Displays asset name and code
        $resAcc->assertSee('Zodiac Milpro Inflatable');
        $resAcc->assertSee('AST-BOAT01');
        // Does NOT link to asset registry
        $resAcc->assertDontSee(route('assets.show', $this->asset));
    }

    /**
     * Requirement 3: Interface hides Add, Edit, Delete, and Adjust Quantity buttons for Staff.
     */
    public function test_interface_hides_action_buttons_for_staff(): void
    {
        $resAttach = $this->actingAs($this->staff)->get(route('attachments.index', ['tab' => 'attachments']));
        $resAttach->assertOk();

        // Add Attachment button must NOT be present
        $resAttach->assertDontSee('openAddAttachmentModal()');
        $resAttach->assertDontSee('Add Attachment</button>', false);

        // Edit and Delete buttons for attachments must NOT be present
        $resAttach->assertDontSee('openEditAttachmentModal');
        $resAttach->assertDontSee('title="Edit Attachment"', false);
        $resAttach->assertDontSee('title="Delete Attachment"', false);

        // Quick View button MUST be present
        $resAttach->assertSee('title="Quick View"', false);
        $resAttach->assertSee('openViewAttachmentModal');

        // Modifying modals must NOT be present in DOM
        $resAttach->assertDontSee('id="add-attachment-modal"', false);
        $resAttach->assertDontSee('id="edit-attachment-modal"', false);

        // Read-only view modal MUST be present in DOM
        $resAttach->assertSee('id="view-attachment-modal"', false);

        // Accessories Tab
        $resAcc = $this->actingAs($this->staff)->get(route('attachments.index', ['tab' => 'accessories']));
        $resAcc->assertOk();

        // Add Accessory button must NOT be present
        $resAcc->assertDontSee('openAddAccessoryModal()');
        $resAcc->assertDontSee('Add Accessory</button>', false);

        // Edit, Delete, and Adjust Quantity buttons must NOT be present
        $resAcc->assertDontSee('openEditAccessoryModal');
        $resAcc->assertDontSee('title="Edit Accessory"', false);
        $resAcc->assertDontSee('title="Delete Accessory"', false);
        $resAcc->assertDontSee('openAdjustModal');
        $resAcc->assertDontSee('title="Adjust Quantity"', false);

        // Quick View button MUST be present
        $resAcc->assertSee('title="Quick View"', false);
        $resAcc->assertSee('openViewAccessoryModal');

        // Modifying modals must NOT be present in DOM
        $resAcc->assertDontSee('id="add-accessory-modal"', false);
        $resAcc->assertDontSee('id="edit-accessory-modal"', false);
        $resAcc->assertDontSee('id="adjust-quantity-modal"', false);

        // Read-only view modal MUST be present in DOM
        $resAcc->assertSee('id="view-accessory-modal"', false);
    }

    /**
     * Requirement 4: Restricted backend operations are rejected for Staff Members (403 Forbidden).
     */
    public function test_restricted_backend_operations_rejected_for_staff(): void
    {
        // 1. GET /attachments/create -> 403
        $this->actingAs($this->staff)->get(route('attachments.create'))->assertForbidden();

        // 2. POST /attachments (store) -> 403
        $this->actingAs($this->staff)->post(route('attachments.store'), [
            'name'      => 'Unauthorized New Rope',
            'type'      => 'Rope',
            'asset_id'  => $this->asset->id,
            'quantity'  => 5,
            'condition' => 'good',
        ])->assertForbidden();

        // 3. GET /attachments/{id}/edit -> 403
        $this->actingAs($this->staff)->get(route('attachments.edit', $this->attachment))->assertForbidden();

        // 4. PUT /attachments/{id} (update) -> 403
        $this->actingAs($this->staff)->put(route('attachments.update', $this->attachment), [
            'name'      => 'Modified Tow Rope Name',
            'quantity'  => 99,
            'condition' => 'poor',
        ])->assertForbidden();

        // 5. DELETE /attachments/{id} -> 403
        $this->actingAs($this->staff)->delete(route('attachments.destroy', $this->attachment))->assertForbidden();

        // 6. POST /asset-accessories (store) -> 403
        $this->actingAs($this->staff)->post(route('asset-accessories.store'), [
            'asset_id'       => $this->asset->id,
            'accessory_name' => 'Unauthorized Extra Propeller',
            'quantity'       => 1,
            'unit_cost'      => 4500.00,
        ])->assertForbidden();

        // 7. PUT /asset-accessories/{id} (update) -> 403
        $this->actingAs($this->staff)->put(route('asset-accessories.update', $this->accessory), [
            'asset_id'       => $this->asset->id,
            'accessory_name' => 'Altered Accessory Name',
            'quantity'       => 50,
            'unit_cost'      => 10.00,
        ])->assertForbidden();

        // 8. DELETE /asset-accessories/{id} -> 403
        $this->actingAs($this->staff)->delete(route('asset-accessories.destroy', $this->accessory))->assertForbidden();

        // 9. POST /asset-accessories/{id}/adjust-quantity -> 403
        $this->actingAs($this->staff)->post(route('asset-accessories.adjust-quantity', $this->accessory), [
            'action' => 'add',
            'amount' => 10,
        ])->assertForbidden();

        // 10. Direct GET /accessories/create, POST /accessories, etc. -> 403
        $this->actingAs($this->staff)->get(route('accessories.create'))->assertForbidden();
        $this->actingAs($this->staff)->post(route('accessories.store'), [
            'name'              => 'Unauthorized Accessory',
            'quantity_in_stock' => 1,
        ])->assertForbidden();

        // Confirm database was NOT modified
        $this->assertDatabaseHas('attachments', [
            'id'        => $this->attachment->id,
            'name'      => 'Heavy Duty Tow Rope (30m)',
            'quantity'  => 3,
            'condition' => 'good',
        ]);

        $this->assertDatabaseHas('asset_accessories', [
            'id'             => $this->accessory->id,
            'accessory_name' => 'High-Pressure Foot Air Pump',
            'quantity'       => 2,
        ]);
    }

    /**
     * Requirement 5: Administrator functionality remains completely intact.
     */
    public function test_administrator_retains_full_action_capabilities(): void
    {
        // 1. Attachments Tab interface check
        $resAttach = $this->actingAs($this->admin)->get(route('attachments.index', ['tab' => 'attachments']));
        $resAttach->assertOk();
        $resAttach->assertSee('Add Attachment');
        $resAttach->assertSee('openAddAttachmentModal()');
        $resAttach->assertSee('openEditAttachmentModal');
        $resAttach->assertSee('title="Quick View"', false);
        $resAttach->assertSee('title="Edit Attachment"', false);
        $resAttach->assertSee('title="Delete Attachment"', false);
        $resAttach->assertSee('id="add-attachment-modal"', false);
        $resAttach->assertSee('id="edit-attachment-modal"', false);
        $resAttach->assertSee(route('assets.show', $this->asset));

        // 2. Accessories Tab interface check
        $resAcc = $this->actingAs($this->admin)->get(route('attachments.index', ['tab' => 'accessories']));
        $resAcc->assertOk();
        $resAcc->assertSee('Add Accessory');
        $resAcc->assertSee('openAddAccessoryModal()');
        $resAcc->assertSee('openEditAccessoryModal');
        $resAcc->assertSee('openAdjustModal');
        $resAcc->assertSee('title="Quick View"', false);
        $resAcc->assertSee('title="Edit Accessory"', false);
        $resAcc->assertSee('title="Delete Accessory"', false);
        $resAcc->assertSee('title="Adjust Quantity"', false);
        $resAcc->assertSee('id="add-accessory-modal"', false);
        $resAcc->assertSee('id="edit-accessory-modal"', false);
        $resAcc->assertSee('id="adjust-quantity-modal"', false);
        $resAcc->assertSee(route('assets.show', $this->asset));

        // 3. Admin can store a new attachment
        $resStoreAttach = $this->actingAs($this->admin)->post(route('attachments.store'), [
            'name'          => 'Admin Stored Tow Strap',
            'type'          => 'Strap',
            'asset_type_id' => $this->type->id,
            'asset_id'      => $this->asset->id,
            'quantity'      => 4,
            'unit_cost'     => 850.00,
            'condition'     => 'excellent',
        ]);
        $resStoreAttach->assertRedirect();
        $this->assertDatabaseHas('attachments', [
            'name'     => 'Admin Stored Tow Strap',
            'quantity' => 4,
        ]);

        // 4. Admin can update an attachment
        $resUpdateAttach = $this->actingAs($this->admin)->put(route('attachments.update', $this->attachment), [
            'name'          => 'Updated Heavy Tow Rope',
            'type'          => 'Rope',
            'asset_type_id' => $this->type->id,
            'asset_id'      => $this->asset->id,
            'quantity'      => 6,
            'condition'     => 'excellent',
        ]);
        $resUpdateAttach->assertRedirect();
        $this->assertDatabaseHas('attachments', [
            'id'        => $this->attachment->id,
            'name'      => 'Updated Heavy Tow Rope',
            'quantity'  => 6,
            'condition' => 'excellent',
        ]);

        // 5. Admin can adjust accessory quantity
        $resAdjust = $this->actingAs($this->admin)->post(route('asset-accessories.adjust-quantity', $this->accessory), [
            'action' => 'add',
            'amount' => 5,
        ]);
        $resAdjust->assertRedirect();
        $this->assertDatabaseHas('asset_accessories', [
            'id'       => $this->accessory->id,
            'quantity' => 7,
        ]);

        // 6. Admin can delete an attachment
        $resDestroy = $this->actingAs($this->admin)->delete(route('attachments.destroy', $this->attachment));
        $resDestroy->assertRedirect();
        $this->assertDatabaseMissing('attachments', ['id' => $this->attachment->id]);
    }
}
