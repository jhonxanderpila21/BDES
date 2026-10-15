<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetDocument;
use App\Models\AssetType;
use App\Models\Deployment;
use App\Models\Location;
use App\Models\OperationalRequirement;
use App\Models\ReportedAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityAndInputHardeningTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $member;
    private User $viewer;
    private Asset $asset;
    private AssetCategory $category;
    private Location $location;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'      => 'administrator',
            'is_active' => true,
        ]);

        $this->member = User::factory()->create([
            'role'      => 'bdrrmc_officer',
            'is_active' => true,
        ]);

        $this->viewer = User::factory()->create([
            'role'      => 'viewer',
            'is_active' => true,
        ]);

        $this->category = AssetCategory::create([
            'name'   => 'Rescue & Emergency',
            'status' => 'active',
        ]);

        $this->location = Location::create([
            'name'             => 'Central Disaster Depot',
            'storage_capacity' => 100,
        ]);

        $this->asset = Asset::create([
            'code'             => 'AST-HARDEN01',
            'name'             => 'High-Pressure Fire Nozzle',
            'category_id'      => $this->category->id,
            'location_id'      => $this->location->id,
            'condition'        => 'excellent',
            'status'           => 'available',
            'acquisition_cost' => 18500.00,
        ]);
    }

    public function test_document_upload_rejects_dangerous_file_extensions(): void
    {
        Storage::fake('public');

        $dangerousFile = UploadedFile::fake()->create('malicious_script.php', 100, 'application/x-php');

        $response = $this->actingAs($this->member)->post(route('assets.documents.store', $this->asset), [
            'title'         => 'Backdoor Exploit Attempt',
            'document_type' => 'other',
            'document_file' => $dangerousFile,
        ]);

        $response->assertSessionHasErrors('document_file');
        $this->assertDatabaseMissing('asset_documents', [
            'asset_id' => $this->asset->id,
            'title'    => 'Backdoor Exploit Attempt',
        ]);
    }

    public function test_document_upload_accepts_valid_pdf_and_sanitizes_filename(): void
    {
        Storage::fake('public');

        $safeFile = UploadedFile::fake()->create('Equipment Manual @#$ 2026.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->member)->post(route('assets.documents.store', $this->asset), [
            'title'         => 'Nozzle Operating Manual',
            'document_type' => 'manual',
            'document_file' => $safeFile,
            'notes'         => 'Official manufacturer user guide.',
        ]);

        $response->assertRedirect(route('assets.show', $this->asset));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('asset_documents', [
            'asset_id'      => $this->asset->id,
            'title'         => 'Nozzle Operating Manual',
            'document_type' => 'manual',
        ]);

        $document = AssetDocument::where('asset_id', $this->asset->id)->first();
        $this->assertNotNull($document);
        $this->assertStringNotContainsString('@#$', $document->file_name);
        Storage::disk('public')->assertExists($document->file_path);
    }

    public function test_document_download_requires_authentication(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('manual.pdf', 200, 'application/pdf');
        $path = $file->store('asset-documents/' . $this->asset->id, 'public');

        $document = AssetDocument::create([
            'asset_id'      => $this->asset->id,
            'title'         => 'Protected Document',
            'document_type' => 'manual',
            'file_path'     => $path,
            'file_name'     => 'manual.pdf',
            'file_size'     => 200,
            'mime_type'     => 'application/pdf',
            'uploaded_by'   => $this->admin->id,
        ]);

        // Unauthenticated access redirects to login
        $response = $this->get(route('assets.documents.download', $document));
        $response->assertRedirect(route('login'));

        // Authenticated member can download
        $memberResponse = $this->actingAs($this->member)->get(route('assets.documents.download', $document));
        $memberResponse->assertOk();
    }

    public function test_asset_input_normalization_capitalizes_code_and_cleans_currency(): void
    {
        $response = $this->actingAs($this->member)->put(route('assets.update', $this->asset), [
            'code'             => 'ast-harden01', // Lowercase test
            'name'             => '<b>High-Pressure Fire Nozzle (Updated)</b>', // HTML test
            'category_id'      => $this->category->id,
            'location_id'      => $this->location->id,
            'condition'        => 'good',
            'status'           => 'available',
            'acquisition_cost' => '₱ 24,500.00', // Formatted currency with symbols test
        ]);

        $response->assertRedirect(route('assets.index', ['tab' => 'assets']));
        $this->asset->refresh();

        $this->assertEquals('AST-HARDEN01', $this->asset->code);
        $this->assertEquals('High-Pressure Fire Nozzle (Updated)', $this->asset->name);
        $this->assertEquals(24500.00, (float) $this->asset->acquisition_cost);
    }

    public function test_deployment_return_rejects_return_date_prior_to_deployment(): void
    {
        $deployment = Deployment::create([
            'asset_id'        => $this->asset->id,
            'destination'     => 'Zone 3 Outpost',
            'deployment_date' => now()->toDateString(),
            'status'          => 'active',
        ]);
        $this->asset->update(['status' => 'deployed']);

        // Attempt return date in past before deployment date
        $response = $this->actingAs($this->member)->post(route('deployment.return', $deployment), [
            'return_date' => now()->subDays(5)->toDateString(),
            'condition'   => 'good',
            'status'      => 'received',
        ]);

        $response->assertSessionHasErrors('return_date');
    }

    public function test_operational_requirement_rejects_current_level_exceeding_capacity(): void
    {
        $type = AssetType::create([
            'category_id' => $this->category->id,
            'name'        => 'Chainsaw Set',
        ]);

        $response = $this->actingAs($this->admin)->post(route('operational-requirements.store'), [
            'asset_type_id'    => $type->id,
            'requirement_type' => 'Bar & Chain Lubricant',
            'unit'             => 'Liters',
            'maximum_capacity' => 20.00,
            'minimum_level'    => 5.00,
            'current_level'    => 35.00, // Invalid: exceeds max capacity
        ]);

        $response->assertSessionHasErrors('current_level');
    }

    public function test_viewer_role_cannot_modify_operational_requirements_or_delete_reports(): void
    {
        $type = AssetType::create([
            'category_id' => $this->category->id,
            'name'        => 'Generators',
        ]);

        $req = OperationalRequirement::create([
            'asset_type_id'    => $type->id,
            'requirement_type' => 'Diesel',
            'unit'             => 'Liters',
            'maximum_capacity' => 100.00,
            'minimum_level'    => 20.00,
            'current_level'    => 50.00,
            'description'      => 'Emergency fuel reserve',
        ]);

        // Viewer cannot refill
        $refillResponse = $this->actingAs($this->viewer)->post(route('operational-requirements.refill', $req), [
            'amount' => 10,
        ]);
        $refillResponse->assertForbidden();

        // Viewer cannot delete
        $deleteResponse = $this->actingAs($this->viewer)->delete(route('operational-requirements.destroy', $req));
        $deleteResponse->assertForbidden();

        // Viewer cannot delete reported asset
        $report = ReportedAsset::create([
            'asset_id'       => $this->asset->id,
            'asset_name'     => $this->asset->name,
            'asset_category' => $this->category->name,
            'reported_type'  => 'Damage',
            'location'       => 'Depot',
            'status'         => 'Pending',
            'reported_date'  => now()->toDateString(),
            'reported_by'    => $this->viewer->id,
        ]);

        $reportDelete = $this->actingAs($this->viewer)->delete(route('reported-assets.destroy', $report));
        $reportDelete->assertForbidden();
    }
}
