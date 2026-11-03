<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetAccessory;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\Attachment;
use App\Models\Inspection;
use App\Models\Maintenance;
use App\Models\MaintenancePart;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use App\Services\AssetLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceModuleEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'      => 'administrator',
            'is_active' => true,
        ]);

        $category = AssetCategory::create(['name' => 'Emergency Vehicles']);
        $type = AssetType::create(['name' => 'Ambulance', 'category_id' => $category->id]);

        $this->asset = Asset::create([
            'name'             => 'Rescue Ambulance Alpha',
            'code'             => 'AMB-001',
            'asset_type_id'    => $type->id,
            'category_id'      => $category->id,
            'status'           => AssetLifecycleService::STATUS_UNDER_MAINTENANCE,
            'condition'        => 'fair',
            'acquisition_cost' => 1500000,
        ]);
    }

    /**
     * Requirement 2: Record Classification and Filter (WO vs SCHED)
     */
    public function test_record_type_filter_separates_work_orders_and_schedules(): void
    {
        $wo = Maintenance::create([
            'asset_id'       => $this->asset->id,
            'type'           => 'repair',
            'priority'       => 'high',
            'technician'     => 'AutoWorks',
            'status'         => 'in_progress',
            'scheduled_date' => now()->toDateString(),
        ]);

        $sched = MaintenanceSchedule::create([
            'asset_id'             => $this->asset->id,
            'asset_type'           => 'Ambulance',
            'maintenance_task'     => 'Engine Oil and Filter Replacement',
            'interval_type'        => 'Monthly',
            'interval_days'        => 30,
            'last_maintenance_date'=> now()->subDays(10)->toDateString(),
        ]);

        // 1. All records
        $responseAll = $this->actingAs($this->admin)->get(route('maintenance.index', ['tab' => 'logs', 'record_type' => 'all']));
        $responseAll->assertOk();
        $responseAll->assertSee($wo->work_order_number);
        $responseAll->assertSee('SCHED-' . str_pad($sched->id, 4, '0', STR_PAD_LEFT));
        $this->assertTrue($responseAll->viewData('maintenances')->contains('id', $wo->id));
        $this->assertTrue($responseAll->viewData('maintenances')->contains('id', $sched->id));

        // 2. Work Orders only
        $responseWO = $this->actingAs($this->admin)->get(route('maintenance.index', ['tab' => 'logs', 'record_type' => 'work_orders']));
        $responseWO->assertOk();
        $responseWO->assertSee($wo->work_order_number);
        $responseWO->assertDontSee('SCHED-' . str_pad($sched->id, 4, '0', STR_PAD_LEFT));
        $this->assertTrue($responseWO->viewData('maintenances')->contains('id', $wo->id));
        $this->assertFalse($responseWO->viewData('maintenances')->contains(fn ($item) => !empty($item->is_schedule) && $item->id === $sched->id));

        // 3. Schedules only
        $responseSched = $this->actingAs($this->admin)->get(route('maintenance.index', ['tab' => 'logs', 'record_type' => 'schedules']));
        $responseSched->assertOk();
        $responseSched->assertSee('SCHED-' . str_pad($sched->id, 4, '0', STR_PAD_LEFT));
        $responseSched->assertDontSee('<i class="fas fa-tools text-[10px]"></i> ' . $wo->work_order_number, false);
        $this->assertTrue($responseSched->viewData('maintenances')->contains('id', $sched->id));
        $this->assertFalse($responseSched->viewData('maintenances')->contains(fn ($item) => empty($item->is_schedule) && $item->id === $wo->id));
    }

    /**
     * Requirement 1 & 3: Table presentations (Parts & Materials header and Service Dates)
     */
    public function test_table_presents_parts_and_materials_and_service_dates(): void
    {
        $sched = MaintenanceSchedule::create([
            'asset_id'              => $this->asset->id,
            'asset_type'            => 'Ambulance',
            'maintenance_task'      => 'Brake Inspection Routine',
            'interval_type'         => 'Quarterly',
            'interval_days'         => 90,
            'last_maintenance_date' => now()->subDays(30)->toDateString(),
            'next_due_date'         => now()->addDays(60)->toDateString(),
        ]);

        $wo = Maintenance::create([
            'asset_id'       => $this->asset->id,
            'type'           => 'repair',
            'priority'       => 'urgent',
            'technician'     => 'QuickFix Shop',
            'status'         => 'completed',
            'scheduled_date' => now()->subDays(2)->toDateString(),
            'started_at'     => now()->subDays(1),
            'completed_at'   => now(),
            'cost'           => 1200.00,
        ]);

        $part = MaintenancePart::create([
            'maintenance_id' => $wo->id,
            'part_name'      => 'High Pressure Fuel Hose',
            'quantity'       => 2,
            'unit'           => 'pcs',
            'unit_cost'      => 600.00,
            'total_cost'     => 1200.00,
        ]);

        $response = $this->actingAs($this->admin)->get(route('maintenance.index', ['tab' => 'logs']));
        $response->assertOk();
        $response->assertSee('Parts &amp; Materials', false);
        $response->assertSee('High Pressure Fuel Hose');
        $response->assertSee('2 pcs');
        $response->assertSee('Last Serviced:');
        $response->assertSee('Next Due:');
        $response->assertSee('Created:');
        $response->assertSee('Scheduled:');
        $response->assertSee('Completed:');
    }

    /**
     * Requirement 5: Parts & Materials inventory consumption & stock deduction
     */
    public function test_parts_consumption_automatically_deducts_inventory_stock(): void
    {
        $attachment = Attachment::create([
            'name'       => 'Hydraulic Seal Kit',
            'type'       => 'Spare Part',
            'quantity'   => 10,
            'unit_cost'  => 450.00,
            'asset_id'   => $this->asset->id,
        ]);

        $accessory = AssetAccessory::create([
            'asset_id'       => $this->asset->id,
            'asset_type'     => 'Ambulance',
            'accessory_name' => 'Spark Plug Set',
            'quantity'       => 8,
            'unit_cost'      => 350.00,
        ]);

        $payload = [
            'asset_id'       => $this->asset->id,
            'type'           => 'repair',
            'priority'       => 'high',
            'technician'     => 'In-house Team',
            'status'         => 'in_progress',
            'scheduled_date' => now()->toDateString(),
            'parts'          => [
                [
                    'inventory_type' => 'attachment',
                    'inventory_id'   => $attachment->id,
                    'part_name'      => 'Hydraulic Seal Kit',
                    'quantity'       => 3,
                    'unit'           => 'sets',
                    'unit_cost'      => 450.00,
                ],
                [
                    'inventory_type' => 'accessory',
                    'inventory_id'   => $accessory->id,
                    'part_name'      => 'Spark Plug Set',
                    'quantity'       => 2,
                    'unit'           => 'sets',
                    'unit_cost'      => 350.00,
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('maintenance.store'), $payload);
        $response->assertRedirect(route('maintenance.index', ['tab' => 'logs']));

        // Verify stock deducted
        $this->assertEquals(7, $attachment->fresh()->quantity);
        $this->assertEquals(6, $accessory->fresh()->quantity);

        $createdWO = Maintenance::latest('id')->first();
        $this->assertCount(2, $createdWO->parts);
        $this->assertStringContainsString('Hydraulic Seal Kit', $createdWO->parts_used);

        // Deleting the work order restores the inventory stock
        $this->actingAs($this->admin)->delete(route('maintenance.destroy', $createdWO));
        $this->assertEquals(10, $attachment->fresh()->quantity);
        $this->assertEquals(8, $accessory->fresh()->quantity);
    }

    /**
     * Requirement 6 & 8: Maintenance completion with operational condition & status verification
     */
    public function test_completing_work_order_requires_operational_verification_and_does_not_blindly_set_available(): void
    {
        $wo = Maintenance::create([
            'asset_id'       => $this->asset->id,
            'type'           => 'repair',
            'priority'       => 'high',
            'technician'     => 'Master Tech',
            'status'         => 'in_progress',
            'scheduled_date' => now()->subDay()->toDateString(),
        ]);

        $this->assertEquals(AssetLifecycleService::STATUS_UNDER_MAINTENANCE, $this->asset->status);

        // Complete with verification: asset could not be fully fixed -> verified as out_of_service and poor condition
        $completePayload = [
            'final_condition'    => 'poor',
            'final_status'       => AssetLifecycleService::STATUS_OUT_OF_SERVICE,
            'verified_by'        => $this->admin->id,
            'verification_notes' => 'Engine block cracked beyond field repair. Decommissioning recommendation submitted.',
            'cost'               => 2500.00,
            'notes'              => 'Major structural failure detected.',
        ];

        $response = $this->actingAs($this->admin)->post(route('maintenance.complete', $wo), $completePayload);
        $response->assertRedirect(route('maintenance.index', ['tab' => 'logs']));

        $woFresh = $wo->fresh();
        $this->assertEquals('completed', $woFresh->status);
        $this->assertEquals('poor', $woFresh->final_condition);
        $this->assertEquals($this->admin->id, $woFresh->verified_by);
        $this->assertStringContainsString('Engine block cracked', $woFresh->verification_notes);

        // No duplicate record created!
        $this->assertEquals(1, Maintenance::count());

        // Asset condition updated and status is OUT_OF_SERVICE (NOT Available!)
        $assetFresh = $this->asset->fresh();
        $this->assertEquals('poor', $assetFresh->condition);
        $this->assertEquals(AssetLifecycleService::STATUS_OUT_OF_SERVICE, $assetFresh->status);
    }

    /**
     * Requirement 7: Asset inspection integration (creating work order from inspection findings)
     */
    public function test_work_order_can_be_created_from_inspection_findings(): void
    {
        $inspection = Inspection::create([
            'asset_id'        => $this->asset->id,
            'inspector'       => 'Lead Safety Inspector Ramos',
            'inspection_date' => now()->toDateString(),
            'result'          => 'failed',
            'findings'        => 'Severe brake fluid leak and defective master cylinder.',
            'status'          => 'completed',
        ]);

        $payload = [
            'asset_id'            => $this->asset->id,
            'inspection_id'       => $inspection->id,
            'type'                => 'repair',
            'priority'            => 'urgent',
            'technician'          => 'Brake Center',
            'status'              => 'in_progress',
            'scheduled_date'      => now()->toDateString(),
            'notes'               => 'Initiated from Inspection #' . $inspection->id . ' - Severe brake fluid leak.',
        ];

        $response = $this->actingAs($this->admin)->post(route('maintenance.store'), $payload);
        $response->assertRedirect(route('maintenance.index', ['tab' => 'logs']));

        $wo = Maintenance::latest('id')->first();
        $this->assertEquals($inspection->id, $wo->inspection_id);
        $this->assertEquals($inspection->id, $wo->inspection->id);
        $this->assertTrue($inspection->workOrders->contains($wo));
    }

    /**
     * Requirement 4: Work order details (show/json) loads all relationships
     */
    public function test_show_maintenance_returns_complete_details_with_relations(): void
    {
        $inspection = Inspection::create([
            'asset_id'        => $this->asset->id,
            'inspector'       => 'Inspector Cruz',
            'inspection_date' => now()->toDateString(),
            'result'          => 'conditional_pass',
            'findings'        => 'Belt tension loose.',
            'status'          => 'completed',
        ]);

        $wo = Maintenance::create([
            'asset_id'           => $this->asset->id,
            'inspection_id'      => $inspection->id,
            'type'               => 'repair',
            'priority'           => 'high',
            'technician'         => 'Tech Auto',
            'status'             => 'completed',
            'final_condition'    => 'good',
            'verified_by'        => $this->admin->id,
            'verification_notes' => 'Tested and approved.',
            'completed_at'       => now(),
            'cost'               => 800.00,
        ]);

        $part = MaintenancePart::create([
            'maintenance_id' => $wo->id,
            'part_name'      => 'Alternator Belt',
            'quantity'       => 1,
            'unit'           => 'pcs',
            'unit_cost'      => 800.00,
            'total_cost'     => 800.00,
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('maintenance.show', $wo));
        $response->assertOk();
        $response->assertJsonPath('id', $wo->id);
        $response->assertJsonPath('inspection.findings', 'Belt tension loose.');
        $response->assertJsonPath('parts.0.part_name', 'Alternator Belt');
        $response->assertJsonPath('verified_by_user.id', $this->admin->id);
    }
}
