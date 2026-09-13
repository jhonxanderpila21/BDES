<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\Inspection;
use App\Models\Location;
use App\Models\Maintenance;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnifiedMaintenanceTest extends TestCase
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
            'name'        => 'Power & Heavy Gear',
            'description' => 'Heavy equipment',
        ]);

        $type = AssetType::create([
            'name'        => 'Generator',
            'category_id' => $category->id,
        ]);

        $location = Location::create([
            'name'     => 'Barangay Storage Yard',
            'capacity' => 20,
        ]);

        $this->asset = Asset::create([
            'code'        => 'GEN-UNIFIED',
            'name'        => 'Heavy Duty Generator',
            'category_id' => $category->id,
            'type_id'     => $type->id,
            'location_id' => $location->id,
            'condition'   => 'good',
            'status'      => 'available',
        ]);
    }

    public function test_unified_maintenance_hub_renders_successfully_with_all_metrics(): void
    {
        MaintenanceSchedule::create([
            'asset_id'              => $this->asset->id,
            'asset_type'            => 'Generator',
            'maintenance_task'      => 'Oil & Filter Change',
            'interval_type'         => 'Monthly',
            'interval_days'         => 30,
            'last_maintenance_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
        ]);

        Maintenance::create([
            'asset_id'       => $this->asset->id,
            'type'           => 'routine',
            'technician'     => 'Lead Mechanic John',
            'scheduled_date' => Carbon::now()->format('Y-m-d'),
            'status'         => 'in_progress',
        ]);

        Inspection::create([
            'asset_id'        => $this->asset->id,
            'inspector'       => 'Safety Officer Reyes',
            'inspection_date' => Carbon::now()->format('Y-m-d'),
            'result'          => 'passed',
            'status'          => 'completed',
        ]);

        $response = $this->actingAs($this->user)->get(route('maintenance.index'));

        $response->assertStatus(200);
        $response->assertSee('Maintenance');
        $response->assertSee('Preventive Schedules');
        $response->assertSee('Maintenance Logs');
        $response->assertSee('Asset Inspections');
        $response->assertSee('Oil & Filter Change');
    }

    public function test_can_switch_tabs_schedules_logs_and_inspections(): void
    {
        Maintenance::create([
            'asset_id'       => $this->asset->id,
            'type'           => 'repair',
            'technician'     => 'Specialist Mike',
            'scheduled_date' => Carbon::now()->format('Y-m-d'),
            'status'         => 'scheduled',
            'notes'          => 'Replace fuel pump',
        ]);

        Inspection::create([
            'asset_id'        => $this->asset->id,
            'inspector'       => 'Auditor Diaz',
            'inspection_date' => Carbon::now()->format('Y-m-d'),
            'result'          => 'conditional_pass',
            'findings'        => 'Minor oil sweat',
            'status'          => 'completed',
        ]);

        // Tab logs
        $responseLogs = $this->actingAs($this->user)->get(route('maintenance.index', ['tab' => 'logs']));
        $responseLogs->assertStatus(200);
        $responseLogs->assertSee('Specialist Mike');
        $responseLogs->assertSee('Replace fuel pump');

        // Tab inspections
        $responseInsp = $this->actingAs($this->user)->get(route('maintenance.index', ['tab' => 'inspections']));
        $responseInsp->assertStatus(200);
        $responseInsp->assertSee('Auditor Diaz');
        $responseInsp->assertSee('Minor oil sweat');
    }

    public function test_maintenance_logs_store_sets_asset_status_to_under_maintenance_when_in_progress(): void
    {
        $response = $this->actingAs($this->user)->post(route('maintenance.store'), [
            'asset_id'           => $this->asset->id,
            'type'               => 'repair',
            'technician'         => 'Heavy Gear Tech',
            'scheduled_date'     => Carbon::now()->format('Y-m-d'),
            'estimated_duration' => '3 days',
            'notes'              => 'Carburetor rebuilt',
            'status'             => 'in_progress',
        ]);

        $response->assertRedirect(route('maintenance.index', ['tab' => 'logs']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('maintenances', [
            'asset_id'   => $this->asset->id,
            'technician' => 'Heavy Gear Tech',
            'status'     => 'in_progress',
        ]);

        $this->assertEquals('under_maintenance', $this->asset->fresh()->status);
    }

    public function test_completing_maintenance_work_order_sets_asset_back_to_available(): void
    {
        $maintenance = Maintenance::create([
            'asset_id'       => $this->asset->id,
            'type'           => 'repair',
            'technician'     => 'Heavy Gear Tech',
            'scheduled_date' => Carbon::now()->format('Y-m-d'),
            'status'         => 'in_progress',
        ]);
        $this->asset->update(['status' => 'under_maintenance']);

        $response = $this->actingAs($this->user)->post(route('maintenance.complete', $maintenance));

        $response->assertRedirect(route('maintenance.index', ['tab' => 'logs']));
        $this->assertEquals('completed', $maintenance->fresh()->status);
        $this->assertEquals('available', $this->asset->fresh()->status);
    }

    public function test_can_record_and_update_inspection(): void
    {
        $response = $this->actingAs($this->user)->post(route('inspection.store'), [
            'asset_id'        => $this->asset->id,
            'inspector'       => 'Lead Auditor Ramos',
            'inspection_date' => Carbon::now()->format('Y-m-d'),
            'result'          => 'passed',
            'findings'        => 'All parameters within safety thresholds',
            'next_due_date'   => Carbon::now()->addMonths(6)->format('Y-m-d'),
            'status'          => 'completed',
        ]);

        $response->assertRedirect(route('maintenance.index', ['tab' => 'inspections']));
        $this->assertDatabaseHas('inspections', [
            'asset_id'  => $this->asset->id,
            'inspector' => 'Lead Auditor Ramos',
            'result'    => 'passed',
        ]);
    }
}
