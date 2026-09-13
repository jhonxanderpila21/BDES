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
    private Asset $asset;
    private Location $location;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role'      => 'administrator',
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
}

