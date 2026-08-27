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
}
