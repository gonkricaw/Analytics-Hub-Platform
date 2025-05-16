<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Main dashboard
        $dashboard = MenuItem::create([
            'title' => 'Dashboard',
            'slug' => 'dashboard',
            'url' => '/dashboard',
            'icon' => 'dashboard',
            'order' => 1,
            'permissions' => [],
            'is_active' => true,
        ]);

        // User management menu
        $userManagement = MenuItem::create([
            'title' => 'User Management',
            'slug' => 'user-management',
            'icon' => 'people',
            'order' => 2,
            'permissions' => ['user-view'],
            'is_active' => true,
        ]);

        // User management sub-items
        MenuItem::create([
            'title' => 'Users',
            'slug' => 'users',
            'url' => '/users',
            'icon' => 'person',
            'order' => 1,
            'parent_id' => $userManagement->id,
            'permissions' => ['user-view'],
            'is_active' => true,
        ]);

        MenuItem::create([
            'title' => 'Roles',
            'slug' => 'roles',
            'url' => '/roles',
            'icon' => 'assignment_ind',
            'order' => 2,
            'parent_id' => $userManagement->id,
            'permissions' => ['role-view'],
            'is_active' => true,
        ]);

        MenuItem::create([
            'title' => 'Permissions',
            'slug' => 'permissions',
            'url' => '/permissions',
            'icon' => 'security',
            'order' => 3,
            'parent_id' => $userManagement->id,
            'permissions' => ['permission-view'],
            'is_active' => true,
        ]);

        // Content management menu
        $contentManagement = MenuItem::create([
            'title' => 'Content Management',
            'slug' => 'content-management',
            'icon' => 'article',
            'order' => 3,
            'permissions' => ['content-view'],
            'is_active' => true,
        ]);

        // Content management sub-items
        MenuItem::create([
            'title' => 'Pages',
            'slug' => 'pages',
            'url' => '/content/pages',
            'icon' => 'description',
            'order' => 1,
            'parent_id' => $contentManagement->id,
            'permissions' => ['content-view'],
            'is_active' => true,
        ]);

        MenuItem::create([
            'title' => 'News',
            'slug' => 'news',
            'url' => '/content/news',
            'icon' => 'feed',
            'order' => 2,
            'parent_id' => $contentManagement->id,
            'permissions' => ['content-view'],
            'is_active' => true,
        ]);

        MenuItem::create([
            'title' => 'FAQs',
            'slug' => 'faqs',
            'url' => '/content/faqs',
            'icon' => 'help',
            'order' => 3,
            'parent_id' => $contentManagement->id,
            'permissions' => ['content-view'],
            'is_active' => true,
        ]);

        // System settings menu
        $systemSettings = MenuItem::create([
            'title' => 'System Settings',
            'slug' => 'system-settings',
            'icon' => 'settings',
            'order' => 4,
            'permissions' => ['email-template-view', 'terms-view'],
            'is_active' => true,
        ]);

        // System settings sub-items
        MenuItem::create([
            'title' => 'Email Templates',
            'slug' => 'email-templates',
            'url' => '/email-templates',
            'icon' => 'email',
            'order' => 1,
            'parent_id' => $systemSettings->id,
            'permissions' => ['email-template-view'],
            'is_active' => true,
        ]);

        MenuItem::create([
            'title' => 'Terms & Conditions',
            'slug' => 'terms-and-conditions',
            'url' => '/terms',
            'icon' => 'gavel',
            'order' => 2,
            'parent_id' => $systemSettings->id,
            'permissions' => ['terms-view'],
            'is_active' => true,
        ]);

        MenuItem::create([
            'title' => 'Menu Management',
            'slug' => 'menu-management',
            'url' => '/menu-items',
            'icon' => 'menu',
            'order' => 3,
            'parent_id' => $systemSettings->id,
            'permissions' => ['menu-view'],
            'is_active' => true,
        ]);

        // Notifications menu
        MenuItem::create([
            'title' => 'Notifications',
            'slug' => 'notifications',
            'url' => '/notifications',
            'icon' => 'notifications',
            'order' => 5,
            'permissions' => ['notification-view'],
            'is_active' => true,
        ]);

        // User profile
        MenuItem::create([
            'title' => 'My Profile',
            'slug' => 'my-profile',
            'url' => '/profile',
            'icon' => 'account_circle',
            'order' => 6,
            'permissions' => [],
            'is_active' => true,
        ]);
    }
}
