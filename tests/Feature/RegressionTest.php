<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetNeed;
use App\Models\AssetType;
use App\Models\Attachment;
use App\Models\Maintenance;
use App\Models\OperationalRequirement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegressionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;
    protected AssetCategory $category;
    protected AssetType $generatorType;
    protected AssetType $extinguisherType;
    protected Asset $generatorAsset;
    protected Asset $extinguisherAsset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'administrator',
            'is_active' => true,
        ]);

        $this->staff = User::factory()->create([
            'role' => 'viewer',
            'is_active' => true,
        ]);

        $this->category = AssetCategory::create([
            'name' => 'Emergency Equipment',
            'description' => 'Category for emergency rescue equipment',
        ]);

        $this->generatorType = AssetType::create([
            'name' => 'Generator Set',
            'category_id' => $this->category->id,
        ]);

        $this->extinguisherType = AssetType::create([
            'name' => 'Fire Extinguisher',
            'category_id' => $this->category->id,
        ]);

        $this->generatorAsset = Asset::create([
            'code' => 'GEN-REG-01',
            'name' => 'Heavy Duty Generator',
            'category_id' => $this->category->id,
            'type_id' => $this->generatorType->id,
            'status' => 'available',
            'condition' => 'good',
        ]);

        $this->extinguisherAsset = Asset::create([
            'code' => 'FE-REG-01',
            'name' => 'Purok A Fire Extinguisher',
            'category_id' => $this->category->id,
            'type_id' => $this->extinguisherType->id,
            'status' => 'available',
            'condition' => 'good',
        ]);
    }

    /**
     * Test that confirmSecureDelete displays correct item labels for operational requirements,
     * asset needs, attachments, and maintenance work orders.
     */
    public function test_confirm_secure_delete_displays_proper_item_identifiers(): void
    {
        // 1. Operational Requirement & Asset Need
        $req = OperationalRequirement::create([
            'asset_type_id' => $this->generatorType->id,
            'requirement_type' => 'Diesel Fuel High Grade',
            'unit' => 'Liters',
            'maximum_capacity' => 200,
            'minimum_level' => 30,
            'current_level' => 100,
            'description' => 'High grade emergency generator fuel reserve',
        ]);

        $need = AssetNeed::create([
            'asset_id' => $this->generatorAsset->id,
            'asset_type' => 'Generator Set',
            'requirement' => 'Synthetic Motor Oil',
            'unit' => 'Liters',
            'maximum_capacity' => 50,
            'minimum_level' => 10,
            'current_level' => 25,
        ]);

        $opResponse = $this->actingAs($this->admin)->get(route('operational-requirements.index', ['tab' => 'requirements']));
        $opResponse->assertOk();
        $opResponse->assertSee("Supply: Diesel Fuel High Grade");

        $needResponse = $this->actingAs($this->admin)->get(route('operational-requirements.index', ['tab' => 'needs']));
        $needResponse->assertOk();
        $needResponse->assertSee("Equipment Need: Synthetic Motor Oil (Heavy Duty Generator)");

        // 2. Attachment
        $attachment = Attachment::create([
            'name' => 'Heavy Duty Towing Cable',
            'type' => 'Cable',
            'asset_id' => $this->generatorAsset->id,
            'quantity' => 2,
            'unit_cost' => 1500,
            'condition' => 'good',
        ]);

        $attResponse = $this->actingAs($this->admin)->get(route('attachments.index', ['tab' => 'attachments']));
        $attResponse->assertOk();
        $attResponse->assertSee("Attachment: Heavy Duty Towing Cable");

        // 3. Maintenance Work Order
        $maintenance = Maintenance::create([
            'work_order_number' => 'WO-REG-2026-001',
            'asset_id' => $this->generatorAsset->id,
            'type' => 'corrective',
            'priority' => 'high',
            'status' => 'in_progress',
            'cost' => 500,
        ]);

        $maintResponse = $this->actingAs($this->admin)->get(route('maintenance.index', ['tab' => 'logs']));
        $maintResponse->assertOk();
        $maintResponse->assertSee("Work Order: [WO-REG-2026-001] (Heavy Duty Generator)");
    }

    /**
     * Test that fire extinguishers cannot have operational requirements or needs assigned or refilled.
     */
    public function test_fire_extinguisher_cannot_have_refill_or_operational_needs(): void
    {
        // 1. Creating Asset Need for Fire Extinguisher is rejected
        $needRes = $this->actingAs($this->admin)->post(route('asset-needs.store'), [
            'asset_id' => $this->extinguisherAsset->id,
            'requirement' => 'ABC Chemical Powder Refill',
            'unit' => 'Kg',
            'maximum_capacity' => 10,
            'minimum_level' => 2,
            'current_level' => 5,
        ]);
        $needRes->assertSessionHasErrors('asset_id');
        $this->assertDatabaseMissing('asset_needs', [
            'asset_id' => $this->extinguisherAsset->id,
        ]);

        // 2. Fire extinguisher asset is not in the dropdown on the page
        $pageRes = $this->actingAs($this->admin)->get(route('operational-requirements.index', ['tab' => 'needs']));
        $pageRes->assertOk();
        $pageRes->assertDontSee("Select Equipment...\n                    <option value=\"{$this->extinguisherAsset->id}\"");

        // 3. Creating Operational Requirement for Fire Extinguisher is rejected
        $opRes = $this->actingAs($this->admin)->post(route('operational-requirements.store'), [
            'asset_type_id' => $this->extinguisherType->id,
            'requirement_type' => 'Extinguisher ABC Refill',
            'unit' => 'Kg',
            'maximum_capacity' => 50,
            'minimum_level' => 10,
            'current_level' => 20,
        ]);
        $opRes->assertSessionHas('error');
        $this->assertDatabaseMissing('operational_requirements', [
            'requirement_type' => 'Extinguisher ABC Refill',
        ]);
    }

    /**
     * Test user credentials auto-generation with collision handling.
     */
    public function test_user_email_auto_generation_with_collision_handling(): void
    {
        // Existing user: cruz.juan@bdes.gov
        User::factory()->create([
            'first_name' => 'Juan',
            'last_name' => 'Cruz',
            'username' => 'cruzjuan',
            'email' => 'cruz.juan@bdes.gov',
        ]);

        $suggestRes = $this->actingAs($this->admin)->get(route('users.suggest-credentials', [
            'first_name' => 'Juan',
            'last_name' => 'Cruz',
        ]));

        $suggestRes->assertOk();
        $data = $suggestRes->json();
        $this->assertEquals('cruzjuan2', $data['username']);
        $this->assertEquals('cruz.juan2@bdes.gov', $data['email']);
        $this->assertTrue($data['has_conflict']);

        // Create new user with duplicate name and verify it auto-increments cleanly
        $createRes = $this->actingAs($this->admin)->post(route('users.store'), [
            'first_name' => 'Juan',
            'last_name' => 'Cruz',
            'role' => 'viewer',
        ]);

        $createRes->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'username' => 'cruzjuan2',
            'email' => 'cruz.juan2@bdes.gov',
        ]);
    }

    /**
     * Test that Account Settings page does not display the delete account button or modal.
     */
    public function test_account_settings_page_hides_delete_account_option(): void
    {
        $response = $this->actingAs($this->admin)->get(route('profile.edit'));
        $response->assertOk();
        $response->assertDontSee('Delete Account');
        $response->assertDontSee('Confirm Account Deletion');
        $response->assertDontSee('deletion_password');
    }
}
