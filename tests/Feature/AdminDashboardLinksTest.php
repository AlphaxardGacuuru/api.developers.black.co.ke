<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * /api/user tells the frontend which admin dashboards to link to from the
 * sidebar.
 */
class AdminDashboardLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_admins_get_the_telescope_and_pulse_links(): void
    {
        config(['telescope.enabled' => true]);

        $admin = User::factory()
            ->create(['email' => 'alphaxardgacuuru47@gmail.com']);

        $this->actingAs($admin)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('email', 'alphaxardgacuuru47@gmail.com')
            ->assertJsonPath('can_view_telescope', true)
            ->assertJsonPath('can_view_pulse', true);
    }

    public function test_other_users_get_neither_link(): void
    {
        config(['telescope.enabled' => true]);

        $this->actingAs(User::factory()->create())
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('can_view_telescope', false)
            ->assertJsonPath('can_view_pulse', false);
    }

    public function test_the_telescope_link_is_hidden_while_telescope_is_disabled(): void
    {
        config(['telescope.enabled' => false]);

        $admin = User::factory()
            ->create(['email' => 'alphaxardgacuuru47@gmail.com']);

        $this->actingAs($admin)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('can_view_telescope', false);
    }
}
