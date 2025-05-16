<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'ip_address',
        'attempts',
        'is_blocked',
        'blocked_until',
        'last_attempt_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_blocked' => 'boolean',
        'blocked_until' => 'datetime',
        'last_attempt_at' => 'datetime'
    ];

    /**
     * Check if the IP address is blocked.
     *
     * @return bool
     */
    public function isBlocked()
    {
        if (!$this->is_blocked) {
            return false;
        }

        // If blocked_until is set and it's in the past, unblock
        if ($this->blocked_until && $this->blocked_until->isPast()) {
            $this->update([
                'is_blocked' => false,
                'blocked_until' => null,
                'attempts' => 0
            ]);
            return false;
        }

        return true;
    }

    /**
     * Increment the number of login attempts.
     * Block the IP after 15 attempts.
     *
     * @param int $maxAttempts
     * @param int $blockMinutes
     * @return $this
     */
    public function incrementAttempts($maxAttempts = 15, $blockMinutes = 60)
    {
        $this->attempts += 1;
        $this->last_attempt_at = now();

        if ($this->attempts >= $maxAttempts) {
            $this->is_blocked = true;
            $this->blocked_until = now()->addMinutes($blockMinutes);
        }

        $this->save();

        return $this;
    }

    /**
     * Reset attempts to zero.
     *
     * @return $this
     */
    public function resetAttempts()
    {
        $this->update([
            'attempts' => 0,
            'is_blocked' => false,
            'blocked_until' => null
        ]);

        return $this;
    }

    /**
     * Unblock the IP address.
     *
     * @return $this
     */
    public function unblock()
    {
        $this->update([
            'is_blocked' => false,
            'blocked_until' => null,
            'attempts' => 0
        ]);

        return $this;
    }
}
