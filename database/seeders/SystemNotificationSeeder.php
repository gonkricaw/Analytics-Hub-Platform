<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemNotification;
use App\Models\UserNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SystemNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get an admin user to be the creator of notifications
        $adminUser = User::whereHas('roles', function($q) {
            $q->where('name', 'admin');
        })->first();

        if (!$adminUser) {
            $adminUser = User::first();
        }

        if (!$adminUser) {
            $this->command->error('No users found. Please run UserSeeder first.');
            return;
        }

        // Define sample notifications
        $notifications = [
            [
                'title' => 'Welcome to Indonet Analytics Hub',
                'message' => 'Welcome to the Indonet Analytics Hub Platform. This is your central dashboard for analytics and insights.',
                'type' => 'info',
                'is_global' => true,
                'user_id' => $adminUser->id,
                'expires_at' => Carbon::now()->addMonths(1),
                'link' => '/dashboard'
            ],
            [
                'title' => 'System Maintenance Scheduled',
                'message' => 'We will be performing system maintenance on ' . Carbon::now()->addDays(5)->format('F d, Y') . ' from 10:00 PM to 2:00 AM. Some services might be unavailable during this time.',
                'type' => 'warning',
                'is_global' => true,
                'user_id' => $adminUser->id,
                'expires_at' => Carbon::now()->addDays(7),
                'link' => null
            ],
            [
                'title' => 'New Analytics Report Available',
                'message' => 'Your monthly analytics report is now available for viewing. Check it out in the Reports section.',
                'type' => 'success',
                'is_global' => true,
                'user_id' => $adminUser->id,
                'expires_at' => Carbon::now()->addDays(14),
                'link' => '/reports/monthly'
            ],
            [
                'title' => 'Update Your Profile',
                'message' => 'Don\'t forget to keep your profile information up to date. This helps us provide you with personalized analytics.',
                'type' => 'info',
                'is_global' => true,
                'user_id' => $adminUser->id,
                'expires_at' => Carbon::now()->addMonths(2),
                'link' => '/profile'
            ],
            [
                'title' => 'Critical Security Update',
                'message' => 'We\'ve enhanced the security of our platform. No action is required on your part, but feel free to contact support if you notice any issues.',
                'type' => 'error',
                'is_global' => true,
                'user_id' => $adminUser->id,
                'expires_at' => Carbon::now()->addDays(10),
                'link' => null
            ]
        ];

        DB::beginTransaction();

        try {
            // Create notifications and link them to users
            foreach ($notifications as $notificationData) {
                // Create the notification
                $notification = SystemNotification::create($notificationData);

                // If it's a global notification, link it to all active users
                if ($notificationData['is_global']) {
                    $users = User::where('is_active', true)->get();
                    foreach ($users as $user) {
                        UserNotification::create([
                            'user_id' => $user->id,
                            'notification_id' => $notification->id,
                            'is_read' => false,
                            'read_at' => null
                        ]);
                    }
                }
            }

            DB::commit();
            $this->command->info('Sample notifications created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error('Error creating sample notifications: ' . $e->getMessage());
        }
    }
}
