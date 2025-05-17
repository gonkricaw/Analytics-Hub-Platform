<?php

namespace Tests\Feature\Security;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
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
        // Clear existing logs
        AuditLog::truncate();
        $this->assertDatabaseCount('audit_logs', 0);

        // Create a user - this should trigger the audit log
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Check that an audit log was created
        $this->assertDatabaseCount('audit_logs', 1);

        $log = AuditLog::first();
        $this->assertEquals('created', $log->action);
        $this->assertEquals(User::class, $log->model_type);
        $this->assertEquals($user->id, $log->model_id);
        $this->assertEquals('Test User', $log->new_values['name']);
        $this->assertEquals('test@example.com', $log->new_values['email']);
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
        $adminRole = Role::create(['name' => 'admin']);

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

        // Create audit-log-view permission
        $permission = Permission::create([
            'name' => 'audit-log-view',
            'display_name' => 'View Audit Logs',
            'description' => 'Can view audit logs',
            'module' => 'audit'
        ]);

        // Attach permission to admin role
        $adminRole->permissions()->attach($permission);

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
        // Create a regular user with a role that doesn't have audit-log-view permission
        $user = User::factory()->create();
        $userRole = Role::create(['name' => 'user']);
        $user->roles()->attach($userRole);

        $this->actingAs($user)
            ->getJson('/api/admin/audit-logs')
            ->assertStatus(403);
    }
}
