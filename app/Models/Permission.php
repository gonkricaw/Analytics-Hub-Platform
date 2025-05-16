<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'module'
    ];

    /**
     * The roles that belong to the permission.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Scope a query to permissions for a specific module.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $module
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForModule($query, $module)
    {
        return $query->where('module', $module);
    }
}
