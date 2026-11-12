<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileAvatarUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_a_user_can_upload_a_profile_picture(): void
    {
        $user = User::factory()
            ->create();

        $response = $this->actingAs($user)
            ->postJson('/api/settings/profile/avatar', [
                'avatar' => UploadedFile::fake()->image('me.jpg'),
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Profile picture updated.');

        $path = $user
            ->refresh()
            ->getRawOriginal('avatar');

        $this->assertStringStartsWith(User::AVATAR_UPLOAD_DIRECTORY . '/', $path);
        $this->assertTrue($user->hasUploadedAvatar());
        $this->assertSame(asset('storage/' . $path), $response->json('avatar'));
        Storage::disk('public')
            ->assertExists($path);
    }

    public function test_the_signed_in_user_includes_the_full_avatar_url(): void
    {
        $user = User::factory()
            ->create(['avatar' => User::AVATAR_UPLOAD_DIRECTORY . '/me.jpg']);

        $this->actingAs($user)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('avatar', asset('storage/' . User::AVATAR_UPLOAD_DIRECTORY . '/me.jpg'));
    }

    public function test_a_user_without_a_picture_has_no_avatar(): void
    {
        $user = User::factory()
            ->create();

        $this->actingAs($user)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('avatar', null);
    }

    public function test_uploading_a_new_picture_deletes_the_previous_upload(): void
    {
        $user = User::factory()
            ->create();

        $this->actingAs($user)
            ->postJson('/api/settings/profile/avatar', [
                'avatar' => UploadedFile::fake()->image('first.jpg'),
            ])
            ->assertOk();

        $first = $user
            ->refresh()
            ->getRawOriginal('avatar');

        $this->actingAs($user)
            ->postJson('/api/settings/profile/avatar', [
                'avatar' => UploadedFile::fake()->image('second.png'),
            ])
            ->assertOk();

        Storage::disk('public')
            ->assertMissing($first);
        Storage::disk('public')
            ->assertExists($user->refresh()->getRawOriginal('avatar'));
    }

    public function test_the_upload_must_be_a_small_enough_image(): void
    {
        $user = User::factory()
            ->create();

        $this->actingAs($user)
            ->postJson('/api/settings/profile/avatar', [
                'avatar' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('avatar');

        $this->actingAs($user)
            ->postJson('/api/settings/profile/avatar', [
                'avatar' => UploadedFile::fake()
                    ->image('huge.jpg')
                    ->size(User::MAX_AVATAR_KB + 1),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('avatar');

        $this->assertFalse($user->refresh()->hasUploadedAvatar());
    }

    public function test_deleting_an_account_deletes_the_uploaded_picture(): void
    {
        $user = User::factory()
            ->create();

        $this->actingAs($user)
            ->postJson('/api/settings/profile/avatar', [
                'avatar' => UploadedFile::fake()->image('me.jpg'),
            ])
            ->assertOk();

        $path = $user
            ->refresh()
            ->getRawOriginal('avatar');

        $this->actingAs($user)
            ->deleteJson('/api/settings/profile', [
                'password' => 'password',
            ])
            ->assertOk();

        Storage::disk('public')
            ->assertMissing($path);
    }

    public function test_guests_cannot_upload_a_profile_picture(): void
    {
        $this->postJson('/api/settings/profile/avatar', [
            'avatar' => UploadedFile::fake()->image('me.jpg'),
        ])
            ->assertUnauthorized();
    }
}
