<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        $user = User::factory()->create([
            'phone_number' => '+639171234567',
        ]);

        $response = $this->post('/forgot-password', ['phone_number' => '09171234567']);

        $response->assertSessionHas('status');
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => '+639171234567',
        ]);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        $user = User::factory()->create([
            'phone_number' => '+639171234567',
        ]);

        $this->post('/forgot-password', ['phone_number' => '09171234567']);

        $response = $this->get('/reset-password/sample-token?phone_number=' . urlencode('+639171234567'));

        $response->assertStatus(200);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create([
            'phone_number' => '+639171234567',
        ]);

        $plainToken = 'test-reset-token-123456';
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => '+639171234567'],
            [
                'token'      => Hash::make($plainToken),
                'created_at' => now(),
            ]
        );

        $response = $this->post('/reset-password', [
            'token'                 => $plainToken,
            'phone_number'          => '09171234567',
            'password'              => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));
    }
}
