<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Deployment;
use App\Models\Location;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeneralAutocompleteSearchTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected AssetCategory $category;
    protected Location $location;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role'      => 'administrator',
            'is_active' => true,
        ]);

        $this->category = AssetCategory::create([
            'name'        => 'Rescue Equipment',
            'description' => 'Disaster response gear',
        ]);

        $this->location = Location::create([
            'name'             => 'Main Emergency Depot',
            'type'             => 'depot',
            'address'          => '123 Rescue St',
            'storage_capacity' => 100,
        ]);
    }

    public function test_guest_cannot_access_autocomplete_search(): void
    {
        $response = $this->get(route('search.autocomplete', ['q' => 'boat']));
        $response->assertRedirect(route('login'));
    }

    public function test_empty_query_returns_default_asset_suggestions(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            Asset::create([
                'code'             => "AST-DEF-00{$i}",
                'name'             => "Default Equipment {$i}",
                'category_id'      => $this->category->id,
                'location_id'      => $this->location->id,
                'condition'        => 'good',
                'status'           => 'available',
                'acquisition_cost' => 10000.00,
            ]);
        }

        $response = $this->actingAs($this->user)->getJson(route('search.autocomplete'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
        $data = $response->json('suggestions');
        $this->assertCount(5, $data);
    }

    public function test_search_query_returns_at_least_5_suggestions_when_matches_exist(): void
    {
        // Create 6 assets with matching 'Rescue'
        for ($i = 1; $i <= 6; $i++) {
            Asset::create([
                'code'             => "AST-RSC-00{$i}",
                'name'             => "Rescue Gear Item {$i}",
                'category_id'      => $this->category->id,
                'location_id'      => $this->location->id,
                'condition'        => 'good',
                'status'           => 'available',
                'acquisition_cost' => 5000.00,
            ]);
        }

        $response = $this->actingAs($this->user)->getJson(route('search.autocomplete', ['q' => 'Rescue']));

        $response->assertOk();
        $suggestions = $response->json('suggestions');
        // Must return at least 5 suggestions
        $this->assertGreaterThanOrEqual(5, count($suggestions));
        $this->assertStringContainsString('Rescue Gear Item', $suggestions[0]['title']);
    }

    public function test_search_finds_cross_module_records(): void
    {
        $asset = Asset::create([
            'code'             => 'AST-CROSS-01',
            'name'             => 'Amphibious Vehicle 1',
            'category_id'      => $this->category->id,
            'location_id'      => $this->location->id,
            'condition'        => 'good',
            'status'           => 'available',
            'acquisition_cost' => 50000.00,
        ]);

        $deployment = Deployment::create([
            'asset_id'        => $asset->id,
            'destination'     => 'Cross-site Delta',
            'deployment_date' => now()->toDateString(),
            'status'          => 'active',
        ]);

        $maintenance = Maintenance::create([
            'asset_id'          => $asset->id,
            'type'              => 'routine',
            'work_order_number' => 'WO-CROSS-99',
            'status'            => 'in_progress',
        ]);

        $location = Location::create([
            'name' => 'Cross Point Station',
            'type' => 'station',
        ]);

        // Search for 'Cross'
        $response = $this->actingAs($this->user)->getJson(route('search.autocomplete', ['q' => 'Cross']));

        $response->assertOk();
        $suggestions = collect($response->json('suggestions'));

        $this->assertTrue($suggestions->contains('type', 'asset'));
        $this->assertTrue($suggestions->contains('type', 'deployment'));
        $this->assertTrue($suggestions->contains('type', 'maintenance'));
        $this->assertTrue($suggestions->contains('type', 'location'));
    }
}
