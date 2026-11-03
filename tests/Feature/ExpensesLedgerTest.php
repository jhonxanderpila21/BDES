<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\Location;
use App\Models\Maintenance;
use App\Models\OperationalRequirement;
use App\Models\OperationalRequirementRestock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpensesLedgerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $inventoryOfficer;
    protected User $viewer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'      => 'administrator',
            'is_active' => true,
        ]);

        $this->inventoryOfficer = User::factory()->create([
            'role'      => 'inventory_officer',
            'is_active' => true,
        ]);

        $this->viewer = User::factory()->create([
            'role'      => 'viewer',
            'is_active' => true,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('financials.expenses'));
        $response->assertRedirect('/login');
    }

    public function test_non_admin_roles_are_forbidden_from_expenses_ledger(): void
    {
        $this->actingAs($this->inventoryOfficer)
            ->get(route('financials.expenses'))
            ->assertForbidden();

        $this->actingAs($this->viewer)
            ->get(route('financials.expenses'))
            ->assertForbidden();
    }

    public function test_admin_can_view_consolidated_expenses_ledger(): void
    {
        $category = AssetCategory::create(['name' => 'Emergency Vehicles', 'status' => 'active']);
        $location = Location::create(['name' => 'Main Barangay Hall', 'status' => 'active']);

        // 1. Create a capital asset
        $asset = Asset::create([
            'name'             => 'Rescue Boat 01',
            'code'             => 'BOT-001',
            'category_id'      => $category->id,
            'location_id'      => $location->id,
            'condition'        => 'good',
            'status'           => 'available',
            'acquisition_cost' => 150000.00,
            'purchase_date'    => now()->toDateString(),
        ]);

        // 2. Create a completed maintenance record with cost
        Maintenance::create([
            'asset_id'         => $asset->id,
            'type'             => 'repair',
            'cost'             => 12500.00,
            'status'           => 'completed',
            'technician'       => 'Marine Power Services',
            'notes'            => 'Engine Overhaul and replacement of cylinder head',
        ]);

        // 3. Create an operational requirement restock
        $type = AssetType::create([
            'name'        => 'Outboard Engine',
            'category_id' => $category->id,
            'status'      => 'active',
        ]);

        $req = OperationalRequirement::create([
            'asset_type_id'    => $type->id,
            'requirement_type' => '2-Stroke Engine Oil',
            'description'      => 'High grade 2-stroke outboard motor engine oil',
            'unit'             => 'liters',
            'current_level'    => 10,
            'minimum_level'    => 2,
            'maximum_capacity' => 50,
        ]);

        OperationalRequirementRestock::create([
            'operational_requirement_id' => $req->id,
            'quantity'                   => 10,
            'unit_cost'                  => 350.00,
            'total_cost'                 => 3500.00,
            'supplier'                   => 'Shell Caltex',
            'restocked_by'               => $this->admin->id,
            'restocked_at'               => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('financials.expenses'));

        $response->assertOk();
        $response->assertSee('Expenses Ledger');
        $response->assertSee('Rescue Boat 01');
        $response->assertSee('Engine Overhaul');
        $response->assertSee('2-Stroke Engine Oil');
        // Total costs should reflect in the metrics
        $response->assertSee('166,000.00'); // 150000 + 12500 + 3500
    }

    public function test_expenses_filter_by_category(): void
    {
        $category = AssetCategory::create(['name' => 'Generators', 'status' => 'active']);
        $location = Location::create(['name' => 'Power Depot', 'status' => 'active']);

        Asset::create([
            'name'             => 'Heavy Duty Diesel Generator',
            'code'             => 'GEN-001',
            'category_id'      => $category->id,
            'location_id'      => $location->id,
            'condition'        => 'good',
            'status'           => 'available',
            'acquisition_cost' => 85000.00,
            'purchase_date'    => now()->toDateString(),
        ]);

        // Query filtering only maintenance_service
        $response = $this->actingAs($this->admin)->get(route('financials.expenses', [
            'category' => 'maintenance_service',
        ]));

        $response->assertOk();
        $response->assertDontSee('Heavy Duty Diesel Generator');
    }

    public function test_expenses_csv_export(): void
    {
        $category = AssetCategory::create(['name' => 'Medical Equipment', 'status' => 'active']);
        $location = Location::create(['name' => 'Health Center', 'status' => 'active']);

        Asset::create([
            'name'             => 'Defibrillator Unit',
            'code'             => 'AED-001',
            'category_id'      => $category->id,
            'location_id'      => $location->id,
            'condition'        => 'excellent',
            'status'           => 'available',
            'acquisition_cost' => 65000.00,
            'purchase_date'    => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('financials.expenses.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Defibrillator Unit', $response->streamedContent());
        $this->assertStringContainsString('AED-001', $response->streamedContent());
    }
}
