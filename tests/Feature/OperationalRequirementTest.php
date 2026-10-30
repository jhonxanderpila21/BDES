<?php

namespace Tests\Feature;

use App\Models\AssetType;
use App\Models\OperationalRequirement;
use App\Models\User;
use Database\Seeders\AssetCategorySeeder;
use Database\Seeders\AssetTypeSeeder;
use Database\Seeders\OperationalRequirementSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalRequirementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $staff;

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

        $this->seed(AssetCategorySeeder::class);
        $this->seed(AssetTypeSeeder::class);
    }

    public function test_operational_requirement_seeder_populates_database(): void
    {
        $this->seed(OperationalRequirementSeeder::class);

        $this->assertGreaterThan(0, OperationalRequirement::count());

        // Verify generator requirements
        $generatorType = AssetType::where('name', 'Generator Set')->firstOrFail();
        $this->assertDatabaseHas('operational_requirements', [
            'asset_type_id'    => $generatorType->id,
            'requirement_type' => 'Diesel Fuel',
            'unit'             => 'Liters',
        ]);

        // Verify rescue boat requirements
        $boatType = AssetType::where('name', 'Rescue Boat')->firstOrFail();
        $this->assertDatabaseHas('operational_requirements', [
            'asset_type_id'    => $boatType->id,
            'requirement_type' => 'Marine Gasoline (Unleaded)',
            'unit'             => 'Liters',
        ]);
    }

    public function test_operational_requirement_refill_and_consume(): void
    {
        $generatorType = AssetType::where('name', 'Generator Set')->firstOrFail();

        $requirement = OperationalRequirement::create([
            'asset_type_id'    => $generatorType->id,
            'requirement_type' => 'Test Fuel',
            'unit'             => 'Liters',
            'current_level'    => 20.00,
            'maximum_capacity' => 50.00,
            'minimum_level'    => 10.00,
            'description'      => 'Test operational requirement description',
        ]);

        // Test consume
        $this->actingAs($this->user)->post(route('operational-requirements.consume', $requirement), [
            'amount' => 5.0,
        ]);
        $this->assertEquals(15.00, $requirement->fresh()->current_level);

        // Test refill
        $this->actingAs($this->user)->post(route('operational-requirements.refill', $requirement), [
            'amount' => 10.0,
        ]);
        $this->assertEquals(25.00, $requirement->fresh()->current_level);

        // Test refill capping at maximum_capacity
        $this->actingAs($this->user)->post(route('operational-requirements.refill', $requirement), [
            'amount' => 100.0,
        ]);
        $this->assertEquals(50.00, $requirement->fresh()->current_level);
    }

    public function test_operational_requirements_page_and_sidebar_dropdown_render(): void
    {
        $this->seed(OperationalRequirementSeeder::class);

        $response = $this->actingAs($this->user)->get(route('operational-requirements.index'));
        $response->assertOk();
        $response->assertSee('Operational Requirements');
        $response->assertSee('Asset Management');
        $response->assertSee('Diesel Fuel');

        // Test low stock filter
        $lowStockResponse = $this->actingAs($this->user)->get(route('operational-requirements.index', ['low' => 1]));
        $lowStockResponse->assertOk();
    }

    public function test_store_creates_requirement_without_asset_type_and_renders_name_unit_count(): void
    {
        $response = $this->actingAs($this->user)->post(route('operational-requirements.store'), [
            'requirement_type' => 'Emergency Ration Pack',
            'unit'             => 'Boxes',
            'current_level'    => 75,
            'maximum_capacity' => 200,
            'minimum_level'    => 20,
            'description'      => 'Barangay emergency response food packs.',
        ]);

        $response->assertRedirect(route('operational-requirements.index'));
        $this->assertDatabaseHas('operational_requirements', [
            'requirement_type' => 'Emergency Ration Pack',
            'unit'             => 'Boxes',
            'current_level'    => 75,
            'maximum_capacity' => 200,
            'asset_type_id'    => null,
        ]);

        $indexResponse = $this->actingAs($this->user)->get(route('operational-requirements.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Emergency Ration Pack');
        $indexResponse->assertSee('Boxes');
        $indexResponse->assertSee('75');
        $indexResponse->assertSee('window.closeModal(\'add-requirement-modal\')', false);
    }

    /**
     * Requirement 1: Staff Members can access the page and view existing requirements and details.
     */
    public function test_staff_member_can_view_all_existing_requirements_and_details(): void
    {
        $generatorType = AssetType::where('name', 'Generator Set')->firstOrFail();

        $requirement = OperationalRequirement::create([
            'asset_type_id'    => $generatorType->id,
            'requirement_type' => 'Diesel Fuel Grade A',
            'unit'             => 'Liters',
            'current_level'    => 40.00,
            'maximum_capacity' => 100.00,
            'minimum_level'    => 20.00,
            'description'      => 'High grade diesel for emergency power',
        ]);

        $response = $this->actingAs($this->staff)->get(route('operational-requirements.index'));
        $response->assertOk();
        $response->assertSee('Operational Requirements');
        $response->assertSee('Diesel Fuel Grade A');
        $response->assertSee('Liters');
        $response->assertSee('40');
        $response->assertSee('Capacity: 100 Liters (Min: 20)');
        $response->assertSee('In Stock');
        $response->assertSee('High grade diesel for emergency power');

        // Quick View button and details modal MUST be present
        $response->assertSee('title="View Details"', false);
        $response->assertSee('openViewRequirementModal');
        $response->assertSee('id="view-requirement-modal"', false);
    }

    /**
     * Requirement 1 & 2: Staff Members can search and use the Low Stock Only filter.
     */
    public function test_staff_member_can_search_and_use_low_stock_filter(): void
    {
        $generatorType = AssetType::where('name', 'Generator Set')->firstOrFail();

        $normalReq = OperationalRequirement::create([
            'asset_type_id'    => $generatorType->id,
            'requirement_type' => 'Standard Motor Oil',
            'unit'             => 'Liters',
            'current_level'    => 50.00,
            'maximum_capacity' => 60.00,
            'minimum_level'    => 10.00,
            'description'      => 'Regular engine maintenance oil',
        ]);

        $lowReq = OperationalRequirement::create([
            'asset_type_id'    => $generatorType->id,
            'requirement_type' => 'Critical Coolant Fluid',
            'unit'             => 'Liters',
            'current_level'    => 5.00,
            'maximum_capacity' => 50.00,
            'minimum_level'    => 15.00,
            'description'      => 'High performance radiator coolant',
        ]);

        // 1. Search bar test
        $searchRes = $this->actingAs($this->staff)->get(route('operational-requirements.index', [
            'search' => 'Coolant',
        ]));
        $searchRes->assertOk();
        $searchRes->assertSee('Critical Coolant Fluid');
        $searchRes->assertDontSee('Standard Motor Oil');

        // 2. Low stock only filter test
        $lowRes = $this->actingAs($this->staff)->get(route('operational-requirements.index', [
            'low' => 1,
        ]));
        $lowRes->assertOk();
        $lowRes->assertSee('Critical Coolant Fluid');
        $lowRes->assertSee('Low Stock');
        $lowRes->assertDontSee('Standard Motor Oil');
    }

    /**
     * Requirement 3: Interface hides Add Requirement, Add Stock (+), Edit, and Delete actions for Staff.
     */
    public function test_interface_hides_action_buttons_for_staff(): void
    {
        $generatorType = AssetType::where('name', 'Generator Set')->firstOrFail();

        OperationalRequirement::create([
            'asset_type_id'    => $generatorType->id,
            'requirement_type' => 'Emergency Battery Cells',
            'unit'             => 'Pcs',
            'current_level'    => 10.00,
            'maximum_capacity' => 30.00,
            'minimum_level'    => 5.00,
            'description'      => 'Lithium battery backup cells',
        ]);

        $response = $this->actingAs($this->staff)->get(route('operational-requirements.index'));
        $response->assertOk();

        // Add Requirement button MUST be hidden
        $response->assertDontSee('openAddRequirementModal()');
        $response->assertDontSee('Add Requirement</button>', false);

        // Add Stock (+), Edit, and Delete buttons MUST be hidden
        $response->assertDontSee('openAddQuantityModal');
        $response->assertDontSee('title="Add Quantity / Restock"', false);
        $response->assertDontSee('editRequirement');
        $response->assertDontSee('title="Edit Requirement"', false);
        $response->assertDontSee('title="Delete Requirement"', false);
        $response->assertDontSee('onsubmit="return confirm(\'Delete this requirement?\');"', false);

        // Modifying modals MUST NOT be present in DOM
        $response->assertDontSee('id="add-requirement-modal"', false);
        $response->assertDontSee('id="edit-requirement-modal"', false);
        $response->assertDontSee('id="add-quantity-modal"', false);

        // Search bar, Low Stock filter, and View Details modal MUST remain available
        $response->assertSee('name="search"', false);
        $response->assertSee('Low Stock Only');
        $response->assertSee('id="view-requirement-modal"', false);
        $response->assertSee('title="View Details"', false);
    }

    /**
     * Requirement 4: Restricted backend operations are rejected for Staff Members (403 Forbidden).
     */
    public function test_restricted_backend_operations_rejected_for_staff(): void
    {
        $generatorType = AssetType::where('name', 'Generator Set')->firstOrFail();

        $requirement = OperationalRequirement::create([
            'asset_type_id'    => $generatorType->id,
            'requirement_type' => 'Lubricant Oil',
            'unit'             => 'Liters',
            'current_level'    => 20.00,
            'maximum_capacity' => 50.00,
            'minimum_level'    => 10.00,
            'description'      => 'Standard heavy equipment lubricant',
        ]);

        // 1. GET /operational-requirements/create -> 403
        $this->actingAs($this->staff)->get(route('operational-requirements.create'))->assertForbidden();

        // 2. POST /operational-requirements (store) -> 403
        $this->actingAs($this->staff)->post(route('operational-requirements.store'), [
            'requirement_type' => 'Unauthorized Requirement',
            'unit'             => 'Boxes',
            'current_level'    => 10,
            'maximum_capacity' => 50,
            'minimum_level'    => 5,
        ])->assertForbidden();

        // 3. GET /operational-requirements/{id}/edit -> 403
        $this->actingAs($this->staff)->get(route('operational-requirements.edit', $requirement))->assertForbidden();

        // 4. PUT /operational-requirements/{id} (update) -> 403
        $this->actingAs($this->staff)->put(route('operational-requirements.update', $requirement), [
            'requirement_type' => 'Illegally Renamed',
            'unit'             => 'Liters',
            'current_level'    => 45,
            'maximum_capacity' => 50,
            'minimum_level'    => 10,
        ])->assertForbidden();

        // 5. POST /operational-requirements/{id}/refill -> 403
        $this->actingAs($this->staff)->post(route('operational-requirements.refill', $requirement), [
            'amount' => 10,
        ])->assertForbidden();

        // 6. POST /operational-requirements/{id}/consume -> 403
        $this->actingAs($this->staff)->post(route('operational-requirements.consume', $requirement), [
            'amount' => 5,
        ])->assertForbidden();

        // 7. DELETE /operational-requirements/{id} -> 403
        $this->actingAs($this->staff)->delete(route('operational-requirements.destroy', $requirement))->assertForbidden();

        // Confirm database was NOT altered
        $this->assertDatabaseHas('operational_requirements', [
            'id'               => $requirement->id,
            'requirement_type' => 'Lubricant Oil',
            'current_level'    => 20.00,
            'maximum_capacity' => 50.00,
            'minimum_level'    => 10.00,
        ]);
    }

    /**
     * Requirement 5: Administrator functionality and permissions remain completely intact.
     */
    public function test_administrator_retains_full_action_capabilities(): void
    {
        $generatorType = AssetType::where('name', 'Generator Set')->firstOrFail();

        $requirement = OperationalRequirement::create([
            'asset_type_id'    => $generatorType->id,
            'requirement_type' => 'Admin Managed Fuel',
            'unit'             => 'Liters',
            'current_level'    => 20.00,
            'maximum_capacity' => 100.00,
            'minimum_level'    => 15.00,
            'description'      => 'Admin dedicated fuel stock',
        ]);

        // Interface checks for Administrator
        $response = $this->actingAs($this->user)->get(route('operational-requirements.index'));
        $response->assertOk();
        $response->assertSee('Add Requirement');
        $response->assertSee('openAddRequirementModal()');
        $response->assertSee('openAddQuantityModal');
        $response->assertSee('title="Add Quantity / Restock"', false);
        $response->assertSee('editRequirement');
        $response->assertSee('title="Edit Requirement"', false);
        $response->assertSee('title="Delete Requirement"', false);
        $response->assertSee('id="add-requirement-modal"', false);
        $response->assertSee('id="edit-requirement-modal"', false);
        $response->assertSee('id="add-quantity-modal"', false);

        // Admin can update requirement
        $resUpdate = $this->actingAs($this->user)->put(route('operational-requirements.update', $requirement), [
            'requirement_type' => 'Admin Updated Fuel',
            'unit'             => 'Liters',
            'current_level'    => 25.00,
            'maximum_capacity' => 100.00,
            'minimum_level'    => 15.00,
        ]);
        $resUpdate->assertRedirect(route('operational-requirements.index'));
        $this->assertDatabaseHas('operational_requirements', [
            'id'               => $requirement->id,
            'requirement_type' => 'Admin Updated Fuel',
            'current_level'    => 25.00,
        ]);

        // Admin can refill stock
        $resRefill = $this->actingAs($this->user)->post(route('operational-requirements.refill', $requirement), [
            'amount' => 15.00,
        ]);
        $resRefill->assertRedirect(route('operational-requirements.index'));
        $this->assertEquals(40.00, $requirement->fresh()->current_level);

        // Admin can consume stock
        $resConsume = $this->actingAs($this->user)->post(route('operational-requirements.consume', $requirement), [
            'amount' => 10.00,
        ]);
        $resConsume->assertRedirect(route('operational-requirements.index'));
        $this->assertEquals(30.00, $requirement->fresh()->current_level);

        // Admin can delete requirement
        $resDelete = $this->actingAs($this->user)->delete(route('operational-requirements.destroy', $requirement));
        $resDelete->assertRedirect(route('operational-requirements.index'));
        $this->assertDatabaseMissing('operational_requirements', ['id' => $requirement->id]);
    }
}
