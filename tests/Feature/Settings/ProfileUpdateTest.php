<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()
            ->create();

        $this->actingAs($user)
            ->patchJson('/api/settings/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Profile Updated')
            ->assertJsonPath('data.email', 'test@example.com');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_is_unchanged(): void
    {
        $user = User::factory()
            ->create();

        $this->actingAs($user)
            ->patchJson('/api/settings/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ])
            ->assertOk();

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_the_email_must_be_valid_and_unused(): void
    {
        $user = User::factory()
            ->create();
        $other = User::factory()
            ->create();

        $this->actingAs($user)
            ->patchJson('/api/settings/profile', [
                'name' => '',
                'email' => $other->email,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()
            ->create();

        $this->actingAs($user)
            ->deleteJson('/api/settings/profile', ['password' => 'password'])
            ->assertOk()
            ->assertJsonPath('message', 'Account Deleted');

        $this->assertModelMissing($user);
    }

    public function test_the_correct_password_must_be_given_to_delete_an_account(): void
    {
        $user = User::factory()
            ->create();

        $this->actingAs($user)
            ->deleteJson('/api/settings/profile', ['password' => 'wrong-password'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');

        $this->assertModelExists($user);
    }

    public function test_guests_cannot_change_a_profile(): void
    {
        $this->patchJson('/api/settings/profile', ['name' => 'Test User', 'email' => 'test@example.com'])
            ->assertUnauthorized();

        $this->deleteJson('/api/settings/profile', ['password' => 'password'])
            ->assertUnauthorized();
    }
}
