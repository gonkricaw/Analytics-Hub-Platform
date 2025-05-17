<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    /**
     * The users that belong to the role.
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * The permissions that belong to the role.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Determine if the role has a specific permission.
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission(string $permission)
    {
        return $this->permissions()->where('name', $permission)->exists();
    }

    /**
     * Give permissions to the role.
     *
     * @param array|Permission $permissions
     * @return $this
     */
    public function givePermissions($permissions)
    {
        $this->permissions()->syncWithoutDetaching(
            $this->extractPermissionIds($permissions)
        );

        return $this;
    }

    /**
     * Revoke permissions from the role.
     *
     * @param array|Permission $permissions
     * @return $this
     */
    public function revokePermissions($permissions)
    {
        $this->permissions()->detach(
            $this->extractPermissionIds($permissions)
        );

        return $this;
    }

    /**
     * Sync permissions for the role.
     *
     * @param array|Permission $permissions
     * @return $this
     */
    public function syncPermissions($permissions)
    {
        $this->permissions()->sync(
            $this->extractPermissionIds($permissions)
        );

        return $this;
    }

    /**
     * Extract permission IDs from array or single Permission model.
     *
     * @param array|Permission $permissions
     * @return array
     */
    protected function extractPermissionIds($permissions)
    {
        if ($permissions instanceof Permission) {
            return [$permissions->id];
        }

        if (is_numeric($permissions)) {
            return [$permissions];
        }

        if (is_string($permissions)) {
            return [Permission::where('name', $permissions)->firstOrFail()->id];
        }

        if (is_array($permissions)) {
            return array_map(function($permission) {
                if ($permission instanceof Permission) {
                    return $permission->id;
                }

                if (is_numeric($permission)) {
                    return $permission;
                }

                if (is_string($permission)) {
                    return Permission::where('name', $permission)->firstOrFail()->id;
                }
            }, $permissions);
        }

        return $permissions;
    }
}
