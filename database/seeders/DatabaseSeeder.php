<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run permissions seeder first
        $this->call(PermissionSeeder::class);

        // Then roles seeder (which assigns permissions to roles)
        $this->call(RoleSeeder::class);

        // Then run admin user seeder
        $this->call(AdminUserSeeder::class);

        // Create regular user for testing
        $user = User::firstOrCreate(
            ['email' => 'user@indonet.com'],
            [
                'name' => 'Regular User',
                'email' => 'user@indonet.com',
                'password' => Hash::make('User@123'),
                'position' => 'Staff',
                'department' => 'Operations',
                'is_active' => true,
                'force_password_change' => false,
                'email_verified_at' => now()
            ]
        );

        // Assign user role to regular user
        $userRole = Role::where('name', 'user')->first();
        $user->roles()->sync([$userRole->id]);

        // Create sample terms & conditions
        $this->call(TermAndConditionSeeder::class);

        // Create sample menu items
        $this->call(MenuItemSeeder::class);

        // Create email templates
        $this->call(EmailTemplateSeeder::class);

        // Create sample embedded URLs
        $this->call(EmbeddedUrlSeeder::class);

        // Create sample system notifications
        $this->call(SystemNotificationSeeder::class);
    }
}
