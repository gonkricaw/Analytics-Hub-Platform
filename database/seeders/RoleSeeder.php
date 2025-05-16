<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default roles
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Administrator with full access to all features'
            ],
            [
                'name' => 'manager',
                'display_name' => 'Manager',
                'description' => 'Manager with limited administrative access'
            ],
            [
                'name' => 'user',
                'display_name' => 'User',
                'description' => 'Regular user with basic access'
            ],
        ];

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(['name' => $roleData['name']], $roleData);
        }

        // Assign all permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();
        $allPermissions = Permission::all();
        $adminRole->syncPermissions($allPermissions);

        // Assign specific permissions to manager role
        $managerRole = Role::where('name', 'manager')->first();
        $managerPermissions = Permission::whereIn('name', [
            'user-view', 'user-create', 'user-update',
            'content-view', 'content-create', 'content-update',
            'notification-view', 'notification-create',
            'email-template-view',
            'menu-view',
            'terms-view',
        ])->get();
        $managerRole->syncPermissions($managerPermissions);

        // Assign basic permissions to user role
        $userRole = Role::where('name', 'user')->first();
        $userPermissions = Permission::whereIn('name', [
            'content-view',
            'notification-view',
        ])->get();
        $userRole->syncPermissions($userPermissions);
    }
}
