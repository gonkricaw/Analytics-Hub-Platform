<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear rate limiters for all the test routes
        RateLimiter::clear('api::');
    }

    /**
     * Test that the login endpoint is rate limited.
     *
     * @return void
     */
    public function test_login_endpoint_is_rate_limited()
    {
        // Credentials don't matter for this test since we just want to hit the rate limit
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'password',
        ];

        // Make 5 requests (which is the limit per minute)
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/auth/login', $credentials);
            // We'll get 401 or 422 since the credentials are invalid, but not 429
            $this->assertNotEquals(429, $response->getStatusCode(), "Got rate limited too early on attempt {$i}");

            // Wait a short time between requests to avoid any race conditions
            if ($i < 4) {
                sleep(1);
            }
        }

        // Wait for any ongoing requests to complete and rate limiting to take effect
        sleep(2);

        // The 6th request should be rate limited
        $response = $this->postJson('/api/auth/login', $credentials);
        $response->assertStatus(429); // Too Many Requests
    }

    /**
     * Test that the forgot password endpoint is rate limited.
     *
     * @return void
     */
    public function test_forgot_password_endpoint_is_rate_limited()
    {
        // Clear any rate limiters
        RateLimiter::clear('login');

        $requestData = [
            'email' => 'test@example.com',
        ];

        // Make 5 requests (which is the limit per minute)
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/auth/forgot-password', $requestData);
            // We expect this to be 200 or 422, but not 429
            $this->assertNotEquals(429, $response->getStatusCode(), "Got rate limited too early on attempt {$i}");

            // Wait a short time between requests to avoid any race conditions
            if ($i < 4) {
                sleep(1);
            }
        }

        // Wait for any ongoing requests to complete and rate limiting to take effect
        sleep(2);

        // The 6th request should be rate limited
        $response = $this->postJson('/api/auth/forgot-password', $requestData);
        $response->assertStatus(429); // Too Many Requests
    }

    /**
     * Test that the reset password endpoint is rate limited.
     *
     * @return void
     */
    public function test_reset_password_endpoint_is_rate_limited()
    {
        // Clear any rate limiters
        RateLimiter::clear('login');

        $requestData = [
            'email' => 'test@example.com',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
            'token' => 'invalid-token',
        ];

        // Make 5 requests (which is the limit per minute)
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/auth/reset-password', $requestData);
            // We expect this to be 422 or other error, but not 429
            $this->assertNotEquals(429, $response->getStatusCode(), "Got rate limited too early on attempt {$i}");

            // Wait a short time between requests to avoid any race conditions
            if ($i < 4) {
                sleep(1);
            }
        }

        // Wait for any ongoing requests to complete and rate limiting to take effect
        sleep(2);

        // The 6th request should be rate limited
        $response = $this->postJson('/api/auth/reset-password', $requestData);
        $response->assertStatus(429); // Too Many Requests
    }

    /**
     * Test that other endpoints like registration are not rate limited as strictly.
     *
     * @return void
     */
    public function test_registration_endpoint_not_using_strict_rate_limiting()
    {
        // Clear any rate limiters
        RateLimiter::clear('login');

        // This test is slightly tricky because the registration endpoint
        // is still covered by the global API rate limiter.
        // We'll just make a few requests to verify it's not as strict.

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        // Make several requests - more than the strict login limit
        for ($i = 0; $i < 6; $i++) {
            // Generate unique email for each attempt
            $userData['email'] = "test{$i}@example.com";

            $response = $this->postJson('/api/auth/register', $userData);
            // We're not expecting a 429 here since registration is not under strict rate limiting
            $this->assertNotEquals(429, $response->getStatusCode(), "Registration was rate limited on attempt {$i}");
        }
    }
}
