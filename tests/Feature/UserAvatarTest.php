<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserAvatarTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test that a user can upload an avatar.
     *
     * @return void
     */
    public function test_user_can_upload_avatar()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        $file = UploadedFile::fake()->image('avatar.jpg', 400, 400);

        $response = $this->postJson('/api/user/avatar', [
            'avatar' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user',
                'avatar_url',
            ]);

        // Assert the file was stored
        $path = $user->fresh()->avatar;
        Storage::disk('public')->assertExists($path);
    }

    /**
     * Test that a user can remove their avatar.
     *
     * @return void
     */
    public function test_user_can_remove_avatar()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        // First upload an avatar
        $file = UploadedFile::fake()->image('avatar.jpg');
        $this->postJson('/api/user/avatar', ['avatar' => $file]);

        // Get the path to the avatar
        $path = $user->fresh()->avatar;

        // Now remove it
        $response = $this->postJson('/api/user/avatar/remove');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Avatar removed successfully',
            ]);

        // Assert the file was removed
        Storage::disk('public')->assertMissing($path);
        $this->assertNull($user->fresh()->avatar);
    }

    /**
     * Test that validation works for avatar uploads.
     *
     * @return void
     */
    public function test_avatar_upload_validation()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Test with oversized file
        $oversizedFile = UploadedFile::fake()->image('avatar.jpg')->size(3000); // 3MB
        $response = $this->postJson('/api/user/avatar', ['avatar' => $oversizedFile]);
        $response->assertStatus(422); // Validation error

        // Test with non-image file
        $textFile = UploadedFile::fake()->create('document.txt', 100, 'text/plain');
        $response = $this->postJson('/api/user/avatar', ['avatar' => $textFile]);
        $response->assertStatus(422); // Validation error
    }

    /**
     * Test that a user can retrieve their avatar URL.
     *
     * @return void
     */
    public function test_user_can_get_avatar_url()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/user/avatar');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'avatar_url',
            ]);
    }
}
