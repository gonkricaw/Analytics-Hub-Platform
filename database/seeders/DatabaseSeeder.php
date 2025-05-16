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

        // Create admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'powerbi@indonet.id'],
            [
                'name' => 'System Administrator',
                'email' => 'powerbi@indonet.id',
                'password' => Hash::make('Admin@123'),
                'email_verified_at' => now()
            ]
        );

        // Assign admin role to admin user
        $adminRole = Role::where('name', 'admin')->first();
        $adminUser->roles()->sync([$adminRole->id]);

        // Create regular user
        $user = User::firstOrCreate(
            ['email' => 'user@indonet.com'],
            [
                'name' => 'Regular User',
                'email' => 'user@indonet.com',
                'password' => Hash::make('User@123'),
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
    }
}
