<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermsAndConditionAgreement extends Model
{
    use HasFactory;

    /**
     * The table name for the model.
     *
     * @var string
     */
    protected $table = 'term_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'term_and_condition_id',
        'accepted_at',
        'ip_address',
        'user_agent'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'accepted_at' => 'datetime'
    ];

    /**
     * Get the user that accepted the terms and conditions.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the terms and conditions that were accepted.
     */
    public function termsAndCondition()
    {
        return $this->belongsTo(TermAndCondition::class, 'term_and_condition_id');
    }
}
