<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\Attachment;
use App\Models\AuditLog;
use App\Models\Deployment;
use App\Models\Location;
use App\Models\Maintenance;
use App\Models\OperationalRequirement;
use App\Models\ReportedAsset;
use App\Models\User;
use App\Services\AssetLifecycleService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchivedRecordsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $viewer;
    protected AssetCategory $category;
    protected AssetType $type;
    protected Location $location;
    protected Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'      => 'administrator',
            'is_active' => true,
        ]);

        $this->viewer = User::factory()->create([
            'role'      => 'viewer',
            'is_active' => true,
        ]);

        $this->category = AssetCategory::create([
            'name'        => 'Emergency Rescue Vehicles',
            'description' => 'Disaster response transport',
        ]);

        $this->type = AssetType::create([
            'category_id' => $this->category->id,
            'name'        => 'Rescue Ambulance',
            'serialized'  => true,
            'borrowable'  => true,
        ]);

        $this->location = Location::create([
            'name'             => 'Disaster Response Command Post',
            'type'             => 'station',
            'storage_capacity' => 100,
        ]);

        $this->asset = Asset::create([
            'code'             => 'AMB-ALPHA-01',
            'name'             => 'Alpha Emergency Van',
            'category_id'      => $this->category->id,
            'asset_type_id'    => $this->type->id,
            'location_id'      => $this->location->id,
            'status'           => 'available',
            'condition'        => 'good',
            'acquisition_cost' => 1250000.00,
        ]);
    }

    /**
     * Test admin can access archived records page, and viewer is forbidden.
     */
    public function test_access_control_for_archived_records(): void
    {
        // Admin gets 200
        $responseAdmin = $this->actingAs($this->admin)->get(route('archived-records.index'));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee('Archived Records');
        $responseAdmin->assertSee('Source Module');
        $responseAdmin->assertSee('Total Archived');

        // Viewer gets 403
        $responseViewer = $this->actingAs($this->viewer)->get(route('archived-records.index'));
        $responseViewer->assertForbidden();
    }

    /**
     * Test soft-deleted records across modules are hidden from active views and appear in the centralized archive.
     */
    public function test_soft_deleted_records_appear_in_centralized_archive_table(): void
    {
        // 1. Soft-delete an asset
        $this->asset->deletion_reason = 'Engine cylinder damage beyond field repair';
        $this->asset->deleted_by = $this->admin->id;
        $this->asset->delete();

        // 2. Soft-delete an attachment
        $attachment = Attachment::create([
            'name'            => 'Oxygen Resuscitation Kit',
            'type'            => 'Medical Equipment',
            'asset_id'        => $this->asset->id,
            'quantity'        => 3,
            'unit_cost'       => 15000.00,
            'condition'       => 'good',
            'deletion_reason' => 'Replaced by next-generation unit',
            'deleted_by'      => $this->admin->id,
        ]);
        $attachment->delete();

        // 3. Soft-delete an operational requirement
        $requirement = OperationalRequirement::create([
            'asset_type_id'    => $this->type->id,
            'requirement_type' => 'Medical Grade Oxygen Tanks',
            'unit'             => 'cylinders',
            'current_level'    => 10,
            'minimum_level'    => 2,
            'maximum_capacity' => 20,
            'description'      => 'Emergency life support oxygen reserves',
            'deletion_reason'  => 'Switched to central gas line supply',
            'deleted_by'       => $this->admin->id,
        ]);
        $requirement->delete();

        // Verify hidden from normal listings
        $responseActiveAssets = $this->actingAs($this->admin)->get(route('assets.index', ['tab' => 'assets']));
        $responseActiveAssets->assertDontSee('AMB-ALPHA-01');

        $responseActiveParts = $this->actingAs($this->admin)->get(route('attachments.index', ['tab' => 'attachments']));
        $responseActiveParts->assertDontSee('Oxygen Resuscitation Kit');

        // Centralized Archived Records page contains all 3
        $responseArchive = $this->actingAs($this->admin)->get(route('archived-records.index'));
        $responseArchive->assertOk();
        $responseArchive->assertSee('AMB-ALPHA-01');
        $responseArchive->assertSee('Alpha Emergency Van');
        $responseArchive->assertSee('Engine cylinder damage beyond field repair');

        $responseArchive->assertSee('Oxygen Resuscitation Kit');
        $responseArchive->assertSee('Replaced by next-generation unit');

        $responseArchive->assertSee('Medical Grade Oxygen Tanks');
        $responseArchive->assertSee('Switched to central gas line supply');

        // Verify audit log entries were created
        $this->assertDatabaseHas('audit_logs', [
            'event'          => 'archived',
            'auditable_type' => Asset::class,
            'auditable_id'   => $this->asset->id,
        ]);
    }

    /**
     * Test filtering archived records by module, deleted_by user, and search term.
     */
    public function test_filtering_archived_records(): void
    {
        $this->asset->deletion_reason = 'Decommissioned test asset';
        $this->asset->deleted_by = $this->admin->id;
        $this->asset->delete();

        $attachment = Attachment::create([
            'name'            => 'Portable Stretcher Set',
            'type'            => 'Hardware',
            'quantity'        => 2,
            'deletion_reason' => 'Wear and tear after flood operations',
            'deleted_by'      => $this->admin->id,
        ]);
        $attachment->delete();

        // Filter by assets module
        $resAssetsOnly = $this->actingAs($this->admin)->get(route('archived-records.index', ['module' => 'assets']));
        $resAssetsOnly->assertOk();
        $resAssetsOnly->assertSee('AMB-ALPHA-01');
        $resAssetsOnly->assertDontSee('Portable Stretcher Set');

        // Filter by attachments module
        $resPartsOnly = $this->actingAs($this->admin)->get(route('archived-records.index', ['module' => 'attachments']));
        $resPartsOnly->assertOk();
        $resPartsOnly->assertSee('Portable Stretcher Set');
        $resPartsOnly->assertDontSee('AMB-ALPHA-01');

        // Search by keyword in reason
        $resSearch = $this->actingAs($this->admin)->get(route('archived-records.index', ['search' => 'flood operations']));
        $resSearch->assertOk();
        $resSearch->assertSee('Portable Stretcher Set');
        $resSearch->assertDontSee('AMB-ALPHA-01');
    }

    /**
     * Test restoring an archived record restores relationships and logs audit activity.
     */
    public function test_admin_can_restore_archived_record(): void
    {
        $attachment = Attachment::create([
            'name'            => 'Specialized Rescue Winch',
            'type'            => 'Vehicle Attachment',
            'asset_id'        => $this->asset->id,
            'quantity'        => 1,
            'unit_cost'       => 28000.00,
            'condition'       => 'excellent',
            'deletion_reason' => 'Mistakenly archived during warehouse audit',
            'deleted_by'      => $this->admin->id,
        ]);
        $attachment->delete();

        $this->assertSoftDeleted('attachments', ['id' => $attachment->id]);

        // Restore via Archived Records route
        $response = $this->actingAs($this->admin)->post(route('archived-records.restore', [
            'module' => 'attachments',
            'id'     => $attachment->id,
        ]));

        $response->assertRedirect(route('archived-records.index'));
        $response->assertSessionHas('success');

        // Verify model is restored
        $attachmentFresh = $attachment->fresh();
        $this->assertNull($attachmentFresh->deleted_at);
        $this->assertEquals($this->asset->id, $attachmentFresh->asset_id);
        $this->assertEquals('Alpha Emergency Van', $attachmentFresh->asset->name);

        // Verify restoration logged in AuditLog
        $this->assertDatabaseHas('audit_logs', [
            'event'          => 'restored',
            'auditable_type' => Attachment::class,
            'auditable_id'   => $attachment->id,
        ]);
    }

    /**
     * Test permanent deletion protection: cannot permanently delete records with critical accountability history.
     */
    public function test_accountability_protection_blocks_permanent_deletion(): void
    {
        // Link a historical deployment to the asset
        Deployment::create([
            'asset_id'           => $this->asset->id,
            'user_id'            => $this->admin->id,
            'destination'        => 'Flood Zone Purok 4',
            'status'             => 'returned',
            'deployment_date'    => now()->subDays(10),
            'expected_return_date'=> now()->subDays(5),
            'purpose'            => 'Flood evacuation mission',
            'responsible_person' => 'Captain Del Rosario',
        ]);

        // Soft-delete the asset
        $this->asset->deletion_reason = 'Candidate for disposal';
        $this->asset->deleted_by = $this->admin->id;
        $this->asset->delete();

        // Attempt permanent force deletion
        $response = $this->actingAs($this->admin)->delete(route('archived-records.force-delete', [
            'module' => 'assets',
            'id'     => $this->asset->id,
        ]));

        $response->assertRedirect(route('archived-records.index'));
        $response->assertSessionHas('error');

        // Assert record is still preserved in database table
        $this->assertDatabaseHas('assets', ['id' => $this->asset->id]);
        $this->assertNotNull($this->asset->fresh()->deleted_at);
    }

    /**
     * Test authorized permanent deletion of an unlinked record.
     */
    public function test_permanent_deletion_of_unlinked_archived_record(): void
    {
        $attachment = Attachment::create([
            'name'            => 'Deprecated Temporary Siren Part',
            'type'            => 'Hardware',
            'quantity'        => 1,
            'deletion_reason' => 'Scrapped metal scrap',
            'deleted_by'      => $this->admin->id,
        ]);
        $attachment->delete();

        $this->assertSoftDeleted('attachments', ['id' => $attachment->id]);

        $response = $this->actingAs($this->admin)->delete(route('archived-records.force-delete', [
            'module' => 'attachments',
            'id'     => $attachment->id,
        ]));

        $response->assertRedirect(route('archived-records.index'));
        $response->assertSessionHas('success');

        // Permanently erased from table
        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);

        // Permanent deletion logged to audit trail
        $this->assertDatabaseHas('audit_logs', [
            'event'          => 'permanently_deleted',
            'auditable_type' => Attachment::class,
            'auditable_id'   => $attachment->id,
        ]);
    }

    /**
     * Test JSON details endpoint for modal view.
     */
    public function test_archived_record_details_api_endpoint(): void
    {
        $this->asset->deletion_reason = 'Structural chassis failure';
        $this->asset->deleted_by = $this->admin->id;
        $this->asset->delete();

        $response = $this->actingAs($this->admin)->getJson(route('archived-records.show', [
            'module' => 'assets',
            'id'     => $this->asset->id,
        ]));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data'    => [
                'record_id'       => 'AMB-ALPHA-01',
                'record_name'     => 'Alpha Emergency Van',
                'module_label'    => 'Assets Registry',
                'deletion_reason' => 'Structural chassis failure',
            ],
        ]);
    }

    /**
     * Test ArchivedRecordsSeeder populates realistic soft-deleted records across modules with audit logs.
     */
    public function test_archived_records_seeder_populates_archive_across_modules(): void
    {
        $this->seed(\Database\Seeders\ArchivedRecordsSeeder::class);

        // Assets
        $this->assertTrue(Asset::onlyTrashed()->where('code', 'AST-ARC-001')->exists());
        $archivedAsset = Asset::onlyTrashed()->where('code', 'AST-ARC-001')->first();
        $this->assertNotNull($archivedAsset->deleted_at);
        $this->assertNotNull($archivedAsset->deleted_by);
        $this->assertStringContainsString('Engine block cracked', $archivedAsset->deletion_reason);

        // Work orders
        $this->assertTrue(Maintenance::onlyTrashed()->where('work_order_number', 'WO-ARC-001')->exists());

        // Attachments
        $this->assertTrue(Attachment::onlyTrashed()->where('name', 'like', '%Discharge Hose%')->exists());

        // Locations
        $this->assertTrue(Location::onlyTrashed()->where('name', 'like', '%Covered Court%')->exists());

        // Audit Logs
        $this->assertDatabaseHas('audit_logs', [
            'event'          => 'archived',
            'auditable_type' => Asset::class,
            'auditable_id'   => $archivedAsset->id,
        ]);

        // UI Access: Check that archived records show on the central table
        $response = $this->actingAs($this->admin)->get(route('archived-records.index'));
        $response->assertOk();
        $response->assertSee('AST-ARC-001');
        $response->assertSee('WO-ARC-001');
    }
}
