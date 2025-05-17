<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\SystemNotification;
use App\Models\UserNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class DistributeSystemNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The notification instance.
     *
     * @var \App\Models\SystemNotification
     */
    protected $notification;

    /**
     * The user IDs to distribute to.
     *
     * @var array|null
     */
    protected $userIds;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\SystemNotification $notification
     * @param array|null $userIds Specific user IDs to distribute to, or null for all active users
     */
    public function __construct(SystemNotification $notification, $userIds = null)
    {
        $this->notification = $notification;
        $this->userIds = $userIds;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting notification distribution job', ['notification_id' => $this->notification->id]);

        try {
            // If notification is global or no specific user IDs provided, send to all active users
            if ($this->notification->is_global || $this->userIds === null) {
                $users = User::where('is_active', true)->get();
            } else {
                // Otherwise, get the specified users
                $users = User::whereIn('id', $this->userIds)->where('is_active', true)->get();
            }

            $count = 0;
            foreach ($users as $user) {
                // Check if the user already has this notification
                $exists = UserNotification::where('user_id', $user->id)
                    ->where('notification_id', $this->notification->id)
                    ->exists();

                if (!$exists) {
                    UserNotification::create([
                        'user_id' => $user->id,
                        'notification_id' => $this->notification->id,
                        'is_read' => false
                    ]);
                    $count++;
                }
            }

            Log::info('Notification distribution completed', [
                'notification_id' => $this->notification->id,
                'users_count' => $count
            ]);
        } catch (\Exception $e) {
            Log::error('Error distributing notification', [
                'notification_id' => $this->notification->id,
                'error' => $e->getMessage()
            ]);

            throw $e; // Re-throw the exception to mark the job as failed
        }
    }
}
