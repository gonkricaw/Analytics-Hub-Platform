<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create admin user if it doesn't exist
        $adminUser = User::where('email', 'admin@indonet.co.id')->first();

        if (!$adminUser) {
            $adminUser = User::create([
                'name' => 'Admin',
                'email' => 'admin@indonet.co.id',
                'password' => Hash::make('Ind0n3t@dm1n'), // Secure default password - should be changed after first login
                'position' => 'System Administrator',
                'department' => 'IT',
                'is_active' => true,
                'force_password_change' => true, // Force password change on first login
                'email_verified_at' => now(),
            ]);

            $this->command->info('Admin user created successfully.');
        } else {
            $this->command->info('Admin user already exists.');
        }

        // Get admin role
        $adminRole = Role::where('name', 'admin')->first();

        if (!$adminRole) {
            $this->command->error('Admin role not found. Make sure to run the RoleSeeder first.');
            return;
        }

        // Assign admin role to admin user if not already assigned
        if (!$adminUser->hasRole('admin')) {
            $adminUser->roles()->attach($adminRole);
            $this->command->info('Admin role assigned to admin user.');
        } else {
            $this->command->info('Admin user already has admin role.');
        }
    }
}
