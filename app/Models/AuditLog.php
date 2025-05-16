<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Get the user who performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log a create action
     *
     * @param Model $model
     * @return self
     */
    public static function logCreated(Model $model)
    {
        return self::log('created', $model, null, $model->getAttributes());
    }

    /**
     * Log an update action
     *
     * @param Model $model
     * @param array $oldValues
     * @return self
     */
    public static function logUpdated(Model $model, array $oldValues)
    {
        return self::log('updated', $model, $oldValues, $model->getAttributes());
    }

    /**
     * Log a delete action
     *
     * @param Model $model
     * @return self
     */
    public static function logDeleted(Model $model)
    {
        return self::log('deleted', $model, $model->getAttributes(), null);
    }

    /**
     * Log a custom action
     *
     * @param string $action
     * @param Model|null $model
     * @param array|null $oldValues
     * @param array|null $newValues
     * @return self
     */
    public static function log(string $action, ?Model $model = null, ?array $oldValues = null, ?array $newValues = null)
    {
        $request = request();

        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->getKey() : null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request ? $request->ip() : null,
            'user_agent' => $request ? $request->userAgent() : null,
        ]);
    }
}
