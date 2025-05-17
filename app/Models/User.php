<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'phone',
        'position',
        'department',
        'last_login_at',
        'last_login_ip',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The roles that belong to the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Check if the user has a specific role.
     *
     * @param string|array $roles
     * @return bool
     */
    public function hasRole($roles)
    {
        if (is_string($roles)) {
            return $this->roles->contains('name', $roles);
        }

        return $this->roles->pluck('name')->intersect($roles)->isNotEmpty();
    }

    /**
     * Check if the user has any of the given roles.
     *
     * @param array $roles
     * @return bool
     */
    public function hasAnyRole(array $roles)
    {
        return $this->roles->pluck('name')->intersect($roles)->isNotEmpty();
    }

    /**
     * Check if the user has all of the given roles.
     *
     * @param array $roles
     * @return bool
     */
    public function hasAllRoles(array $roles)
    {
        return $this->roles->pluck('name')->intersect($roles)->count() === count($roles);
    }

    /**
     * Get all permissions for the user through roles.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllPermissions()
    {
        return $this->roles->map->permissions->flatten()->unique('id');
    }

    /**
     * Check if the user has a specific permission.
     *
     * @param string|array $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        $permissions = $this->getAllPermissions()->pluck('name');

        if (is_string($permission)) {
            return $permissions->contains($permission);
        }

        return $permissions->intersect($permission)->isNotEmpty();
    }

    /**
     * The terms and conditions that the user has accepted.
     */
    public function acceptedTerms()
    {
        return $this->belongsToMany(TermAndCondition::class, 'term_user')
            ->withPivot('accepted_at', 'ip_address', 'user_agent')
            ->withTimestamps();
    }

    /**
     * The notifications directly assigned to the user.
     */
    public function notifications()
    {
        return $this->hasMany(SystemNotification::class);
    }

    /**
     * The user notifications junction model.
     */
    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class);
    }

    /**
     * Get all notifications for the user including global ones.
     */
    public function allNotifications()
    {
        return SystemNotification::forUser($this)
                                 ->orderBy('created_at', 'desc');
    }
}
