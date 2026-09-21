<?php

namespace Tests\Feature;

use App\Http\Controllers\ReportController;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AuditLog;
use App\Models\Deployment;
use App\Models\Inspection;
use App\Models\Location;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private AssetCategory $category;
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
            'name'   => 'First Response Kits',
            'status' => 'active',
        ]);

        $this->location = Location::create([
            'name'     => 'Main Hall Cabinet A',
            'capacity' => 20,
        ]);

        $this->asset = Asset::create([
            'code'             => 'AST-MED001',
            'name'             => 'Trauma First Aid Kit Level 3',
            'category_id'      => $this->category->id,
            'location_id'      => $this->location->id,
            'condition'        => 'excellent',
            'status'           => 'available',
            'acquisition_cost' => 12500.00,
        ]);
    }

    public function test_reports_index_loads_default_inventory_view(): void
    {
        $response = $this->actingAs($this->user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertSee('Regulatory Compliance Center');
        $response->assertSee($this->asset->name);
        $response->assertSee('12,500.00');
    }

    public function test_all_ten_standard_reports_load_successfully(): void
    {
        $types = array_keys(ReportController::reportTypes());

        $this->assertCount(10, $types);

        foreach ($types as $type) {
            $response = $this->actingAs($this->user)->get(route('reports.index', ['type' => $type]));
            $response->assertOk();
            $response->assertViewHas('selectedType', $type);
        }
    }

    public function test_all_ten_standard_reports_can_be_streamed_as_csv(): void
    {
        $types = array_keys(ReportController::reportTypes());

        foreach ($types as $type) {
            $response = $this->actingAs($this->user)->get(route('reports.export', ['type' => $type]));
            $response->assertOk();
            $this->assertEquals('text/csv; charset=UTF-8', $response->headers->get('content-type'));
            $this->assertStringContainsString('bdes_report_' . $type, $response->headers->get('content-disposition'));
        }
    }

    public function test_inventory_report_filters_by_category_and_status(): void
    {
        $otherCat = AssetCategory::create(['name' => 'Heavy Marine', 'status' => 'active']);
        $deployedAsset = Asset::create([
            'code'             => 'AST-MAR001',
            'name'             => 'Outboard Motor 40HP',
            'category_id'      => $otherCat->id,
            'location_id'      => $this->location->id,
            'condition'        => 'good',
            'status'           => 'deployed',
            'acquisition_cost' => 150000.00,
        ]);

        // Filter by category
        $responseCat = $this->actingAs($this->user)->get(route('reports.index', [
            'type'        => 'inventory',
            'category_id' => $otherCat->id,
        ]));
        $responseCat->assertOk();
        $responseCat->assertSee($deployedAsset->name);
        $responseCat->assertDontSee($this->asset->name);

        // Filter by status
        $responseStatus = $this->actingAs($this->user)->get(route('reports.index', [
            'type'   => 'inventory',
            'status' => 'available',
        ]));
        $responseStatus->assertOk();
        $responseStatus->assertSee($this->asset->name);
        $responseStatus->assertDontSee($deployedAsset->name);
    }
}
