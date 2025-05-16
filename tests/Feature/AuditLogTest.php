<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test that audit logs are created when a model is created.
     *
     * @return void
     */
    public function test_audit_logs_created_on_model_creation()
    {
        $this->assertDatabaseCount('audit_logs', 0);

        $user = User::factory()->create();

        $this->assertDatabaseCount('audit_logs', 1);

        $log = AuditLog::first();
        $this->assertEquals('created', $log->action);
        $this->assertEquals(User::class, $log->model_type);
        $this->assertEquals($user->id, $log->model_id);
    }

    /**
     * Test that audit logs are created when a model is updated.
     *
     * @return void
     */
    public function test_audit_logs_created_on_model_update()
    {
        $user = User::factory()->create();

        // Clear existing logs
        AuditLog::truncate();
        $this->assertDatabaseCount('audit_logs', 0);

        // Update the user
        $oldName = $user->name;
        $user->name = 'Updated Name';
        $user->save();

        $this->assertDatabaseCount('audit_logs', 1);

        $log = AuditLog::first();
        $this->assertEquals('updated', $log->action);
        $this->assertEquals(User::class, $log->model_type);
        $this->assertEquals($user->id, $log->model_id);

        // Check that the old values contain the previous name
        $this->assertEquals($oldName, $log->old_values['name']);
        // Check that the new values contain the new name
        $this->assertEquals('Updated Name', $log->new_values['name']);
    }

    /**
     * Test that audit logs are created when a model is deleted.
     *
     * @return void
     */
    public function test_audit_logs_created_on_model_deletion()
    {
        $user = User::factory()->create();

        // Clear existing logs
        AuditLog::truncate();
        $this->assertDatabaseCount('audit_logs', 0);

        $userId = $user->id;
        $user->delete();

        $this->assertDatabaseCount('audit_logs', 1);

        $log = AuditLog::first();
        $this->assertEquals('deleted', $log->action);
        $this->assertEquals(User::class, $log->model_type);
        $this->assertEquals($userId, $log->model_id);
    }

    /**
     * Test that admin can access the audit logs API.
     *
     * @return void
     */
    public function test_admin_can_access_audit_logs()
    {
        // Create an admin role
        $adminRole = Role::create(['name' => 'Admin']);

        // Create a user with admin role
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);

        // Create some audit logs
        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'test-action',
            'model_type' => User::class,
            'model_id' => $admin->id,
        ]);

        // Mock the hasPermission method to return true for audit-log-view
        $this->partialMock(User::class, function ($mock) {
            $mock->shouldReceive('hasPermission')
                ->with('audit-log-view')
                ->andReturn(true);
        });

        $this->actingAs($admin)
            ->getJson('/api/admin/audit-logs')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'action',
                        'model_type',
                        'model_id',
                        'created_at',
                        'updated_at',
                    ]
                ],
                'links',
                'meta',
            ]);
    }

    /**
     * Test that non-admin users cannot access the audit logs API.
     *
     * @return void
     */
    public function test_non_admin_cannot_access_audit_logs()
    {
        // Create a regular user
        $user = User::factory()->create();

        // Mock the hasPermission method to return false for audit-log-view
        $this->partialMock(User::class, function ($mock) {
            $mock->shouldReceive('hasPermission')
                ->with('audit-log-view')
                ->andReturn(false);
        });

        $this->actingAs($user)
            ->getJson('/api/admin/audit-logs')
            ->assertStatus(403);
    }
}
