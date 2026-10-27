<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetAccessory;
use App\Models\AssetCategory;
use App\Models\AssetNeed;
use App\Models\AssetType;
use App\Models\Attachment;
use App\Models\Deployment;
use App\Models\Inspection;
use App\Models\Location;
use App\Models\MaintenanceSchedule;
use App\Models\OperationalRequirement;
use App\Models\Receipt;
use App\Models\ReportedAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UniversalFileUploadTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private AssetCategory $category;
    private AssetType $type;
    private Location $location;
    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(Receipt::DISK);

        $this->admin = User::factory()->create([
            'role'      => 'administrator',
            'is_active' => true,
        ]);

        $this->category = AssetCategory::create([
            'name'        => 'Emergency Rescue Equipment',
            'description' => 'Rescue boats and life saving kits',
            'status'      => 'active',
        ]);

        $this->type = AssetType::create([
            'name'        => 'Rescue Inflatable Boat',
            'category_id' => $this->category->id,
        ]);

        $this->location = Location::create([
            'name'     => 'Barangay Rescue Station 1',
            'capacity' => 50,
        ]);

        $this->asset = Asset::create([
            'code'             => 'BOAT-2026-001',
            'name'             => 'Inflatable Rescue Craft Alpha',
            'category_id'      => $this->category->id,
            'type_id'          => $this->type->id,
            'location_id'      => $this->location->id,
            'acquisition_cost' => 125000.00,
            'acquisition_date' => now()->toDateString(),
            'status'           => 'available',
            'condition'        => 'good',
        ]);
    }

    public function test_attachment_creation_supports_file_uploads(): void
    {
        $file = UploadedFile::fake()->create('propeller_specs.pdf', 300, 'application/pdf');

        $response = $this->actingAs($this->admin)->post(route('attachments.store'), [
            'name'                     => 'Outboard Motor Propeller',
            'type'                     => 'Mechanical',
            'asset_id'                 => $this->asset->id,
            'acquisition_cost'         => 18500.00,
            'notes'                    => 'Heavy duty propeller unit',
            'receipt_reference_number' => 'OR-ATTACH-001',
            'receipts'                 => [$file],
        ]);

        $response->assertRedirect(route('attachments.index', ['tab' => 'attachments']));
        $attachment = Attachment::where('name', 'Outboard Motor Propeller')->firstOrFail();

        $this->assertCount(1, $attachment->receipts);
        $this->assertEquals('OR-ATTACH-001', $attachment->receipts->first()->reference_number);
        Storage::disk(Receipt::DISK)->assertExists($attachment->receipts->first()->file_path);
    }

    public function test_accessory_creation_supports_file_uploads(): void
    {
        $file = UploadedFile::fake()->create('life_vest.png', 200, 'image/png');

        $response = $this->actingAs($this->admin)->post(route('asset-accessories.store'), [
            'accessory_name'           => 'SOLAS Rescue Vest',
            'asset_id'                 => $this->asset->id,
            'unit_cost'                => 3500.00,
            'quantity'                 => 4,
            'receipt_reference_number' => 'OR-ACC-001',
            'receipts'                 => [$file],
        ]);

        $response->assertRedirect(route('asset-accessories.index'));
        $accessory = AssetAccessory::where('accessory_name', 'SOLAS Rescue Vest')->firstOrFail();

        $this->assertCount(1, $accessory->receipts);
        $this->assertEquals('OR-ACC-001', $accessory->receipts->first()->reference_number);
        Storage::disk(Receipt::DISK)->assertExists($accessory->receipts->first()->file_path);
    }

    public function test_operational_requirement_supports_file_uploads(): void
    {
        $file = UploadedFile::fake()->create('two_stroke_oil_invoice.pdf', 250, 'application/pdf');

        $response = $this->actingAs($this->admin)->post(route('operational-requirements.store'), [
            'asset_type_id'            => $this->type->id,
            'requirement_type'         => '2-Stroke Marine Engine Oil',
            'unit'                     => 'Liters',
            'maximum_capacity'         => 50.00,
            'minimum_level'            => 10.00,
            'current_level'            => 25.00,
            'description'              => 'Marine grade lubricant',
            'receipt_reference_number' => 'INV-OIL-2026',
            'receipts'                 => [$file],
        ]);

        $response->assertRedirect(route('operational-requirements.index'));
        $req = OperationalRequirement::where('requirement_type', '2-Stroke Marine Engine Oil')->firstOrFail();

        $this->assertCount(1, $req->receipts);
        $this->assertEquals('INV-OIL-2026', $req->receipts->first()->reference_number);
        Storage::disk(Receipt::DISK)->assertExists($req->receipts->first()->file_path);
    }

    public function test_deployment_supports_resident_borrowing_slip_upload(): void
    {
        $file = UploadedFile::fake()->create('resident_id_photo.jpg', 200, 'image/jpeg');

        $response = $this->actingAs($this->admin)->post(route('deployment.store'), [
            'asset_id'                  => $this->asset->id,
            'borrower_type'             => 'resident',
            'borrower_name'             => 'Pedro Kalungsod',
            'borrower_contact'          => '0917-123-4567',
            'borrower_address'          => 'Purok 4 Riverside',
            'borrower_id_type'          => 'Barangay ID',
            'borrower_id_number'        => 'BRGY-ID-9988',
            'destination'               => 'Riverside Evacuation Area',
            'deployment_date'           => now()->toDateString(),
            'condition_before'          => 'good',
            'purpose'                   => 'Emergency water evacuation assist',
            'receipt_reference_number'  => 'BRGY-SLIP-404',
            'receipts'                  => [$file],
        ]);

        $response->assertRedirect(route('deployment.index'));
        $deployment = Deployment::where('asset_id', $this->asset->id)->latest()->firstOrFail();

        $this->assertCount(1, $deployment->receipts);
        Storage::disk(Receipt::DISK)->assertExists($deployment->receipts->first()->file_path);
    }

    public function test_deployment_return_supports_condition_evidence_upload(): void
    {
        $deployment = Deployment::create([
            'asset_id'                  => $this->asset->id,
            'borrower_type'             => 'resident',
            'borrower_name'             => 'Maria Makiling',
            'destination'               => 'Zone 2 Evac Center',
            'deployment_date'           => now()->subDays(2)->toDateString(),
            'status'                    => 'active',
        ]);
        $this->asset->update(['status' => 'deployed']);

        $file = UploadedFile::fake()->create('return_photo.jpg', 200, 'image/jpeg');

        $response = $this->actingAs($this->admin)->post(route('deployment.return', $deployment->id), [
            'return_date'              => now()->toDateString(),
            'condition'                => 'good',
            'status'                   => 'received',
            'damage_notes'             => 'Turned in clean and complete',
            'receipt_reference_number' => 'TURN-IN-2026',
            'receipts'                 => [$file],
        ]);

        $response->assertRedirect(route('deployment.index'));
        $deployment->refresh();

        $this->assertEquals('returned', $deployment->status);
        $this->assertCount(1, $deployment->receipts);
        Storage::disk(Receipt::DISK)->assertExists($deployment->receipts->first()->file_path);
    }

    public function test_maintenance_schedule_supports_procedure_file_upload(): void
    {
        $file = UploadedFile::fake()->create('valve_clearance_guide.pdf', 400, 'application/pdf');

        $response = $this->actingAs($this->admin)->post(route('maintenance-schedules.store'), [
            'asset_id'              => $this->asset->id,
            'maintenance_task'      => 'Boat Engine Valve Clearance Check',
            'interval_type'         => 'Quarterly',
            'interval_days'         => 90,
            'last_maintenance_date' => now()->toDateString(),
            'receipts'              => [$file],
        ]);

        $response->assertRedirect(route('maintenance-schedules.index'));
        $schedule = MaintenanceSchedule::where('asset_id', $this->asset->id)->firstOrFail();

        $this->assertCount(1, $schedule->receipts);
        Storage::disk(Receipt::DISK)->assertExists($schedule->receipts->first()->file_path);
    }

    public function test_inspection_record_supports_inspection_report_upload(): void
    {
        $file = UploadedFile::fake()->create('hull_crack_check.png', 200, 'image/png');

        $response = $this->actingAs($this->admin)->post(route('inspection.store'), [
            'asset_id'        => $this->asset->id,
            'inspector'       => 'Engr. Bautista',
            'inspection_date' => now()->toDateString(),
            'result'          => 'passed',
            'status'          => 'completed',
            'findings'        => 'Rigid hull integrity sound',
            'receipts'        => [$file],
        ]);

        $response->assertRedirect(route('maintenance.index', ['tab' => 'inspections']));
        $inspection = Inspection::where('asset_id', $this->asset->id)->firstOrFail();

        $this->assertCount(1, $inspection->receipts);
        Storage::disk(Receipt::DISK)->assertExists($inspection->receipts->first()->file_path);
    }

    public function test_reported_asset_supports_photo_evidence_upload(): void
    {
        $file = UploadedFile::fake()->create('puncture_leak.jpg', 200, 'image/jpeg');

        $response = $this->actingAs($this->admin)->post(route('reported-assets.store'), [
            'asset_id'      => $this->asset->id,
            'reported_type' => 'Damage',
            'reported_date' => now()->toDateString(),
            'location'      => 'Storage Bay 3',
            'description'   => 'Slow air leak on port side chamber',
            'action'        => 'Patch kit needed',
            'receipts'      => [$file],
        ]);

        $response->assertRedirect(route('reported-assets.index'));
        $report = ReportedAsset::where('asset_id', $this->asset->id)->firstOrFail();

        $this->assertCount(1, $report->receipts);
        Storage::disk(Receipt::DISK)->assertExists($report->receipts->first()->file_path);
    }

    public function test_asset_need_supports_specification_file_upload(): void
    {
        $file = UploadedFile::fake()->create('battery_specs.pdf', 350, 'application/pdf');

        $response = $this->actingAs($this->admin)->post(route('asset-needs.store'), [
            'asset_id'         => $this->asset->id,
            'requirement'      => 'Marine Deep Cycle Battery 12V 100Ah',
            'unit'             => 'Liters',
            'maximum_capacity' => 4,
            'minimum_level'    => 1,
            'current_level'    => 1,
            'receipts'         => [$file],
        ]);

        $response->assertRedirect(route('asset-needs.index'));
        $need = AssetNeed::where('asset_id', $this->asset->id)->firstOrFail();

        $this->assertCount(1, $need->receipts);
        Storage::disk(Receipt::DISK)->assertExists($need->receipts->first()->file_path);
    }
}
