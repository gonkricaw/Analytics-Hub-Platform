<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'slug',
        'url',
        'route_name',
        'icon',
        'order',
        'parent_id',
        'permissions',
        'is_active',
        'is_external',
        'target'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_external' => 'boolean',
        'permissions' => 'array',
    ];

    /**
     * Get the parent menu item.
     */
    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Get the child menu items.
     */
    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')
                    ->orderBy('order');
    }

    /**
     * Recursive function to get all children and nested children.
     */
    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    /**
     * Scope a query to only include active menu items.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include top-level menu items.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get the analytics data associated with this menu item.
     */
    public function analytics()
    {
        return $this->hasMany(MenuAnalytics::class, 'menu_item_id');
    }

    /**
     * Check if user has permission to see this menu item.
     *
     * @param User $user
     * @return bool
     */
    public function canBeSeenBy(User $user)
    {
        // If no permissions are required, anyone can see it
        if (empty($this->permissions)) {
            return true;
        }

        // Check if user has any of the required permissions
        foreach ($this->permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }
}
