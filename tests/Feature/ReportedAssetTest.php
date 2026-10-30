<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\Location;
use App\Models\ReportedAsset;
use App\Models\ReportedAssetStatusHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportedAssetTest extends TestCase
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

    public function test_index_displays_reports_and_summary(): void
    {
        ReportedAsset::create([
            'asset_id'      => $this->asset->id,
            'asset_category' => 'Emergency Equipment',
            'asset_name'    => 'GEN-TEST — Test Generator',
            'reported_by'   => $this->user->id,
            'reported_type' => 'Damage',
            'status'        => 'Pending',
            'location'      => 'Barangay Hall',
            'description'   => 'Generator won\'t start.',
            'reported_date' => Carbon::today(),
        ]);

        $response = $this->actingAs($this->user)->get(route('reported-assets.index'));

        $response->assertStatus(200);
        $response->assertSee('Test Generator');
        $response->assertSee('Damage');
        $response->assertSee('Pending');
    }

    public function test_store_creates_report_with_pending_status_and_history(): void
    {
        $response = $this->actingAs($this->user)->post(route('reported-assets.store'), [
            'asset_id'      => $this->asset->id,
            'reported_type' => 'Malfunction',
            'location'      => 'Evacuation Center 1',
            'description'   => 'Static noise on all channels.',
            'reported_date' => Carbon::today()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('reported-assets.index'));

        $this->assertDatabaseHas('reported_assets', [
            'reported_type' => 'Malfunction',
            'status'        => 'Pending',
            'location'      => 'Evacuation Center 1',
        ]);

        // Initial status history should have been logged
        $report = ReportedAsset::latest()->first();
        $this->assertDatabaseHas('reported_asset_status_histories', [
            'reported_asset_id' => $report->id,
            'from_status'       => null,
            'to_status'         => 'Pending',
        ]);
    }

    public function test_update_modifies_report_fields(): void
    {
        $report = ReportedAsset::create([
            'asset_id'       => $this->asset->id,
            'asset_category' => 'Emergency Equipment',
            'asset_name'     => 'GEN-TEST — Test Generator',
            'reported_by'    => $this->user->id,
            'reported_type'  => 'Damage',
            'status'         => 'Pending',
            'location'       => 'Barangay Hall',
            'reported_date'  => Carbon::today(),
        ]);

        $response = $this->actingAs($this->user)->put(route('reported-assets.update', $report), [
            'asset_id'      => $this->asset->id,
            'reported_type' => 'Malfunction',
            'location'      => 'Updated Location',
            'description'   => 'Updated description.',
            'reported_date' => Carbon::today()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('reported-assets.index'));
        $report->refresh();
        $this->assertEquals('Malfunction', $report->reported_type);
        $this->assertEquals('Updated Location', $report->location);
    }

    public function test_destroy_deletes_report(): void
    {
        $report = ReportedAsset::create([
            'asset_id'       => $this->asset->id,
            'asset_category' => 'Emergency Equipment',
            'asset_name'     => 'GEN-TEST — Test Generator',
            'reported_by'    => $this->user->id,
            'reported_type'  => 'Damage',
            'status'         => 'Pending',
            'location'       => 'Barangay Hall',
            'reported_date'  => Carbon::today(),
        ]);

        $response = $this->actingAs($this->user)->delete(route('reported-assets.destroy', $report));

        $response->assertRedirect(route('reported-assets.index'));
        $this->assertDatabaseMissing('reported_assets', ['id' => $report->id]);
    }

    public function test_change_status_valid_transition_succeeds(): void
    {
        $report = ReportedAsset::create([
            'asset_id'       => $this->asset->id,
            'asset_category' => 'Emergency Equipment',
            'asset_name'     => 'GEN-TEST — Test Generator',
            'reported_by'    => $this->user->id,
            'reported_type'  => 'Damage',
            'status'         => 'Pending',
            'location'       => 'Barangay Hall',
            'reported_date'  => Carbon::today(),
        ]);

        $response = $this->actingAs($this->user)->post(route('reported-assets.change-status', $report), [
            'new_status' => 'Ongoing',
            'notes'      => 'Skipping review, straightforward fix.',
        ]);

        $response->assertRedirect(route('reported-assets.show', $report));
        $report->refresh();
        $this->assertEquals('Ongoing', $report->status);

        // Check history was logged
        $this->assertDatabaseHas('reported_asset_status_histories', [
            'reported_asset_id' => $report->id,
            'from_status'       => 'Pending',
            'to_status'         => 'Ongoing',
            'notes'             => 'Skipping review, straightforward fix.',
        ]);
    }

    public function test_change_status_invalid_transition_is_rejected(): void
    {
        $report = ReportedAsset::create([
            'asset_id'       => $this->asset->id,
            'asset_category' => 'Emergency Equipment',
            'asset_name'     => 'GEN-TEST — Test Generator',
            'reported_by'    => $this->user->id,
            'reported_type'  => 'Damage',
            'status'         => 'Pending',
            'location'       => 'Barangay Hall',
            'reported_date'  => Carbon::today(),
        ]);

        $response = $this->actingAs($this->user)->post(route('reported-assets.change-status', $report), [
            'new_status' => 'Done', // Invalid: Pending cannot go to Done
        ]);

        $response->assertSessionHasErrors('new_status');
        $report->refresh();
        $this->assertEquals('Pending', $report->status); // Unchanged
    }

    public function test_resolved_date_auto_set_when_status_changes_to_done(): void
    {
        $report = ReportedAsset::create([
            'asset_id'       => $this->asset->id,
            'asset_category' => 'Emergency Equipment',
            'asset_name'     => 'GEN-TEST — Test Generator',
            'reported_by'    => $this->user->id,
            'reported_type'  => 'Damage',
            'status'         => 'Ongoing',
            'location'       => 'Barangay Hall',
            'reported_date'  => Carbon::today()->subDays(5),
        ]);

        $this->assertNull($report->resolved_date);

        $report->changeStatus('Done', $this->user->id, 'Repaired.');
        $report->refresh();

        $this->assertEquals('Done', $report->status);
        $this->assertNotNull($report->resolved_date);
        $this->assertEquals(Carbon::today()->format('Y-m-d'), $report->resolved_date->format('Y-m-d'));
    }

    public function test_validation_rejects_future_reported_date(): void
    {
        $response = $this->actingAs($this->user)->post(route('reported-assets.store'), [
            'asset_id'      => $this->asset->id,
            'reported_type' => 'Damage',
            'location'      => 'Barangay Hall',
            'reported_date' => Carbon::tomorrow()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('reported_date');
    }

    public function test_show_displays_report_and_timeline(): void
    {
        $report = ReportedAsset::create([
            'asset_id'       => $this->asset->id,
            'asset_category' => 'Emergency Equipment',
            'asset_name'     => 'GEN-TEST — Test Generator',
            'reported_by'    => $this->user->id,
            'reported_type'  => 'Damage',
            'status'         => 'Pending',
            'location'       => 'Barangay Hall',
            'reported_date'  => Carbon::today(),
        ]);

        $report->statusHistories()->create([
            'from_status' => null,
            'to_status'   => 'Pending',
            'changed_by'  => $this->user->id,
            'notes'       => 'Report created.',
        ]);

        $response = $this->actingAs($this->user)->get(route('reported-assets.show', $report));

        $response->assertStatus(200);
        $response->assertSee('Test Generator');
        $response->assertSee('Report created.');
        $response->assertSee('Status History');
    }

    public function test_asset_has_open_report_flag(): void
    {
        $report = ReportedAsset::create([
            'asset_id'       => $this->asset->id,
            'asset_category' => 'Emergency Equipment',
            'asset_name'     => 'GEN-TEST — Test Generator',
            'reported_by'    => $this->user->id,
            'reported_type'  => 'Damage',
            'status'         => 'Ongoing',
            'location'       => 'Barangay Hall',
            'reported_date'  => Carbon::today(),
        ]);

        $this->assertTrue($this->asset->has_open_report);

        // Now close it
        $report->status = 'Done';
        $report->save();
        $report->status = 'Closed';
        $report->save();

        // Refresh the asset
        $this->asset->refresh();
        $this->assertFalse($this->asset->has_open_report);
    }

    public function test_staff_can_view_reported_assets_index_with_summary_cards_and_read_only_badge(): void
    {
        ReportedAsset::create([
            'asset_id'       => $this->asset->id,
            'asset_category' => 'Emergency Equipment',
            'asset_name'     => 'GEN-TEST — Test Generator',
            'reported_by'    => $this->user->id,
            'reported_type'  => 'Damage',
            'status'         => 'Pending',
            'location'       => 'Barangay Hall Bay 2',
            'description'    => 'Power surge during storm.',
            'reported_date'  => Carbon::today(),
        ]);

        $response = $this->actingAs($this->staff)->get(route('reported-assets.index'));

        $response->assertStatus(200);
        $response->assertSee('Reported Assets');
        $response->assertSee('Read-Only Staff Access');
        $response->assertSee('Awaiting Review');
        $response->assertSee('Test Generator');
        $response->assertSee('Barangay Hall Bay 2');
        $response->assertSee('Power surge during storm.');
        $response->assertSee('View Details');

        // Verify modification controls are hidden
        $response->assertDontSee('File Report');
        $response->assertDontSee('openAddModal');
        $response->assertDontSee('openEditModal');
        $response->assertDontSee('route(\'reported-assets.destroy\'');
    }

    public function test_staff_can_view_complete_report_details_and_timeline(): void
    {
        $report = ReportedAsset::create([
            'asset_id'       => $this->asset->id,
            'asset_category' => 'Emergency Equipment',
            'asset_name'     => 'GEN-TEST — Test Generator',
            'reported_by'    => $this->user->id,
            'reported_type'  => 'Malfunction',
            'status'         => 'Ongoing',
            'location'       => 'Storage Bay 3',
            'description'    => 'Oil leak and irregular voltage output.',
            'action'         => 'Dispatched mechanic for inspection',
            'reported_date'  => Carbon::today(),
        ]);

        $report->statusHistories()->create([
            'from_status' => null,
            'to_status'   => 'Pending',
            'changed_by'  => $this->user->id,
            'notes'       => 'Initial report filed.',
        ]);

        $report->statusHistories()->create([
            'from_status' => 'Pending',
            'to_status'   => 'Ongoing',
            'changed_by'  => $this->user->id,
            'notes'       => 'Investigation started.',
        ]);

        // 1. View report details page (HTML)
        $response = $this->actingAs($this->staff)->get(route('reported-assets.show', $report));

        $response->assertStatus(200);
        $response->assertSee('Incident Report #' . $report->id);
        $response->assertSee('Incident Overview');
        $response->assertSee('Test Generator');
        $response->assertSee('Emergency Equipment');
        $response->assertSee('Malfunction');
        $response->assertSee('Storage Bay 3');
        $response->assertSee($this->user->name);
        $response->assertSee('Ongoing');
        $response->assertSee('Oil leak and irregular voltage output.');
        $response->assertSee('Dispatched mechanic for inspection');
        $response->assertSee('Status History');
        $response->assertSee('Initial report filed.');
        $response->assertSee('Investigation started.');

        // Verify staff cannot transition status
        $response->assertSee('Read-Only Staff Access');
        $response->assertDontSee('Transition Status');
        $response->assertDontSee('route(\'reported-assets.change-status\'');

        // 2. View complete report details via JSON endpoint
        $jsonResponse = $this->actingAs($this->staff)->getJson(route('reported-assets.show', $report));
        $jsonResponse->assertStatus(200);
        $jsonResponse->assertJsonPath('asset_name', 'GEN-TEST — Test Generator');
        $jsonResponse->assertJsonPath('reported_type', 'Malfunction');
        $jsonResponse->assertJsonPath('location', 'Storage Bay 3');
        $jsonResponse->assertJsonPath('reporter.name', $this->user->name);
        $jsonResponse->assertJsonPath('status', 'Ongoing');
    }

    public function test_staff_can_search_and_use_all_filters(): void
    {
        $rescueCategory = AssetCategory::create([
            'name'        => 'Rescue Vehicles',
            'description' => 'Boats and trucks',
        ]);
        $rescueType = AssetType::create([
            'name'        => 'Rescue Boat',
            'category_id' => $rescueCategory->id,
        ]);
        $rescueAsset = Asset::create([
            'code'        => 'BOAT-01',
            'name'        => 'Inflatable Rescue Boat',
            'category_id' => $rescueCategory->id,
            'type_id'     => $rescueType->id,
            'location_id' => $this->asset->location_id,
            'condition'   => 'good',
            'status'      => 'available',
        ]);

        $report1 = ReportedAsset::create([
            'asset_id'       => $this->asset->id,
            'asset_category' => 'Emergency Equipment',
            'asset_name'     => 'GEN-TEST — Test Generator',
            'reported_by'    => $this->user->id,
            'reported_type'  => 'Damage',
            'status'         => 'Pending',
            'location'       => 'Command Center',
            'description'    => 'Cracked alternator bracket',
            'reported_date'  => Carbon::today(),
        ]);

        $report2 = ReportedAsset::create([
            'asset_id'       => $rescueAsset->id,
            'asset_category' => 'Rescue Vehicles',
            'asset_name'     => 'BOAT-01 — Inflatable Rescue Boat',
            'reported_by'    => $this->user->id,
            'reported_type'  => 'Malfunction',
            'status'         => 'Escalated',
            'location'       => 'River Station Alpha',
            'description'    => 'Puncture on starboard pontoon',
            'reported_date'  => Carbon::yesterday(),
        ]);

        // Search by keyword
        $searchResponse = $this->actingAs($this->staff)->get(route('reported-assets.index', ['search' => 'alternator']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Test Generator');
        $searchResponse->assertDontSee('Inflatable Rescue Boat');

        // Filter by asset_category
        $categoryResponse = $this->actingAs($this->staff)->get(route('reported-assets.index', ['asset_category' => 'Rescue Vehicles']));
        $categoryResponse->assertStatus(200);
        $categoryResponse->assertSee('Inflatable Rescue Boat');
        $categoryResponse->assertDontSee('Test Generator');

        // Filter by reported_type
        $typeResponse = $this->actingAs($this->staff)->get(route('reported-assets.index', ['reported_type' => 'Damage']));
        $typeResponse->assertStatus(200);
        $typeResponse->assertSee('Test Generator');
        $typeResponse->assertDontSee('Inflatable Rescue Boat');

        // Filter by status
        $statusResponse = $this->actingAs($this->staff)->get(route('reported-assets.index', ['status' => 'Escalated']));
        $statusResponse->assertStatus(200);
        $statusResponse->assertSee('Inflatable Rescue Boat');
        $statusResponse->assertDontSee('Test Generator');

        // Filter by timeframe (today)
        $timeframeResponse = $this->actingAs($this->staff)->get(route('reported-assets.index', ['timeframe' => 'today']));
        $timeframeResponse->assertStatus(200);
        $timeframeResponse->assertSee('Test Generator');
        $timeframeResponse->assertDontSee('Inflatable Rescue Boat');
    }

    public function test_staff_cannot_file_edit_or_delete_reports(): void
    {
        $report = ReportedAsset::create([
            'asset_id'       => $this->asset->id,
            'asset_category' => 'Emergency Equipment',
            'asset_name'     => 'GEN-TEST — Test Generator',
            'reported_by'    => $this->user->id,
            'reported_type'  => 'Damage',
            'status'         => 'Pending',
            'location'       => 'Barangay Hall',
            'description'    => 'Original description.',
            'reported_date'  => Carbon::today(),
        ]);

        // 1. Attempt GET create
        $responseCreate = $this->actingAs($this->staff)->get(route('reported-assets.create'));
        $responseCreate->assertStatus(403);

        // 2. Attempt POST store
        $responseStore = $this->actingAs($this->staff)->post(route('reported-assets.store'), [
            'asset_id'      => $this->asset->id,
            'reported_type' => 'Damage',
            'location'      => 'Unauthorized Location',
            'description'   => 'Staff filing attempt.',
            'reported_date' => Carbon::today()->format('Y-m-d'),
        ]);
        $responseStore->assertStatus(403);
        $this->assertDatabaseMissing('reported_assets', ['location' => 'Unauthorized Location']);

        // 3. Attempt GET edit
        $responseEdit = $this->actingAs($this->staff)->get(route('reported-assets.edit', $report));
        $responseEdit->assertStatus(403);

        // 4. Attempt PUT update
        $responseUpdate = $this->actingAs($this->staff)->put(route('reported-assets.update', $report), [
            'asset_id'      => $this->asset->id,
            'reported_type' => 'Malfunction',
            'location'      => 'Tampered Location',
            'description'   => 'Tampered description.',
        ]);
        $responseUpdate->assertStatus(403);
        $this->assertEquals('Barangay Hall', $report->fresh()->location);

        // 5. Attempt POST change-status
        $responseStatus = $this->actingAs($this->staff)->post(route('reported-assets.change-status', $report), [
            'new_status' => 'Ongoing',
            'notes'      => 'Staff attempt to change status',
        ]);
        $responseStatus->assertStatus(403);
        $this->assertEquals('Pending', $report->fresh()->status);

        // 6. Attempt DELETE destroy
        $responseDelete = $this->actingAs($this->staff)->delete(route('reported-assets.destroy', $report));
        $responseDelete->assertStatus(403);
        $this->assertDatabaseHas('reported_assets', ['id' => $report->id]);
    }
}
