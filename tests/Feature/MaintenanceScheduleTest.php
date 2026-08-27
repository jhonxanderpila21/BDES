<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\Location;
use App\Models\Maintenance;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceScheduleTest extends TestCase
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
            'name'        => 'Emergency Equipment',
            'description' => 'Test category',
        ]);

        $type = AssetType::create([
            'name'        => 'Generator',
            'category_id' => $category->id,
        ]);

        $location = Location::create([
            'name'     => 'Barangay Hall',
            'capacity' => 100,
        ]);

        $this->asset = Asset::create([
            'code'        => 'GEN-TEST',
            'name'        => 'Test Generator',
            'category_id' => $category->id,
            'type_id'     => $type->id,
            'location_id' => $location->id,
            'condition'   => 'good',
            'status'      => 'available',
        ]);
    }

    public function test_index_displays_schedules(): void
    {
        MaintenanceSchedule::create([
            'asset_id'              => $this->asset->id,
            'asset_type'            => 'Generator',
            'maintenance_task'      => 'Oil Change',
            'interval_type'         => 'Monthly',
            'interval_days'         => 30,
            'last_maintenance_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->user)->get(route('maintenance-schedules.index'));

        $response->assertStatus(200);
        $response->assertSee('Oil Change');
        $response->assertSee('Monthly');
    }

    public function test_store_creates_schedule_with_auto_status(): void
    {
        $response = $this->actingAs($this->user)->post(route('maintenance-schedules.store'), [
            'asset_id'              => $this->asset->id,
            'maintenance_task'      => 'Test Task',
            'interval_type'         => 'Quarterly',
            'interval_days'         => 90,
            'last_maintenance_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('maintenance-schedules.index'));
        $this->assertDatabaseHas('maintenance_schedules', [
            'maintenance_task' => 'Test Task',
            'asset_type'       => 'Generator',
            'status'           => 'Monitoring Only',
        ]);
    }

    public function test_update_modifies_schedule_and_recomputes_status(): void
    {
        $schedule = MaintenanceSchedule::create([
            'asset_id'              => $this->asset->id,
            'asset_type'            => 'Generator',
            'maintenance_task'      => 'Test Task',
            'interval_type'         => 'Monthly',
            'interval_days'         => 30,
            'last_maintenance_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
        ]);

        $this->assertEquals('Monitoring Only', $schedule->status);

        $response = $this->actingAs($this->user)->put(route('maintenance-schedules.update', $schedule), [
            'asset_id'              => $this->asset->id,
            'maintenance_task'      => 'Test Task Updated',
            'interval_type'         => 'Monthly',
            'interval_days'         => 30,
            'last_maintenance_date' => Carbon::now()->subDays(40)->format('Y-m-d'), // Makes it overdue (30 + 7 < 40)
        ]);

        $response->assertRedirect(route('maintenance-schedules.index'));
        
        $schedule->refresh();
        $this->assertEquals('Test Task Updated', $schedule->maintenance_task);
        $this->assertEquals('Overdue', $schedule->status);
    }

    public function test_destroy_deletes_schedule(): void
    {
        $schedule = MaintenanceSchedule::create([
            'asset_id'              => $this->asset->id,
            'asset_type'            => 'Generator',
            'maintenance_task'      => 'Test Task',
            'interval_type'         => 'Monthly',
            'interval_days'         => 30,
            'last_maintenance_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->user)->delete(route('maintenance-schedules.destroy', $schedule));

        $response->assertRedirect(route('maintenance-schedules.index'));
        $this->assertDatabaseMissing('maintenance_schedules', ['id' => $schedule->id]);
    }

    public function test_complete_marks_schedule_done_and_creates_history(): void
    {
        $schedule = MaintenanceSchedule::create([
            'asset_id'              => $this->asset->id,
            'asset_type'            => 'Generator',
            'maintenance_task'      => 'Test Task',
            'interval_type'         => 'Monthly',
            'interval_days'         => 30,
            'last_maintenance_date' => Carbon::now()->subDays(40)->format('Y-m-d'),
        ]);

        $this->assertEquals('Overdue', $schedule->status);

        $completionDate = Carbon::now()->format('Y-m-d');

        $response = $this->actingAs($this->user)->post(route('maintenance-schedules.complete', $schedule), [
            'completion_date' => $completionDate,
            'technician'      => 'John Doe',
            'notes'           => 'Fixed it good.',
        ]);

        $response->assertRedirect(route('maintenance-schedules.index'));

        $schedule->refresh();
        $this->assertEquals($completionDate, $schedule->last_maintenance_date->format('Y-m-d'));
        $this->assertEquals('Monitoring Only', $schedule->status); // Now back to monitoring

        // Check history log
        $this->assertDatabaseHas('maintenances', [
            'asset_id'       => $this->asset->id,
            'type'           => 'preventive',
            'technician'     => 'John Doe',
            // SQLite returns dates with time appended
            'scheduled_date' => $completionDate . ' 00:00:00',
            'status'         => 'completed',
        ]);
        
        $history = Maintenance::latest()->first();
        $this->assertStringContainsString('Completed preventive maintenance: Test Task', $history->notes);
        $this->assertStringContainsString('Fixed it good.', $history->notes);
    }
}
