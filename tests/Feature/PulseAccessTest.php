<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pulse is open to the same people as Telescope (TelescopeServiceProvider's
 * gate); everyone else is turned away outside local development.
 */
class PulseAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_pulse_is_closed_to_everyone_else_outside_local_development(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/pulse')
            ->assertForbidden();

        $this->get('/pulse')
            ->assertForbidden();
    }

    public function test_pulse_is_open_to_telescope_admins(): void
    {
        $admin = User::factory()
            ->create(['email' => 'alphaxardgacuuru47@gmail.com']);

        $this->actingAs($admin)
            ->get('/pulse')
            ->assertOk();
    }
}
