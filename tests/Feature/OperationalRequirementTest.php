<?php

namespace Tests\Feature;

use App\Models\AssetType;
use App\Models\OperationalRequirement;
use App\Models\User;
use Database\Seeders\AssetCategorySeeder;
use Database\Seeders\AssetTypeSeeder;
use Database\Seeders\OperationalRequirementSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalRequirementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role'      => 'administrator',
            'is_active' => true,
        ]);

        $this->seed(AssetCategorySeeder::class);
        $this->seed(AssetTypeSeeder::class);
    }

    public function test_operational_requirement_seeder_populates_database(): void
    {
        $this->seed(OperationalRequirementSeeder::class);

        $this->assertGreaterThan(0, OperationalRequirement::count());

        // Verify generator requirements
        $generatorType = AssetType::where('name', 'Generator Set')->firstOrFail();
        $this->assertDatabaseHas('operational_requirements', [
            'asset_type_id'    => $generatorType->id,
            'requirement_type' => 'Diesel Fuel',
            'unit'             => 'Liters',
        ]);

        // Verify rescue boat requirements
        $boatType = AssetType::where('name', 'Rescue Boat')->firstOrFail();
        $this->assertDatabaseHas('operational_requirements', [
            'asset_type_id'    => $boatType->id,
            'requirement_type' => 'Marine Gasoline (Unleaded)',
            'unit'             => 'Liters',
        ]);
    }

    public function test_operational_requirement_refill_and_consume(): void
    {
        $generatorType = AssetType::where('name', 'Generator Set')->firstOrFail();

        $requirement = OperationalRequirement::create([
            'asset_type_id'    => $generatorType->id,
            'requirement_type' => 'Test Fuel',
            'unit'             => 'Liters',
            'current_level'    => 20.00,
            'maximum_capacity' => 50.00,
            'minimum_level'    => 10.00,
            'description'      => 'Test operational requirement description',
        ]);

        // Test consume
        $this->actingAs($this->user)->post(route('operational-requirements.consume', $requirement), [
            'amount' => 5.0,
        ]);
        $this->assertEquals(15.00, $requirement->fresh()->current_level);

        // Test refill
        $this->actingAs($this->user)->post(route('operational-requirements.refill', $requirement), [
            'amount' => 10.0,
        ]);
        $this->assertEquals(25.00, $requirement->fresh()->current_level);

        // Test refill capping at maximum_capacity
        $this->actingAs($this->user)->post(route('operational-requirements.refill', $requirement), [
            'amount' => 100.0,
        ]);
        $this->assertEquals(50.00, $requirement->fresh()->current_level);
    }

    public function test_operational_requirements_page_and_sidebar_dropdown_render(): void
    {
        $this->seed(OperationalRequirementSeeder::class);

        $response = $this->actingAs($this->user)->get(route('operational-requirements.index'));
        $response->assertOk();
        $response->assertSee('Operational Requirements');
        $response->assertSee('Asset Management');
        $response->assertSee('Diesel Fuel');

        // Test low stock filter
        $lowStockResponse = $this->actingAs($this->user)->get(route('operational-requirements.index', ['low' => 1]));
        $lowStockResponse->assertOk();
    }

    public function test_store_creates_requirement_without_asset_type_and_renders_name_unit_count(): void
    {
        $response = $this->actingAs($this->user)->post(route('operational-requirements.store'), [
            'requirement_type' => 'Emergency Ration Pack',
            'unit'             => 'Boxes',
            'current_level'    => 75,
            'maximum_capacity' => 200,
            'minimum_level'    => 20,
            'description'      => 'Barangay emergency response food packs.',
        ]);

        $response->assertRedirect(route('operational-requirements.index'));
        $this->assertDatabaseHas('operational_requirements', [
            'requirement_type' => 'Emergency Ration Pack',
            'unit'             => 'Boxes',
            'current_level'    => 75,
            'maximum_capacity' => 200,
            'asset_type_id'    => null,
        ]);

        $indexResponse = $this->actingAs($this->user)->get(route('operational-requirements.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Emergency Ration Pack');
        $indexResponse->assertSee('Boxes');
        $indexResponse->assertSee('75');
        $indexResponse->assertSee('window.closeModal(\'add-requirement-modal\')', false);
    }
}
