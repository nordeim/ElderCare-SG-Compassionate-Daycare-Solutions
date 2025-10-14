<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditObserver
{
    public function created(Model $model): void
    {
        $this->write('created', $model, null, $this->snapshot($model));
    }

    public function updated(Model $model): void
    {
        $old = array_intersect_key($model->getOriginal(), $model->getChanges());
        $new = array_intersect_key($model->getAttributes(), $model->getChanges());

        $this->write('updated', $model, $this->filterForAudit($model, $old), $this->filterForAudit($model, $new));
    }

    public function deleted(Model $model): void
    {
        $this->write('deleted', $model, $this->snapshot($model), null);
    }

    public function restored(Model $model): void
    {
        $this->write('restored', $model, null, $this->snapshot($model));
    }

    protected function snapshot(Model $model): array
    {
        if (method_exists($model, 'toAudit')) {
            return $model->toAudit();
        }

        return $model->getAttributes();
    }

    protected function filterForAudit(Model $model, array $attributes): array
    {
        if (method_exists($model, 'scrubAuditAttributes')) {
            return $model->scrubAuditAttributes($attributes);
        }

        if (method_exists($model, 'toAudit')) {
            return $model->toAudit(array_keys($attributes));
        }

        return $attributes;
    }

    protected function write(string $action, Model $model, ?array $old = null, ?array $new = null): void
    {
        // Avoid recursion if model is AuditLog
        if ($model instanceof \App\Models\AuditLog) {
            return;
        }

        try {
            // DEBUG: trace attempts to write audit entries (test-time troubleshooting)
            try {
                @file_put_contents(storage_path('logs/audit_debug.log'), date('c') . " | attempt: {$action} {$model->getMorphClass()}#{$model->getKey()}\n", FILE_APPEND);
            } catch (\Throwable $e) {
                // ignore debug write failures
            }

            $userId = Auth::check() ? Auth::id() : null;

            $request = null;
            try {
                $request = request();
            } catch (\Throwable $e) {
                // no request context
            }

            \DB::table('audit_logs')->insert([
                'user_id' => $userId,
                'auditable_type' => $model->getMorphClass(),
                'auditable_id' => $model->getKey(),
                'action' => $action,
                'old_values' => is_null($old) ? null : json_encode($old),
                'new_values' => is_null($new) ? null : json_encode($new),
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
                'url' => $request?->fullUrl(),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('AuditObserver failed to record audit log: ' . $e->getMessage(), ['model' => get_class($model), 'id' => $model->getKey()]);
        }
    }
}
