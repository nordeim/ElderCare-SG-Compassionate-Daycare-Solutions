<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AuditObserver
{
    public function created(Model $model): void
    {
        $this->logAudit($model, 'created', null, $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $this->logAudit($model, 'updated', $model->getOriginal(), $model->getChanges());
    }

    public function deleted(Model $model): void
    {
        $this->logAudit($model, 'deleted', $model->getAttributes(), null);
    }

    public function restored(Model $model): void
    {
        $this->logAudit($model, 'restored', null, $model->getAttributes());
    }

    protected function logAudit(Model $model, string $action, ?array $oldValues, ?array $newValues): void
    {
        // Skip logging for the AuditLog model itself
        if ($model instanceof AuditLog) {
            return;
        }

        try {
            $request = request();
            $user = auth()->user();

            AuditLog::create([
                'user_id' => $user?->id,
                'auditable_type' => get_class($model),
                'auditable_id' => $model->getKey(),
                'action' => $action,
                'old_values' => $oldValues ? $this->sanitize($oldValues) : null,
                'new_values' => $newValues ? $this->sanitize($newValues) : null,
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
                'url' => $request?->fullUrl(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Audit log failed: ' . $e->getMessage());
        }
    }

    protected function sanitize(array $values): array
    {
        $sensitive = ['password', 'remember_token', 'api_token', 'auth_token'];

        foreach ($sensitive as $key) {
            if (array_key_exists($key, $values)) {
                $values[$key] = '[REDACTED]';
            }
        }

        return $values;
    }
}
        $sensitive = ['password', 'remember_token', 'api_token', 'auth_token'];
