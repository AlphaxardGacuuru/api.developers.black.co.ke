<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_updated(): void
    {
        $user = User::factory()
            ->create();

        $this->actingAs($user)
            ->putJson('/api/settings/password', [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Password Updated');

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }

    public function test_the_current_password_must_be_correct(): void
    {
        $user = User::factory()
            ->create();

        $this->actingAs($user)
            ->putJson('/api/settings/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('current_password');

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }

    public function test_the_new_password_must_be_confirmed(): void
    {
        $user = User::factory()
            ->create();

        $this->actingAs($user)
            ->putJson('/api/settings/password', [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'other-password',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }

    public function test_guests_cannot_change_a_password(): void
    {
        $this->putJson('/api/settings/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
            ->assertUnauthorized();
    }
}
