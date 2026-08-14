<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->writeAudit('created', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            if (empty($changes)) {
                return;
            }

            $model->writeAudit('updated', $model->getOriginal(), $changes);
        });

        static::deleted(function ($model) {
            $model->writeAudit('deleted', $model->getOriginal(), null);
        });
    }

    protected function writeAudit(string $action, ?array $old, ?array $new): void
    {
        try {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'model_type' => static::class,
                'model_id' => $this->getKey(),
                'old_values' => $old,
                'new_values' => $new,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Audit log gagal ditulis: '.$e->getMessage());
        }
    }
}
