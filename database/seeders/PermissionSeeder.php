<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionsByModule = [
            'user' => [
                ['name' => 'user-view', 'display_name' => 'View Users', 'description' => 'Can view user list and details'],
                ['name' => 'user-create', 'display_name' => 'Create Users', 'description' => 'Can create new users'],
                ['name' => 'user-update', 'display_name' => 'Update Users', 'description' => 'Can update user details'],
                ['name' => 'user-delete', 'display_name' => 'Delete Users', 'description' => 'Can delete users'],
            ],
            'role' => [
                ['name' => 'role-view', 'display_name' => 'View Roles', 'description' => 'Can view roles and their permissions'],
                ['name' => 'role-create', 'display_name' => 'Create Roles', 'description' => 'Can create new roles'],
                ['name' => 'role-update', 'display_name' => 'Update Roles', 'description' => 'Can update roles and their permissions'],
                ['name' => 'role-delete', 'display_name' => 'Delete Roles', 'description' => 'Can delete roles'],
            ],
            'permission' => [
                ['name' => 'permission-view', 'display_name' => 'View Permissions', 'description' => 'Can view permissions list'],
            ],
            'terms' => [
                ['name' => 'terms-view', 'display_name' => 'View Terms & Conditions', 'description' => 'Can view terms and conditions'],
                ['name' => 'terms-create', 'display_name' => 'Create Terms & Conditions', 'description' => 'Can create new terms and conditions'],
                ['name' => 'terms-update', 'display_name' => 'Update Terms & Conditions', 'description' => 'Can update terms and conditions'],
                ['name' => 'terms-delete', 'display_name' => 'Delete Terms & Conditions', 'description' => 'Can delete terms and conditions'],
            ],
            'notification' => [
                ['name' => 'notification-view', 'display_name' => 'View Notifications', 'description' => 'Can view notifications'],
                ['name' => 'notification-create', 'display_name' => 'Create Notifications', 'description' => 'Can create new notifications'],
                ['name' => 'notification-update', 'display_name' => 'Update Notifications', 'description' => 'Can update notifications'],
                ['name' => 'notification-delete', 'display_name' => 'Delete Notifications', 'description' => 'Can delete notifications'],
            ],
            'email-template' => [
                ['name' => 'email-template-view', 'display_name' => 'View Email Templates', 'description' => 'Can view email templates'],
                ['name' => 'email-template-create', 'display_name' => 'Create Email Templates', 'description' => 'Can create new email templates'],
                ['name' => 'email-template-update', 'display_name' => 'Update Email Templates', 'description' => 'Can update email templates'],
                ['name' => 'email-template-delete', 'display_name' => 'Delete Email Templates', 'description' => 'Can delete email templates'],
            ],
            'content' => [
                ['name' => 'content-view', 'display_name' => 'View Content', 'description' => 'Can view content'],
                ['name' => 'content-create', 'display_name' => 'Create Content', 'description' => 'Can create new content'],
                ['name' => 'content-update', 'display_name' => 'Update Content', 'description' => 'Can update content'],
                ['name' => 'content-delete', 'display_name' => 'Delete Content', 'description' => 'Can delete content'],
            ],
            'menu' => [
                ['name' => 'menu-view', 'display_name' => 'View Menu Items', 'description' => 'Can view menu items'],
                ['name' => 'menu-create', 'display_name' => 'Create Menu Items', 'description' => 'Can create new menu items'],
                ['name' => 'menu-update', 'display_name' => 'Update Menu Items', 'description' => 'Can update menu items'],
                ['name' => 'menu-delete', 'display_name' => 'Delete Menu Items', 'description' => 'Can delete menu items'],
            ],
        ];

        foreach ($permissionsByModule as $module => $permissions) {
            foreach ($permissions as $permissionData) {
                $permissionData['module'] = $module;
                Permission::firstOrCreate(
                    ['name' => $permissionData['name']],
                    $permissionData
                );
            }
        }
    }
}
