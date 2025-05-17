<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemConfiguration extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'is_public',
        'display_name',
        'description'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Get configuration value with casting based on type
     *
     * @return mixed
     */
    public function getTypedValueAttribute()
    {
        switch ($this->type) {
            case 'boolean':
                return (bool) $this->value;
            case 'integer':
                return (int) $this->value;
            case 'float':
                return (float) $this->value;
            case 'json':
                return json_decode($this->value, true);
            case 'array':
                return explode(',', $this->value);
            case 'string':
            default:
                return $this->value;
        }
    }

    /**
     * Get a configuration value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getConfig(string $key, $default = null)
    {
        $config = self::where('key', $key)->first();

        if (!$config) {
            return $default;
        }

        return $config->typed_value;
    }

    /**
     * Set a configuration value
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @return SystemConfiguration
     */
    public static function setConfig(string $key, $value, string $type = 'string', string $group = 'general')
    {
        // Convert arrays/objects to JSON strings
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
            $type = 'json';
        }

        // Convert booleans to string representation
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
            $type = 'boolean';
        }

        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => (string) $value,
                'type' => $type,
                'group' => $group
            ]
        );
    }
}
