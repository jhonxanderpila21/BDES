<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetReturn;
use App\Models\AssetType;
use App\Models\Deployment;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeploymentAndReturnTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $staff;
    private Asset $asset;
    private Location $location;

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
            'name'        => 'Rescue Equipment',
            'description' => 'Disaster response gear',
        ]);

        $type = AssetType::create([
            'name'        => 'Medical Kit',
            'category_id' => $category->id,
        ]);

        $this->location = Location::create([
            'name'     => 'Main Storage Room',
            'capacity' => 50,
        ]);

        $this->asset = Asset::create([
            'code'        => 'AST-MED01',
            'name'        => 'Trauma Response Bag',
            'category_id' => $category->id,
            'type_id'     => $type->id,
            'location_id' => $this->location->id,
            'condition'   => 'good',
            'status'      => 'available',
        ]);
    }

    public function test_index_displays_deployments_and_metrics(): void
    {
        Deployment::create([
            'asset_id'        => $this->asset->id,
            'destination'     => 'Evacuation Site Alpha',
            'assigned_team'   => 'Alpha Team',
            'deployment_date' => now()->toDateString(),
            'status'          => 'active',
        ]);

        $response = $this->actingAs($this->user)->get(route('deployment.index'));

        $response->assertStatus(200);
        $response->assertSee('Deployments & Returns');
        $response->assertSee('Evacuation Site Alpha');
        $response->assertSee('Alpha Team');
        $response->assertSee('AST-MED01');
    }

    public function test_can_create_deployment_and_updates_asset_status_to_deployed(): void
    {
        $response = $this->actingAs($this->user)->post(route('deployment.store'), [
            'asset_id'             => $this->asset->id,
            'destination'          => 'Community Center',
            'assigned_team'        => 'Bravo Team',
            'deployment_date'      => now()->toDateString(),
            'expected_return_date' => now()->addDays(3)->toDateString(),
            'purpose'              => 'First aid readiness during typhoon',
        ]);

        $response->assertRedirect(route('deployment.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('deployments', [
            'asset_id'      => $this->asset->id,
            'destination'   => 'Community Center',
            'assigned_team' => 'Bravo Team',
            'status'        => 'active',
        ]);

        $this->assertEquals('deployed', $this->asset->fresh()->status);
    }

    public function test_cannot_deploy_asset_that_is_already_deployed(): void
    {
        $this->asset->update(['status' => 'deployed']);

        $response = $this->actingAs($this->user)->post(route('deployment.store'), [
            'asset_id'        => $this->asset->id,
            'destination'     => 'Zone 5 Outpost',
            'deployment_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors(['asset_id']);
        $this->assertDatabaseMissing('deployments', [
            'destination' => 'Zone 5 Outpost',
        ]);
    }

    public function test_can_record_return_on_deployment_and_updates_asset_to_available_with_new_condition(): void
    {
        $deployment = Deployment::create([
            'asset_id'        => $this->asset->id,
            'destination'     => 'Field Command Post',
            'assigned_team'   => 'Delta Squad',
            'deployment_date' => now()->subDays(2)->toDateString(),
            'status'          => 'active',
        ]);
        $this->asset->update(['status' => 'deployed']);

        $response = $this->actingAs($this->user)->post(route('deployment.return', $deployment), [
            'return_date'         => now()->toDateString(),
            'returned_by'         => 'Captain Miller',
            'condition'           => 'excellent',
            'damage_notes'        => 'No damages observed',
            'missing_accessories' => null,
            'actual_location'     => 'Main Storage Room',
            'status'              => 'received',
        ]);

        $response->assertRedirect(route('deployment.index'));
        $response->assertSessionHas('success');

        // Check deployment status
        $this->assertEquals('returned', $deployment->fresh()->status);

        // Check return record created and linked
        $this->assertDatabaseHas('returns', [
            'deployment_id'   => $deployment->id,
            'asset_id'        => $this->asset->id,
            'returned_by'     => 'Captain Miller',
            'condition'       => 'excellent',
            'actual_location' => 'Main Storage Room',
            'status'          => 'received',
        ]);

        // Check asset status and condition updated
        $freshAsset = $this->asset->fresh();
        $this->assertEquals('available', $freshAsset->status);
        $this->assertEquals('excellent', $freshAsset->condition);
        $this->assertEquals($this->location->id, $freshAsset->location_id);
    }

    public function test_can_filter_deployments_by_status(): void
    {
        Deployment::create([
            'asset_id'        => $this->asset->id,
            'destination'     => 'Active Site',
            'deployment_date' => now()->toDateString(),
            'status'          => 'active',
        ]);

        $response = $this->actingAs($this->user)->get(route('deployment.index', ['status' => 'active']));
        $response->assertStatus(200);
        $response->assertSee('Active Site');

        $responseReturned = $this->actingAs($this->user)->get(route('deployment.index', ['status' => 'returned']));
        $responseReturned->assertStatus(200);
        $responseReturned->assertDontSee('Active Site');
    }

    public function test_deleting_active_deployment_resets_asset_to_available(): void
    {
        $deployment = Deployment::create([
            'asset_id'        => $this->asset->id,
            'destination'     => 'Temporary Post',
            'deployment_date' => now()->toDateString(),
            'status'          => 'active',
        ]);
        $this->asset->update(['status' => 'deployed']);

        $response = $this->actingAs($this->user)->delete(route('deployment.destroy', $deployment));
        $response->assertRedirect(route('deployment.index'));

        $this->assertDatabaseMissing('deployments', ['id' => $deployment->id]);
        $this->assertEquals('available', $this->asset->fresh()->status);
    }

    public function test_returns_index_route_redirects_to_deployment_index_with_returned_filter(): void
    {
        $response = $this->actingAs($this->user)->get(route('returns.index'));
        $response->assertRedirect(route('deployment.index', ['status' => 'returned']));
    }

    public function test_staff_can_view_all_deployment_and_return_records(): void
    {
        $activeDep = Deployment::create([
            'asset_id'        => $this->asset->id,
            'destination'     => 'Site Alpha 101',
            'assigned_team'   => 'Response Unit A',
            'deployment_date' => now()->toDateString(),
            'status'          => 'active',
        ]);

        $secondAsset = Asset::create([
            'code'        => 'AST-RES02',
            'name'        => 'Emergency Generator',
            'category_id' => $this->asset->category_id,
            'type_id'     => $this->asset->type_id,
            'location_id' => $this->location->id,
            'condition'   => 'good',
            'status'      => 'deployed',
        ]);

        $returnedDep = Deployment::create([
            'asset_id'        => $secondAsset->id,
            'destination'     => 'Shelter Zone B',
            'assigned_team'   => 'Logistics Unit',
            'deployment_date' => now()->subDays(5)->toDateString(),
            'status'          => 'returned',
        ]);

        AssetReturn::create([
            'deployment_id'   => $returnedDep->id,
            'asset_id'        => $secondAsset->id,
            'returned_by'     => 'Officer Ramos',
            'received_by'     => $this->user->id,
            'return_date'     => now()->subDay()->toDateString(),
            'condition'       => 'good',
            'actual_location' => 'Main Storage Room',
            'status'          => 'received',
        ]);

        $response = $this->actingAs($this->staff)->get(route('deployment.index'));

        $response->assertStatus(200);
        $response->assertSee('Deployments & Returns');
        $response->assertSee('Site Alpha 101');
        $response->assertSee('Response Unit A');
        $response->assertSee('AST-MED01');
        $response->assertSee('Shelter Zone B');
        $response->assertSee('Logistics Unit');
        $response->assertSee('AST-RES02');
    }

    public function test_summary_cards_display_correctly_for_staff(): void
    {
        // 1 Active
        Deployment::create([
            'asset_id'        => $this->asset->id,
            'destination'     => 'Site Active',
            'deployment_date' => now()->toDateString(),
            'status'          => 'active',
        ]);

        // 1 Pending Return
        $pendingAsset = Asset::create([
            'code'        => 'AST-PEND01',
            'name'        => 'Hydraulic Cutter',
            'category_id' => $this->asset->category_id,
            'location_id' => $this->location->id,
            'condition'   => 'good',
            'status'      => 'deployed',
        ]);
        Deployment::create([
            'asset_id'        => $pendingAsset->id,
            'destination'     => 'Site Pending',
            'deployment_date' => now()->toDateString(),
            'status'          => 'pending_return',
        ]);

        // 1 Returned
        $returnedAsset = Asset::create([
            'code'        => 'AST-RET01',
            'name'        => 'Flood Light Tower',
            'category_id' => $this->asset->category_id,
            'location_id' => $this->location->id,
            'condition'   => 'good',
            'status'      => 'available',
        ]);
        Deployment::create([
            'asset_id'        => $returnedAsset->id,
            'destination'     => 'Site Returned',
            'deployment_date' => now()->subDays(3)->toDateString(),
            'status'          => 'returned',
        ]);

        $response = $this->actingAs($this->staff)->get(route('deployment.index'));

        $response->assertStatus(200);
        $response->assertSee('Total Records');
        $response->assertSee('Active in Field');
        $response->assertSee('Pending Return');
        $response->assertSee('Returned');

        // Check view data statusCounts
        $response->assertViewHas('statusCounts', function ($counts) {
            return $counts['total'] === 3
                && $counts['active'] === 1
                && $counts['pending_return'] === 1
                && $counts['returned'] === 1;
        });
    }

    public function test_staff_can_search_and_filter_records_by_status(): void
    {
        $dep1 = Deployment::create([
            'asset_id'        => $this->asset->id,
            'destination'     => 'Zone 3 Evacuation Center',
            'assigned_team'   => 'Alpha Search Unit',
            'deployment_date' => now()->toDateString(),
            'status'          => 'active',
        ]);

        $secondAsset = Asset::create([
            'code'        => 'AST-SEARCH02',
            'name'        => 'Satellite Phone',
            'category_id' => $this->asset->category_id,
            'location_id' => $this->location->id,
            'condition'   => 'good',
            'status'      => 'deployed',
        ]);

        $dep2 = Deployment::create([
            'asset_id'        => $secondAsset->id,
            'destination'     => 'Purok 5 Mountain Post',
            'assigned_team'   => 'Bravo Mountain Team',
            'deployment_date' => now()->subDays(2)->toDateString(),
            'status'          => 'returned',
        ]);

        // Search by destination "Zone 3"
        $searchResponse = $this->actingAs($this->staff)->get(route('deployment.index', ['search' => 'Zone 3']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Zone 3 Evacuation Center');
        $searchResponse->assertDontSee('Purok 5 Mountain Post');

        // Search by asset code "AST-SEARCH02"
        $codeSearchResponse = $this->actingAs($this->staff)->get(route('deployment.index', ['search' => 'AST-SEARCH02']));
        $codeSearchResponse->assertStatus(200);
        $codeSearchResponse->assertSee('Purok 5 Mountain Post');
        $codeSearchResponse->assertDontSee('Zone 3 Evacuation Center');

        // Filter by status active
        $activeFilterResponse = $this->actingAs($this->staff)->get(route('deployment.index', ['status' => 'active']));
        $activeFilterResponse->assertStatus(200);
        $activeFilterResponse->assertSee('Zone 3 Evacuation Center');
        $activeFilterResponse->assertDontSee('Purok 5 Mountain Post');

        // Filter by status returned
        $returnedFilterResponse = $this->actingAs($this->staff)->get(route('deployment.index', ['status' => 'returned']));
        $returnedFilterResponse->assertStatus(200);
        $returnedFilterResponse->assertSee('Purok 5 Mountain Post');
        $returnedFilterResponse->assertDontSee('Zone 3 Evacuation Center');
    }

    public function test_staff_can_open_record_details_and_view_inspection_information(): void
    {
        $deployment = Deployment::create([
            'asset_id'             => $this->asset->id,
            'destination'          => 'East Riverside Outpost',
            'assigned_team'        => 'Water Rescue Team',
            'responsible_person'   => 'Sergeant Cruz',
            'deployment_date'      => now()->subDays(4)->toDateString(),
            'expected_return_date' => now()->subDay()->toDateString(),
            'status'               => 'returned',
        ]);

        $returnRecord = AssetReturn::create([
            'deployment_id'       => $deployment->id,
            'asset_id'            => $this->asset->id,
            'returned_by'         => 'Sergeant Cruz',
            'received_by'         => $this->user->id,
            'return_date'         => now()->subDay()->toDateString(),
            'condition'           => 'fair',
            'actual_location'     => 'Warehouse Shelf 4B',
            'damage_notes'        => 'Minor scuff on the protective casing',
            'missing_accessories' => 'Missing shoulder strap buckle',
            'status'              => 'damage_noted',
        ]);

        // Staff can fetch details via AJAX/JSON show endpoint
        $jsonResponse = $this->actingAs($this->staff)
            ->getJson(route('deployment.show', $deployment));

        $jsonResponse->assertStatus(200);
        $jsonResponse->assertJsonFragment([
            'destination' => 'East Riverside Outpost',
            'condition'   => 'fair',
            'damage_notes' => 'Minor scuff on the protective casing',
            'missing_accessories' => 'Missing shoulder strap buckle',
            'actual_location' => 'Warehouse Shelf 4B',
        ]);

        // Staff can also fetch return record details directly
        $returnJsonResponse = $this->actingAs($this->staff)
            ->getJson(route('returns.show', $returnRecord));

        $returnJsonResponse->assertStatus(200);
        $returnJsonResponse->assertJsonFragment([
            'returned_by'     => 'Sergeant Cruz',
            'condition'       => 'fair',
            'damage_notes'    => 'Minor scuff on the protective casing',
            'actual_location' => 'Warehouse Shelf 4B',
        ]);

        // HTML page renders the return inspection information in the table and dossier
        $htmlResponse = $this->actingAs($this->staff)->get(route('deployment.index'));
        $htmlResponse->assertStatus(200);
        $htmlResponse->assertSee('Fair');
        $htmlResponse->assertSee('Damage noted');
        $htmlResponse->assertSee('East Riverside Outpost');
        $htmlResponse->assertSee('openViewReturnModal');
        $htmlResponse->assertSee('openDeploymentDetailsModal');
    }

    public function test_interface_hides_action_buttons_for_staff(): void
    {
        $deployment = Deployment::create([
            'asset_id'        => $this->asset->id,
            'destination'     => 'Test Station',
            'deployment_date' => now()->toDateString(),
            'status'          => 'active',
        ]);

        $response = $this->actingAs($this->staff)->get(route('deployment.index'));

        $response->assertStatus(200);

        // Staff read-only badge should be visible
        $response->assertSee('Read-Only Staff Access');

        // Mutating action buttons must NOT be present
        $response->assertDontSee('openCreateDeploymentModal');
        $response->assertDontSee('openGenericRecordReturnModal');
        $response->assertDontSee('openRecordReturnModal');
        $response->assertDontSee('openEditDeploymentModal');
        $response->assertDontSee('Deploy Asset</button>', false);
        $response->assertDontSee('Record Return</button>', false);
        $response->assertDontSee('id="create-deployment-modal"', false);
        $response->assertDontSee('id="record-return-modal"', false);
        $response->assertDontSee('id="edit-deployment-modal"', false);
        $response->assertDontSee('route(\'deployment.destroy\'', false);

        // Read-only modal buttons MUST remain present
        $response->assertSee('openDeploymentDetailsModal');
        $response->assertSee('id="view-deployment-modal"', false);
        $response->assertSee('id="view-return-modal"', false);

        // Search bar and filter must remain available
        $response->assertSee('name="search"', false);
        $response->assertSee('All Statuses');
    }

    public function test_restricted_backend_operations_rejected_for_staff(): void
    {
        $deployment = Deployment::create([
            'asset_id'        => $this->asset->id,
            'destination'     => 'Station Delta',
            'deployment_date' => now()->toDateString(),
            'status'          => 'active',
        ]);

        $returnRecord = AssetReturn::create([
            'deployment_id'   => $deployment->id,
            'asset_id'        => $this->asset->id,
            'returned_by'     => 'Officer Test',
            'received_by'     => $this->user->id,
            'return_date'     => now()->toDateString(),
            'condition'       => 'good',
            'actual_location' => 'HQ',
            'status'          => 'received',
        ]);

        // 1. Staff cannot access create form
        $this->actingAs($this->staff)->get(route('deployment.create'))->assertStatus(403);

        // 2. Staff cannot store new deployment
        $this->actingAs($this->staff)->post(route('deployment.store'), [
            'asset_id'        => $this->asset->id,
            'destination'     => 'Forbidden Destination',
            'deployment_date' => now()->toDateString(),
        ])->assertStatus(403);

        // 3. Staff cannot access edit form
        $this->actingAs($this->staff)->get(route('deployment.edit', $deployment))->assertStatus(403);

        // 4. Staff cannot update deployment
        $this->actingAs($this->staff)->put(route('deployment.update', $deployment), [
            'destination'     => 'Updated Forbidden',
            'deployment_date' => now()->toDateString(),
            'status'          => 'active',
        ])->assertStatus(403);

        // 5. Staff cannot record asset return
        $this->actingAs($this->staff)->post(route('deployment.return', $deployment), [
            'return_date'     => now()->toDateString(),
            'condition'       => 'good',
            'status'          => 'received',
        ])->assertStatus(403);

        // 6. Staff cannot delete deployment
        $this->actingAs($this->staff)->delete(route('deployment.destroy', $deployment))->assertStatus(403);

        // 7. Staff cannot access returns create or edit
        $this->actingAs($this->staff)->get(route('returns.create'))->assertStatus(403);
        $this->actingAs($this->staff)->get(route('returns.edit', $returnRecord))->assertStatus(403);

        // 8. Staff cannot store, update, or destroy via ReturnController
        $this->actingAs($this->staff)->post(route('returns.store'), [
            'asset_id'    => $this->asset->id,
            'return_date' => now()->toDateString(),
            'condition'   => 'good',
            'status'      => 'received',
        ])->assertStatus(403);

        $this->actingAs($this->staff)->put(route('returns.update', $returnRecord), [
            'condition'   => 'fair',
            'status'      => 'received',
        ])->assertStatus(403);

        $this->actingAs($this->staff)->delete(route('returns.destroy', $returnRecord))->assertStatus(403);
    }

    public function test_administrator_functionality_remains_unchanged(): void
    {
        // 1. Admin sees Deploy Asset and Record Return buttons on index
        $response = $this->actingAs($this->user)->get(route('deployment.index'));
        $response->assertStatus(200);
        $response->assertSee('Deploy Asset');
        $response->assertSee('Record Return');
        $response->assertDontSee('Read-Only Staff Access');

        // 2. Admin can store deployment
        $storeResponse = $this->actingAs($this->user)->post(route('deployment.store'), [
            'asset_id'        => $this->asset->id,
            'destination'     => 'Admin Test Site',
            'deployment_date' => now()->toDateString(),
        ]);
        $storeResponse->assertRedirect(route('deployment.index'));
        $storeResponse->assertSessionHas('success');

        $deployment = Deployment::where('destination', 'Admin Test Site')->first();
        $this->assertNotNull($deployment);

        // 3. Admin can update deployment
        $updateResponse = $this->actingAs($this->user)->put(route('deployment.update', $deployment), [
            'destination'     => 'Admin Updated Site',
            'deployment_date' => now()->toDateString(),
            'status'          => 'active',
        ]);
        $updateResponse->assertRedirect(route('deployment.index'));
        $this->assertEquals('Admin Updated Site', $deployment->fresh()->destination);

        // 4. Admin can record return
        $returnResponse = $this->actingAs($this->user)->post(route('deployment.return', $deployment), [
            'return_date'     => now()->toDateString(),
            'condition'       => 'excellent',
            'actual_location' => 'Main Storage Room',
            'status'          => 'received',
        ]);
        $returnResponse->assertRedirect(route('deployment.index'));
        $this->assertEquals('returned', $deployment->fresh()->status);

        // 5. Admin can delete deployment
        $deleteResponse = $this->actingAs($this->user)->delete(route('deployment.destroy', $deployment));
        $deleteResponse->assertRedirect(route('deployment.index'));
        $this->assertDatabaseMissing('deployments', ['id' => $deployment->id]);
    }
}

