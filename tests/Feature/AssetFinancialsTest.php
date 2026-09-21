<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetFinancialsTest extends TestCase
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
            'name'        => 'Emergency Vehicles',
            'description' => 'Rescue & Transport vehicles',
        ]);

        $location = Location::create([
            'name'     => 'Central Station',
            'capacity' => 15,
        ]);

        $this->asset = Asset::create([
            'code'              => 'VEH-TEST-001',
            'name'              => 'Rescue Ambulance',
            'category_id'       => $category->id,
            'location_id'       => $location->id,
            'condition'         => 'good',
            'status'            => 'available',
            'purchase_date'     => Carbon::now()->subYears(2)->format('Y-m-d'),
            'acquisition_cost'  => 100000.00,
            'useful_life_years' => 5,
        ]);
    }

    public function test_depreciation_page_renders_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('financials.depreciation'));

        $response->assertOk();
        $response->assertSee('Asset Depreciation Schedule');
        $response->assertSee('Rescue Ambulance');
        $response->assertSee('VEH-TEST-001');
    }

    public function test_can_update_asset_depreciation_parameters(): void
    {
        $response = $this->actingAs($this->user)->post(route('financials.depreciation.update', $this->asset), [
            'useful_life_years' => 8,
            'salvage_value'     => 15000.00,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->asset->refresh();
        $this->assertEquals(8, $this->asset->useful_life_years);
        $this->assertEquals(15000.00, (float) $this->asset->salvage_value);
    }

    public function test_can_export_depreciation_schedule_to_csv(): void
    {
        $response = $this->actingAs($this->user)->get(route('financials.depreciation.export'));

        $response->assertOk();
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));
    }

    public function test_salvage_records_page_renders_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('financials.salvage'));

        $response->assertOk();
        $response->assertSee('Salvage Value Records');
        $response->assertSee('Rescue Ambulance');
    }

    public function test_can_update_asset_salvage_record(): void
    {
        $response = $this->actingAs($this->user)->post(route('financials.salvage.update', $this->asset), [
            'salvage_value' => 12000.00,
            'salvage_notes' => 'Appraised salvage recovery value for municipal vehicle parts',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->asset->refresh();
        $this->assertEquals(12000.00, (float) $this->asset->salvage_value);
        $this->assertEquals('Appraised salvage recovery value for municipal vehicle parts', $this->asset->salvage_notes);
    }

    public function test_asset_straight_line_depreciation_calculations(): void
    {
        // Cost: 100,000; default COA salvage: 10% = 10,000
        // Depreciable: 90,000
        // Useful life: 5 years -> Annual Dep: 18,000/yr
        // Age: ~2 years -> Accumulated Dep: ~36,000
        // Book value: ~64,000
        $this->assertEquals(10000.00, $this->asset->calculated_salvage_value);
        $this->assertEquals(90000.00, $this->asset->depreciable_amount);
        $this->assertEquals(18000.00, $this->asset->annual_depreciation);
        $this->assertGreaterThanOrEqual(35000.00, $this->asset->accumulated_depreciation);
        $this->assertLessThanOrEqual(65000.00, $this->asset->current_book_value);
    }
}

