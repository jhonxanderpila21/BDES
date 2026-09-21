<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AuditLog;
use App\Models\Deployment;
use App\Models\Location;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private AssetCategory $category;
    private Location $location;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role'      => 'administrator',
            'is_active' => true,
        ]);

        $this->category = AssetCategory::create([
            'name'   => 'Rescue Operations',
            'status' => 'active',
        ]);

        $this->location = Location::create([
            'name'     => 'Depot Storage Bay 1',
            'capacity' => 100,
        ]);
    }

    public function test_asset_creation_and_update_generate_audit_logs(): void
    {
        $response = $this->actingAs($this->user)->post(route('assets.store'), [
            'name'             => 'High-Capacity Submersible Pump',
            'category_id'      => $this->category->id,
            'location_id'      => $this->location->id,
            'condition'        => 'excellent',
            'status'           => 'available',
            'acquisition_cost' => 45000.00,
        ]);
        $response->assertRedirect();

        $asset = Asset::where('name', 'High-Capacity Submersible Pump')->firstOrFail();

        $creationLog = AuditLog::where('auditable_type', Asset::class)
            ->where('auditable_id', $asset->id)
            ->where('event', 'created')
            ->first();

        $this->assertNotNull($creationLog);
        $this->assertEquals($this->user->id, $creationLog->user_id);

        // Update asset
        $this->actingAs($this->user)->put(route('assets.update', $asset), [
            'code'             => $asset->code,
            'name'             => 'High-Capacity Submersible Pump 3-Inch',
            'category_id'      => $this->category->id,
            'location_id'      => $this->location->id,
            'condition'        => 'good',
            'status'           => 'available',
            'acquisition_cost' => 45000.00,
        ]);

        $updateLog = AuditLog::where('auditable_type', Asset::class)
            ->where('auditable_id', $asset->id)
            ->where('event', 'updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($updateLog);
        $this->assertEquals($this->user->id, $updateLog->user_id);
        $this->assertEquals('good', $updateLog->new_values['condition']);
    }

    public function test_deployment_and_return_generate_audit_logs(): void
    {
        $asset = Asset::create([
            'code'             => 'AST-RSC010',
            'name'             => 'Zodiac Inflatable Rescue Boat',
            'category_id'      => $this->category->id,
            'location_id'      => $this->location->id,
            'condition'        => 'excellent',
            'status'           => 'available',
            'acquisition_cost' => 120000.00,
        ]);

        // Deploy
        $this->actingAs($this->user)->post(route('deployment.store'), [
            'asset_id'           => $asset->id,
            'destination'        => 'Riverside Purok 4',
            'responsible_person' => 'Rescue Team Lead Garcia',
            'deployment_date'    => now()->toDateString(),
            'purpose'            => 'Preemptive flood evacuation',
        ]);

        $deployment = Deployment::where('asset_id', $asset->id)->firstOrFail();

        $deployLog = AuditLog::where('auditable_type', Deployment::class)
            ->where('auditable_id', $deployment->id)
            ->where('event', 'created')
            ->first();
        $this->assertNotNull($deployLog);

        // Return
        $this->actingAs($this->user)->post(route('deployment.return', $deployment), [
            'return_date' => now()->toDateString(),
            'condition'   => 'good',
            'status'      => 'received',
        ]);

        $returnLog = AuditLog::where('auditable_type', Asset::class)
            ->where('auditable_id', $asset->id)
            ->where('event', 'status_changed')
            ->first();
        $this->assertNotNull($returnLog);
    }

    public function test_maintenance_lifecycle_generates_audit_logs(): void
    {
        $asset = Asset::create([
            'code'             => 'AST-RSC020',
            'name'             => 'Chainsaw Stihl MS381',
            'category_id'      => $this->category->id,
            'location_id'      => $this->location->id,
            'condition'        => 'fair',
            'status'           => 'available',
            'acquisition_cost' => 32000.00,
        ]);

        $this->actingAs($this->user)->post(route('maintenance.store'), [
            'asset_id'   => $asset->id,
            'type'       => 'preventive',
            'priority'   => 'medium',
            'technician' => 'Brgy Mechanic Santos',
            'cost'       => 1500.00,
            'status'     => 'scheduled',
        ]);

        $maintenance = Maintenance::where('asset_id', $asset->id)->firstOrFail();

        $maintLog = AuditLog::where('auditable_type', Maintenance::class)
            ->where('auditable_id', $maintenance->id)
            ->where('event', 'created')
            ->first();
        $this->assertNotNull($maintLog);

        // Complete
        $this->actingAs($this->user)->post(route('maintenance.complete', $maintenance), [
            'cost'       => 1850.00,
            'parts_used' => 'Replaced spark plug & drive sprocket',
        ]);

        $completeLog = AuditLog::where('auditable_type', Maintenance::class)
            ->where('auditable_id', $maintenance->id)
            ->where('event', 'status_changed')
            ->first();
        $this->assertNotNull($completeLog);
    }

    public function test_asset_history_index_and_export_functionality(): void
    {
        // Seed an audit log
        AuditLog::create([
            'user_id'        => $this->user->id,
            'event'          => 'created',
            'auditable_type' => Asset::class,
            'auditable_id'   => 1,
            'description'    => 'Initial test audit trail entry for export.',
            'ip_address'     => '127.0.0.1',
        ]);

        $indexResponse = $this->actingAs($this->user)->get(route('asset-history.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Initial test audit trail entry for export.');

        $exportResponse = $this->actingAs($this->user)->get(route('asset-history.export'));
        $exportResponse->assertOk();
        $this->assertEquals('text/csv; charset=UTF-8', $exportResponse->headers->get('content-type'));
    }
}
