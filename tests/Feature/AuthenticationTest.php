<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // Run seeders to set up roles and permissions
        $this->artisan('db:seed --class=PermissionSeeder');
        $this->artisan('db:seed --class=RoleSeeder');
    }

    /** @test */
    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'is_active' => true
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'token',
                    'user',
                    'force_password_change',
                    'needs_to_accept_terms'
                ]);
    }

    /** @test */
    public function test_user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'is_active' => true
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_inactive_user_cannot_login()
    {
        $user = User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => bcrypt('password123'),
            'is_active' => false
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'inactive@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function test_user_can_logout()
    {
        $user = User::factory()->create();

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                        ->postJson('/api/auth/logout');

        $response->assertStatus(200);

        // The token should be removed from the database
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    /** @test */
    public function test_user_can_get_own_details()
    {
        $user = User::factory()->create();

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                        ->getJson('/api/auth/me');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'user',
                    'force_password_change',
                    'needs_to_accept_terms'
                ]);
    }
}
