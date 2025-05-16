<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermAndCondition extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'terms_and_conditions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'content',
        'is_active',
        'effective_date',
        'version'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'effective_date' => 'datetime',
    ];

    /**
     * The users that have accepted this terms and conditions.
     */
    public function acceptedByUsers()
    {
        return $this->belongsToMany(User::class, 'term_user')
            ->withPivot('accepted_at', 'ip_address', 'user_agent')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include active terms and conditions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if a specific user has accepted this terms and conditions.
     *
     * @param User $user
     * @return bool
     */
    public function isAcceptedBy(User $user)
    {
        return $this->acceptedByUsers()->where('user_id', $user->id)->exists();
    }
}
