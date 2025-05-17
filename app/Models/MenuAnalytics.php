<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuAnalytics extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'menu_item_id',
        'user_id',
        'access_count',
        'last_accessed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'last_accessed_at' => 'datetime',
    ];

    /**
     * Get the menu item that this analytics entry belongs to.
     */
    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id');
    }

    /**
     * Get the user that this analytics entry belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Increment the access count for a menu item by a specific user
     *
     * @param int $menuItemId
     * @param int $userId
     * @return MenuAnalytics
     */
    public static function incrementAccessCount($menuItemId, $userId)
    {
        $analytics = self::firstOrNew([
            'menu_item_id' => $menuItemId,
            'user_id' => $userId,
        ]);

        if (!$analytics->exists) {
            $analytics->access_count = 1;
        } else {
            $analytics->access_count++;
        }

        $analytics->last_accessed_at = now();
        $analytics->save();

        return $analytics;
    }

    /**
     * Get the most popular menu items across all users
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getMostPopularMenuItems($limit = 10)
    {
        return self::selectRaw('menu_item_id, SUM(access_count) as total_count')
            ->groupBy('menu_item_id')
            ->orderByDesc('total_count')
            ->limit($limit)
            ->with('menuItem')
            ->get();
    }
}
