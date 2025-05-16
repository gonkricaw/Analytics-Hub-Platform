<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Arr;

trait Auditable
{
    /**
     * Boot the trait.
     */
    public static function bootAuditable()
    {
        static::created(function ($model) {
            AuditLog::logCreated($model);
        });

        static::updated(function ($model) {
            // Get the original values for all changed attributes
            $oldValues = [];
            foreach ($model->getDirty() as $key => $value) {
                $oldValues[$key] = $model->getOriginal($key);
            }

            if (!empty($oldValues)) {
                AuditLog::logUpdated($model, $oldValues);
            }
        });

        static::deleted(function ($model) {
            AuditLog::logDeleted($model);
        });
    }

    /**
     * Log a custom action for this model.
     *
     * @param string $action
     * @param array|null $oldValues
     * @param array|null $newValues
     * @return \App\Models\AuditLog
     */
    public function logActivity(string $action, ?array $oldValues = null, ?array $newValues = null)
    {
        return AuditLog::log($action, $this, $oldValues, $newValues);
    }
}
