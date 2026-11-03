<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\Location;
use App\Models\User;
use App\Services\PhilippineAccountingStandardService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhilippineAccountingStandardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $inventoryOfficer;
    private AssetCategory $vehicleCategory;
    private AssetCategory $toolsCategory;
    private Location $depotLocation;

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

        $this->vehicleCategory = AssetCategory::create([
            'name'   => 'Transportation & Emergency Vehicles',
            'status' => 'active',
        ]);

        $this->toolsCategory = AssetCategory::create([
            'name'   => 'Rescue Hand Tools & Supplies',
            'status' => 'active',
        ]);

        $this->depotLocation = Location::create([
            'name'             => 'Central Disaster Response Depot',
            'storage_capacity' => 100,
        ]);
    }

    public function test_equipment_below_threshold_classified_as_semi_expendable_with_zero_depreciation_and_salvage(): void
    {
        // Add equipment with acquisition cost below ₱20,000
        $response = $this->actingAs($this->inventoryOfficer)->post(route('assets.store'), [
            'name'             => 'Portable Water Pump',
            'category_id'      => $this->toolsCategory->id,
            'location_id'      => $this->depotLocation->id,
            'condition'        => 'good',
            'status'           => 'available',
            'purchase_date'    => Carbon::now()->subYears(2)->toDateString(),
            'acquisition_cost' => '₱ 12,000.00', // Under ₱20k threshold
        ]);

        $response->assertRedirect();

        $asset = Asset::where('name', 'Portable Water Pump')->firstOrFail();

        // Enforce statutory rules
        $this->assertTrue($asset->is_semi_expendable);
        $this->assertFalse($asset->is_capitalized_ppe);
        $this->assertEquals('Semi-Expendable Property (< ₱20,000)', $asset->coa_classification);

        // Salvage and depreciation MUST evaluate to zero
        $this->assertEquals(0.00, $asset->calculated_salvage_value);
        $this->assertEquals(0.00, $asset->depreciable_amount);
        $this->assertEquals(0.00, $asset->annual_depreciation);
        $this->assertEquals(0.00, $asset->monthly_depreciation);
        $this->assertEquals(0.00, $asset->accumulated_depreciation);
        $this->assertEquals(0.00, $asset->depreciation_percentage);

        // Book value remains at original cost
        $this->assertEquals(12000.00, $asset->current_book_value);
    }

    public function test_equipment_at_or_above_threshold_auto_calculates_10_percent_salvage_and_coa_useful_life(): void
    {
        // Add tangible vehicle with acquisition cost ≥ ₱20,000 (Capitalized PPE)
        $response = $this->actingAs($this->inventoryOfficer)->post(route('assets.store'), [
            'name'             => 'Disaster Rescue Ambulance Van',
            'category_id'      => $this->vehicleCategory->id,
            'location_id'      => $this->depotLocation->id,
            'condition'        => 'excellent',
            'status'           => 'available',
            'purchase_date'    => Carbon::now()->subYears(1)->toDateString(),
            'acquisition_cost' => '120,000.00',
        ]);

        $response->assertRedirect();

        $asset = Asset::where('name', 'Disaster Rescue Ambulance Van')->firstOrFail();

        // Enforce Capitalized PPE status
        $this->assertTrue($asset->is_capitalized_ppe);
        $this->assertFalse($asset->is_semi_expendable);
        $this->assertEquals('Capitalized PPE (₱20,000+)', $asset->coa_classification);

        // Auto-computed 10% COA residual value (₱120,000 * 10% = ₱12,000)
        $this->assertEquals(12000.00, $asset->calculated_salvage_value);
        $this->assertEquals(12000.00, (float) $asset->salvage_value);

        // Auto-assigned COA useful life for Transportation (7 years)
        $this->assertEquals(7, $asset->effective_useful_life);

        // Depreciable amount = 120,000 - 12,000 = 108,000.00
        $this->assertEquals(108000.00, $asset->depreciable_amount);

        // Annual depreciation = 108,000 / 7 = 15,428.57
        $this->assertEquals(15428.57, $asset->annual_depreciation);

        // Monthly depreciation = 15,428.57 / 12 = 1,285.71
        $this->assertEquals(1285.71, $asset->monthly_depreciation);

        // Accumulated depreciation after ~1 year
        $this->assertGreaterThanOrEqual(14000.00, $asset->accumulated_depreciation);
        $this->assertLessThanOrEqual(17000.00, $asset->accumulated_depreciation);

        // Book value should be reduced from 120,000 by ~15,428
        $this->assertLessThanOrEqual(106000.00, $asset->current_book_value);
        $this->assertGreaterThanOrEqual($asset->calculated_salvage_value, $asset->current_book_value);
    }

    public function test_updating_semi_expendable_asset_with_salvage_value_is_rejected_with_coa_statutory_error(): void
    {
        $semiExpendable = Asset::create([
            'code'              => 'TEST-SEMI-001',
            'name'              => 'Handheld Megaphone',
            'category_id'       => $this->toolsCategory->id,
            'location_id'       => $this->depotLocation->id,
            'condition'         => 'good',
            'status'            => 'available',
            'acquisition_cost'  => 8500.00,
            'useful_life_years' => 3,
        ]);

        // Attempt to update depreciation schedule on item below ₱20,000
        $response = $this->actingAs($this->admin)->post(route('financials.depreciation.update', $semiExpendable), [
            'useful_life_years' => 5,
            'salvage_value'     => 1000.00,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertTrue(str_contains(session('error'), 'Semi-Expendable Property'));

        // Attempt to update salvage record on item below ₱20,000
        $salvageResponse = $this->actingAs($this->admin)->post(route('financials.salvage.update', $semiExpendable), [
            'salvage_value' => 850.00,
            'salvage_notes' => 'Custom salvage valuation',
        ]);

        $salvageResponse->assertRedirect();
        $salvageResponse->assertSessionHas('error');
        $this->assertTrue(str_contains(session('error'), 'Semi-Expendable Property'));
    }

    public function test_updating_cost_to_meet_20k_promotes_asset_to_capitalized_ppe(): void
    {
        $asset = Asset::create([
            'code'              => 'TEST-PROMO-01',
            'name'              => 'Upgradable Radio System',
            'category_id'       => $this->toolsCategory->id,
            'location_id'       => $this->depotLocation->id,
            'condition'         => 'good',
            'status'            => 'available',
            'acquisition_cost'  => 15000.00, // Semi-Expendable (< 20k)
        ]);

        $this->assertFalse($asset->is_capitalized_ppe);
        $this->assertEquals(0.00, $asset->calculated_salvage_value);

        // Update cost to ₱75,000 (Capitalized PPE)
        $response = $this->actingAs($this->inventoryOfficer)->put(route('assets.update', $asset), [
            'code'             => $asset->code,
            'name'             => $asset->name,
            'category_id'      => $asset->category_id,
            'location_id'      => $asset->location_id,
            'condition'        => $asset->condition,
            'status'           => $asset->status,
            'acquisition_cost' => 75000.00,
        ]);

        $response->assertRedirect();
        $asset->refresh();

        $this->assertTrue($asset->is_capitalized_ppe);
        $this->assertEquals(7500.00, $asset->calculated_salvage_value);
        $this->assertEquals(67500.00, $asset->depreciable_amount);
    }
}

