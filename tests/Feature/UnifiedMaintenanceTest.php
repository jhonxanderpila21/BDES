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
    private User $staff;
    private Asset $asset;

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

    public function test_staff_can_view_maintenance_hub_schedules_tab_with_read_only_badge_and_no_add_schedule_button(): void
    {
        $schedule = MaintenanceSchedule::create([
            'asset_id'              => $this->asset->id,
            'asset_type'            => 'Generator',
            'maintenance_task'      => 'Bi-Monthly Belt Inspection',
            'interval_type'         => 'Monthly',
            'interval_days'         => 60,
            'last_maintenance_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->staff)->get(route('maintenance.index', ['tab' => 'schedules']));

        $response->assertStatus(200);
        $response->assertSee('Read-Only Staff Access');
        $response->assertSee('Bi-Monthly Belt Inspection');
        $response->assertSee('openViewScheduleModal');
        $response->assertDontSee('Add Schedule');
        $response->assertDontSee('openAddScheduleModal');
        $response->assertDontSee('openEditScheduleModal');
        $response->assertDontSee('route(\'maintenance-schedules.destroy\'');
    }

    public function test_staff_can_view_maintenance_logs_tab_with_read_only_badge_and_no_log_maintenance_button(): void
    {
        $maintenance = Maintenance::create([
            'asset_id'           => $this->asset->id,
            'type'               => 'routine',
            'priority'           => 'high',
            'technician'         => 'Chief Mechanic Perez',
            'scheduled_date'     => Carbon::now()->format('Y-m-d'),
            'estimated_duration' => '4 hours',
            'parts_used'         => 'Oil gasket, filter',
            'notes'              => 'Quarterly overhaul check',
            'status'             => 'in_progress',
        ]);

        $response = $this->actingAs($this->staff)->get(route('maintenance.index', ['tab' => 'logs']));

        $response->assertStatus(200);
        $response->assertSee('Read-Only Staff Access');
        $response->assertSee('Chief Mechanic Perez');
        $response->assertSee('Quarterly overhaul check');
        $response->assertSee('openViewMaintenanceModal');
        $response->assertDontSee('Log Maintenance');
        $response->assertDontSee('openCreateMaintenanceModal');
        $response->assertDontSee('openEditMaintenanceModal');
        $response->assertDontSee('route(\'maintenance.destroy\'');
        // Linked asset code should be plain text without link to Asset Registry
        $response->assertDontSee(route('assets.show', $this->asset->id));
        $response->assertSee('[' . $this->asset->code . ']');
    }

    public function test_staff_can_view_inspections_tab_with_read_only_badge_and_no_record_inspection_button(): void
    {
        $inspection = Inspection::create([
            'asset_id'        => $this->asset->id,
            'inspector'       => 'Safety Inspector Santos',
            'inspection_date' => Carbon::now()->format('Y-m-d'),
            'result'          => 'passed',
            'findings'        => 'Wiring and housing intact',
            'status'          => 'completed',
        ]);

        $response = $this->actingAs($this->staff)->get(route('maintenance.index', ['tab' => 'inspections']));

        $response->assertStatus(200);
        $response->assertSee('Read-Only Staff Access');
        $response->assertSee('Safety Inspector Santos');
        $response->assertSee('Wiring and housing intact');
        $response->assertSee('openViewInspectionModal');
        $response->assertDontSee('Record Inspection');
        $response->assertDontSee('openCreateInspectionModal');
        $response->assertDontSee('openEditInspectionModal');
        $response->assertDontSee('route(\'inspection.destroy\'');
    }

    public function test_staff_can_use_search_and_filters_across_all_tabs(): void
    {
        $schedule1 = MaintenanceSchedule::create([
            'asset_id'              => $this->asset->id,
            'asset_type'            => 'Generator',
            'maintenance_task'      => 'Alternator Testing',
            'interval_type'         => 'Monthly',
            'interval_days'         => 30,
            'last_maintenance_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
        ]);

        $schedule2 = MaintenanceSchedule::create([
            'asset_id'              => $this->asset->id,
            'asset_type'            => 'Generator',
            'maintenance_task'      => 'Spark Plug Degreasing',
            'interval_type'         => 'Yearly',
            'interval_days'         => 365,
            'last_maintenance_date' => Carbon::now()->subDays(100)->format('Y-m-d'),
        ]);

        // Search in Schedules
        $response = $this->actingAs($this->staff)->get(route('maintenance.index', [
            'tab'    => 'schedules',
            'search' => 'Alternator',
        ]));
        $response->assertStatus(200);
        $response->assertSee('Alternator Testing');
        $response->assertDontSee('Spark Plug Degreasing');

        // Filter by interval in Schedules
        $responseInterval = $this->actingAs($this->staff)->get(route('maintenance.index', [
            'tab'           => 'schedules',
            'interval_type' => 'Yearly',
        ]));
        $responseInterval->assertStatus(200);
        $responseInterval->assertSee('Spark Plug Degreasing');
        $responseInterval->assertDontSee('Alternator Testing');

        // Search in Logs
        Maintenance::create([
            'asset_id'       => $this->asset->id,
            'type'           => 'routine',
            'technician'     => 'Electrician Mario',
            'scheduled_date' => Carbon::now()->format('Y-m-d'),
            'status'         => 'scheduled',
            'notes'          => 'Check voltage regulator',
        ]);
        Maintenance::create([
            'asset_id'       => $this->asset->id,
            'type'           => 'repair',
            'technician'     => 'Plumber Luigi',
            'scheduled_date' => Carbon::now()->format('Y-m-d'),
            'status'         => 'completed',
            'notes'          => 'Fix coolant valve',
        ]);

        $responseLogs = $this->actingAs($this->staff)->get(route('maintenance.index', [
            'tab'    => 'logs',
            'search' => 'Luigi',
        ]));
        $responseLogs->assertStatus(200);
        $responseLogs->assertSee('Plumber Luigi');
        $responseLogs->assertDontSee('Electrician Mario');

        // Search in Inspections
        Inspection::create([
            'asset_id'        => $this->asset->id,
            'inspector'       => 'Auditor Diaz',
            'inspection_date' => Carbon::now()->format('Y-m-d'),
            'result'          => 'conditional_pass',
            'findings'        => 'Belt friction wear detected',
            'status'          => 'completed',
        ]);
        Inspection::create([
            'asset_id'        => $this->asset->id,
            'inspector'       => 'Officer Ramos',
            'inspection_date' => Carbon::now()->format('Y-m-d'),
            'result'          => 'passed',
            'findings'        => 'Flawless condition',
            'status'          => 'completed',
        ]);

        $responseInsp = $this->actingAs($this->staff)->get(route('maintenance.index', [
            'tab'    => 'inspections',
            'search' => 'friction',
        ]));
        $responseInsp->assertStatus(200);
        $responseInsp->assertSee('Auditor Diaz');
        $responseInsp->assertDontSee('Officer Ramos');
    }

    public function test_staff_can_view_complete_record_details_via_show_endpoints(): void
    {
        $schedule = MaintenanceSchedule::create([
            'asset_id'              => $this->asset->id,
            'asset_type'            => 'Generator',
            'maintenance_task'      => 'Fuel System Purge',
            'interval_type'         => 'Monthly',
            'interval_days'         => 30,
            'last_maintenance_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
        ]);

        $maintenance = Maintenance::create([
            'asset_id'           => $this->asset->id,
            'type'               => 'routine',
            'technician'         => 'Lead Specialist David',
            'scheduled_date'     => Carbon::now()->format('Y-m-d'),
            'status'             => 'in_progress',
            'cost'               => 1250.50,
            'parts_used'         => 'Primary filter',
            'notes'              => 'Standard fuel overhaul',
        ]);

        $inspection = Inspection::create([
            'asset_id'        => $this->asset->id,
            'inspector'       => 'Safety Officer Reyes',
            'inspection_date' => Carbon::now()->format('Y-m-d'),
            'result'          => 'passed',
            'findings'        => 'Exhaust levels nominal',
            'status'          => 'completed',
        ]);

        // 1. Schedule details endpoint
        $schedResponse = $this->actingAs($this->staff)->getJson(route('maintenance-schedules.show', $schedule));
        $schedResponse->assertStatus(200);
        $schedResponse->assertJsonPath('maintenance_task', 'Fuel System Purge');
        $schedResponse->assertJsonPath('asset.code', 'GEN-UNIFIED');

        // 2. Maintenance details endpoint
        $maintResponse = $this->actingAs($this->staff)->getJson(route('maintenance.show', $maintenance));
        $maintResponse->assertStatus(200);
        $maintResponse->assertJsonPath('technician', 'Lead Specialist David');
        $maintResponse->assertJsonPath('cost', '1250.50');

        // 3. Inspection details endpoint
        $inspResponse = $this->actingAs($this->staff)->getJson(route('inspection.show', $inspection));
        $inspResponse->assertStatus(200);
        $inspResponse->assertJsonPath('inspector', 'Safety Officer Reyes');
        $inspResponse->assertJsonPath('findings', 'Exhaust levels nominal');
    }

    public function test_staff_cannot_create_update_complete_or_delete_maintenance_logs(): void
    {
        $maintenance = Maintenance::create([
            'asset_id'       => $this->asset->id,
            'type'           => 'repair',
            'technician'     => 'Tech Alpha',
            'scheduled_date' => Carbon::now()->format('Y-m-d'),
            'status'         => 'in_progress',
        ]);

        // Attempt Create
        $responseCreate = $this->actingAs($this->staff)->get(route('maintenance.create'));
        $responseCreate->assertStatus(403);

        // Attempt Store
        $responseStore = $this->actingAs($this->staff)->post(route('maintenance.store'), [
            'asset_id'       => $this->asset->id,
            'type'           => 'repair',
            'technician'     => 'Unauthorized Staff',
            'scheduled_date' => Carbon::now()->format('Y-m-d'),
            'status'         => 'in_progress',
        ]);
        $responseStore->assertStatus(403);

        // Attempt Edit
        $responseEdit = $this->actingAs($this->staff)->get(route('maintenance.edit', $maintenance));
        $responseEdit->assertStatus(403);

        // Attempt Update
        $responseUpdate = $this->actingAs($this->staff)->put(route('maintenance.update', $maintenance), [
            'asset_id'       => $this->asset->id,
            'type'           => 'repair',
            'technician'     => 'Tampered Tech',
            'status'         => 'in_progress',
        ]);
        $responseUpdate->assertStatus(403);

        // Attempt Complete
        $responseComplete = $this->actingAs($this->staff)->post(route('maintenance.complete', $maintenance), [
            'cost' => 500,
        ]);
        $responseComplete->assertStatus(403);

        // Attempt Delete
        $responseDelete = $this->actingAs($this->staff)->delete(route('maintenance.destroy', $maintenance));
        $responseDelete->assertStatus(403);

        $this->assertDatabaseHas('maintenances', [
            'id'         => $maintenance->id,
            'technician' => 'Tech Alpha',
        ]);
    }

    public function test_staff_cannot_create_update_complete_or_delete_preventive_schedules(): void
    {
        $schedule = MaintenanceSchedule::create([
            'asset_id'              => $this->asset->id,
            'asset_type'            => 'Generator',
            'maintenance_task'      => 'Spark Check',
            'interval_type'         => 'Monthly',
            'interval_days'         => 30,
            'last_maintenance_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
        ]);

        // Attempt Store
        $responseStore = $this->actingAs($this->staff)->post(route('maintenance-schedules.store'), [
            'asset_id'              => $this->asset->id,
            'maintenance_task'      => 'Unauthorized Task',
            'interval_type'         => 'Monthly',
            'interval_days'         => 30,
            'last_maintenance_date' => Carbon::now()->format('Y-m-d'),
        ]);
        $responseStore->assertStatus(403);

        // Attempt Update
        $responseUpdate = $this->actingAs($this->staff)->put(route('maintenance-schedules.update', $schedule), [
            'asset_id'              => $this->asset->id,
            'maintenance_task'      => 'Tampered Spark Check',
            'interval_type'         => 'Monthly',
            'interval_days'         => 30,
            'last_maintenance_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
        ]);
        $responseUpdate->assertStatus(403);

        // Attempt Complete
        $responseComplete = $this->actingAs($this->staff)->post(route('maintenance-schedules.complete', $schedule), [
            'completion_date' => Carbon::now()->format('Y-m-d'),
        ]);
        $responseComplete->assertStatus(403);

        // Attempt Delete
        $responseDelete = $this->actingAs($this->staff)->delete(route('maintenance-schedules.destroy', $schedule));
        $responseDelete->assertStatus(403);

        $this->assertDatabaseHas('maintenance_schedules', [
            'id'               => $schedule->id,
            'maintenance_task' => 'Spark Check',
        ]);
    }

    public function test_staff_cannot_create_update_or_delete_inspections(): void
    {
        $inspection = Inspection::create([
            'asset_id'        => $this->asset->id,
            'inspector'       => 'Authorized Inspector',
            'inspection_date' => Carbon::now()->format('Y-m-d'),
            'result'          => 'passed',
            'status'          => 'completed',
        ]);

        // Attempt Create
        $responseCreate = $this->actingAs($this->staff)->get(route('inspection.create'));
        $responseCreate->assertStatus(403);

        // Attempt Store
        $responseStore = $this->actingAs($this->staff)->post(route('inspection.store'), [
            'asset_id'        => $this->asset->id,
            'inspector'       => 'Unauthorized Staff Member',
            'inspection_date' => Carbon::now()->format('Y-m-d'),
            'result'          => 'passed',
            'status'          => 'completed',
        ]);
        $responseStore->assertStatus(403);

        // Attempt Edit
        $responseEdit = $this->actingAs($this->staff)->get(route('inspection.edit', $inspection));
        $responseEdit->assertStatus(403);

        // Attempt Update
        $responseUpdate = $this->actingAs($this->staff)->put(route('inspection.update', $inspection), [
            'asset_id'        => $this->asset->id,
            'inspector'       => 'Tampered Inspector',
            'inspection_date' => Carbon::now()->format('Y-m-d'),
            'result'          => 'failed',
            'status'          => 'completed',
        ]);
        $responseUpdate->assertStatus(403);

        // Attempt Delete
        $responseDelete = $this->actingAs($this->staff)->delete(route('inspection.destroy', $inspection));
        $responseDelete->assertStatus(403);

        $this->assertDatabaseHas('inspections', [
            'id'        => $inspection->id,
            'inspector' => 'Authorized Inspector',
        ]);
    }

    public function test_administrator_can_create_update_complete_and_delete_schedules(): void
    {
        // 1. Create Schedule
        $storeResponse = $this->actingAs($this->user)->post(route('maintenance-schedules.store'), [
            'asset_id'              => $this->asset->id,
            'maintenance_task'      => 'Admin Scheduled Servicing',
            'interval_type'         => 'Monthly',
            'interval_days'         => 30,
            'last_maintenance_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
        ]);
        $storeResponse->assertRedirect(route('maintenance-schedules.index'));
        $this->assertDatabaseHas('maintenance_schedules', [
            'maintenance_task' => 'Admin Scheduled Servicing',
        ]);

        $schedule = MaintenanceSchedule::where('maintenance_task', 'Admin Scheduled Servicing')->firstOrFail();

        // 2. Update Schedule
        $updateResponse = $this->actingAs($this->user)->put(route('maintenance-schedules.update', $schedule), [
            'asset_id'              => $this->asset->id,
            'maintenance_task'      => 'Admin Scheduled Servicing Updated',
            'interval_type'         => 'Quarterly',
            'interval_days'         => 90,
            'last_maintenance_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
        ]);
        $updateResponse->assertRedirect(route('maintenance-schedules.index'));
        $this->assertDatabaseHas('maintenance_schedules', [
            'id'               => $schedule->id,
            'maintenance_task' => 'Admin Scheduled Servicing Updated',
            'interval_type'    => 'Quarterly',
        ]);

        // 3. Complete Schedule
        $completeResponse = $this->actingAs($this->user)->post(route('maintenance-schedules.complete', $schedule), [
            'completion_date' => Carbon::now()->format('Y-m-d'),
            'technician'      => 'Senior Admin Tech',
            'notes'           => 'Completed as per manual',
        ]);
        $completeResponse->assertRedirect(route('maintenance-schedules.index'));
        $this->assertEquals(Carbon::now()->format('Y-m-d'), $schedule->fresh()->last_maintenance_date->format('Y-m-d'));

        // 4. Delete Schedule
        $deleteResponse = $this->actingAs($this->user)->delete(route('maintenance-schedules.destroy', $schedule));
        $deleteResponse->assertRedirect(route('maintenance-schedules.index'));
        $this->assertDatabaseMissing('maintenance_schedules', [
            'id' => $schedule->id,
        ]);
    }
}
