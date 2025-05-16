<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'subject',
        'body',
        'plain_text',
        'placeholders',
        'is_active',
        'sender_name',
        'sender_email',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'placeholders' => 'array',
    ];

    /**
     * Scope a query to only include active email templates.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Parse the email template with provided data.
     *
     * @param array $data
     * @return array
     */
    public function parse(array $data)
    {
        $subject = $this->subject;
        $body = $this->body;
        $plainText = $this->plain_text;

        foreach ($data as $key => $value) {
            $placeholder = '{{' . $key . '}}';

            $subject = str_replace($placeholder, $value, $subject);
            $body = str_replace($placeholder, $value, $body);

            if ($plainText) {
                $plainText = str_replace($placeholder, $value, $plainText);
            }
        }

        return [
            'subject' => $subject,
            'body' => $body,
            'plain_text' => $plainText,
        ];
    }
}
