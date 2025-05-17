<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivityLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'activity_type',
        'activity_details',
        'ip_address',
        'user_agent',
        'device',
        'browser',
        'os',
        'related_model_type',
        'related_model_id'
    ];

    /**
     * Get the user that owns the activity log.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related model
     */
    public function relatedModel()
    {
        return $this->morphTo('related_model');
    }

    /**
     * Scope a query to only include login activities.
     */
    public function scopeLogins($query)
    {
        return $query->where('activity_type', 'login');
    }

    /**
     * Scope a query to get recent activities.
     */
    public function scopeRecent($query, $days = 15)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Log user activity
     *
     * @param int $userId
     * @param string $activityType
     * @param string|null $activityDetails
     * @param string|null $ipAddress
     * @param string|null $userAgent
     * @param Model|null $relatedModel
     * @return UserActivityLog
     */
    public static function logActivity($userId, $activityType, $activityDetails = null, $ipAddress = null, $userAgent = null, $relatedModel = null)
    {
        $data = [
            'user_id' => $userId,
            'activity_type' => $activityType,
            'activity_details' => $activityDetails,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ];

        // Detect device, browser, and OS from user agent
        if ($userAgent) {
            $data['device'] = self::detectDevice($userAgent);
            $data['browser'] = self::detectBrowser($userAgent);
            $data['os'] = self::detectOS($userAgent);
        }

        // Add related model if provided
        if ($relatedModel) {
            $data['related_model_type'] = get_class($relatedModel);
            $data['related_model_id'] = $relatedModel->id;
        }

        return self::create($data);
    }

    /**
     * Simple device detection
     */
    protected static function detectDevice($userAgent)
    {
        if (preg_match('/(tablet|ipad|playbook)/i', $userAgent)) {
            return 'Tablet';
        }

        if (preg_match('/(mobile|android|iphone|ipod|phone)/i', $userAgent)) {
            return 'Mobile';
        }

        return 'Desktop';
    }

    /**
     * Simple browser detection
     */
    protected static function detectBrowser($userAgent)
    {
        if (preg_match('/MSIE|Trident/i', $userAgent)) {
            return 'Internet Explorer';
        }
        if (preg_match('/Firefox/i', $userAgent)) {
            return 'Firefox';
        }
        if (preg_match('/Chrome/i', $userAgent)) {
            return 'Chrome';
        }
        if (preg_match('/Safari/i', $userAgent)) {
            return 'Safari';
        }
        if (preg_match('/Opera|OPR/i', $userAgent)) {
            return 'Opera';
        }
        if (preg_match('/Edge/i', $userAgent)) {
            return 'Edge';
        }

        return 'Unknown';
    }

    /**
     * Simple OS detection
     */
    protected static function detectOS($userAgent)
    {
        if (preg_match('/Windows/i', $userAgent)) {
            return 'Windows';
        }
        if (preg_match('/Mac OS X/i', $userAgent)) {
            return 'Mac OS';
        }
        if (preg_match('/Linux/i', $userAgent)) {
            return 'Linux';
        }
        if (preg_match('/Android/i', $userAgent)) {
            return 'Android';
        }
        if (preg_match('/(iPhone|iPad|iPod)/i', $userAgent)) {
            return 'iOS';
        }

        return 'Unknown';
    }
}
