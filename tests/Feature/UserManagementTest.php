<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name'      => 'System Administrator',
            'username'  => 'admin',
            'email'     => 'admin@bdes.gov',
            'role'      => 'administrator',
            'is_active' => true,
        ]);

        $this->member = User::factory()->create([
            'name'      => 'Inventory Officer',
            'username'  => 'inventory_officer',
            'email'     => 'inventory@bdes.gov',
            'role'      => 'inventory_officer',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_user_management_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('users.index'));

        $response->assertOk();
        $response->assertSee('User Management');
        $response->assertSee('Add New User');
        $response->assertSee('name="name"', false);
        $response->assertSee('name="username"', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('name="password"', false);
    }

    public function test_admin_can_create_new_user_with_standard_modal_fields(): void
    {
        $userData = [
            'name'                  => 'Officer Juan Dela Cruz',
            'username'              => 'jdelacruz',
            'email'                 => 'jdelacruz@bdes.gov',
            'role'                  => 'inventory_officer',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->actingAs($this->admin)->post(route('users.store'), $userData);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name'      => 'Officer Juan Dela Cruz',
            'username'  => 'jdelacruz',
            'email'     => 'jdelacruz@bdes.gov',
            'role'      => 'inventory_officer',
            'is_active' => true,
        ]);

        $created = User::where('username', 'jdelacruz')->first();
        $this->assertTrue(Hash::check('password123', $created->password));
    }

    public function test_user_creation_with_first_name_and_last_name_fallback(): void
    {
        $userData = [
            'first_name'            => 'Maria',
            'last_name'             => 'Santos',
            'email'                 => 'msantos@bdes.gov',
            'role'                  => 'maintenance_officer',
            'password'              => 'securepass123',
            'password_confirmation' => 'securepass123',
        ];

        $response = $this->actingAs($this->admin)->post(route('users.store'), $userData);

        $response->assertRedirect(route('users.index'));
        $user = User::where('email', 'msantos@bdes.gov')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Maria Santos', $user->name);
        $this->assertEquals('msantos', $user->username);
        $this->assertEquals('maintenance_officer', $user->role);
        $this->assertTrue($user->is_active);
    }

    public function test_user_creation_validation_errors(): void
    {
        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            '_form'                 => 'add_user',
            'name'                  => '',
            'username'              => '',
            'email'                 => '',
            'password'              => '',
            'password_confirmation' => '',
            'role'                  => '',
        ]);

        $response->assertSessionHasErrors(['name', 'username', 'email', 'password', 'role']);
    }

    public function test_admin_can_update_user(): void
    {
        $target = User::factory()->create([
            'name'     => 'Old Name',
            'username' => 'olduser',
            'email'    => 'olduser@bdes.gov',
            'role'     => 'viewer',
        ]);

        $response = $this->actingAs($this->admin)->put(route('users.update', $target), [
            'name'     => 'Updated Name',
            'username' => 'updateduser',
            'email'    => 'updateduser@bdes.gov',
            'role'     => 'inventory_officer',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'id'       => $target->id,
            'name'     => 'Updated Name',
            'username' => 'updateduser',
            'email'    => 'updateduser@bdes.gov',
            'role'     => 'inventory_officer',
        ]);
    }

    public function test_admin_can_toggle_account_active_status(): void
    {
        $target = User::factory()->create([
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->patch(route('users.toggle-active', $target));
        $response->assertRedirect(route('users.index'));
        $this->assertFalse($target->fresh()->is_active);

        $this->actingAs($this->admin)->patch(route('users.toggle-active', $target));
        $this->assertTrue($target->fresh()->is_active);
    }

    public function test_admin_can_reset_user_password(): void
    {
        $target = User::factory()->create([
            'password' => Hash::make('original_pass'),
        ]);

        $response = $this->actingAs($this->admin)->post(route('users.reset-password', $target));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');
        $this->assertFalse(Hash::check('original_pass', $target->fresh()->password));
    }

    public function test_non_admin_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->member)->get(route('users.index'));
        $response->assertForbidden();

        $response = $this->actingAs($this->member)->post(route('users.store'), [
            'name'                  => 'Hacker',
            'username'              => 'hacker',
            'email'                 => 'hacker@bdes.gov',
            'role'                  => 'administrator',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertForbidden();
    }

    public function test_admin_can_manage_locations(): void
    {
        // 1. Create Location
        $response = $this->actingAs($this->admin)->post(route('locations.store'), [
            'name'             => 'Purok 5 Coastal Warehouse',
            'type'             => 'depot',
            'address'          => 'Zone 5 Seaside Boulevard',
            'storage_capacity' => 120,
        ]);

        $response->assertRedirect(route('locations.index'));
        $this->assertDatabaseHas('locations', [
            'name'             => 'Purok 5 Coastal Warehouse',
            'type'             => 'depot',
            'storage_capacity' => 120,
        ]);

        $location = Location::where('name', 'Purok 5 Coastal Warehouse')->first();

        // 2. Update Location
        $response = $this->actingAs($this->admin)->put(route('locations.update', $location), [
            'name'             => 'Purok 5 Upgraded Warehouse',
            'type'             => 'storage',
            'address'          => 'Zone 5 Seaside Boulevard New Annex',
            'storage_capacity' => 250,
        ]);

        $response->assertRedirect(route('locations.index'));
        $this->assertDatabaseHas('locations', [
            'id'               => $location->id,
            'name'             => 'Purok 5 Upgraded Warehouse',
            'storage_capacity' => 250,
        ]);

        // 3. Delete Location
        $response = $this->actingAs($this->admin)->delete(route('locations.destroy', $location));
        $response->assertRedirect(route('locations.index'));
        $this->assertDatabaseMissing('locations', ['id' => $location->id]);
    }

    public function test_roles_page_renders_system_roles_cleanly(): void
    {
        $response = $this->actingAs($this->admin)->get(route('roles.index'));

        $response->assertOk();
        $response->assertSeeText('Role & Access Control');
        $response->assertSeeText('Administrator');
        $response->assertSeeText('Hon. Barangay Captain');
        $response->assertSeeText('Inventory Officer');
        $response->assertSeeText('Maintenance Officer');
        $response->assertSeeText('Viewer / Staff');
        $response->assertSeeText('Role Permissions & Authority Matrix');
    }
}
