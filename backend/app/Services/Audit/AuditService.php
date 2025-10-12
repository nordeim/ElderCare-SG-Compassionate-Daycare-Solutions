<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    /**
     * Log an action in the audit trail.
     *
     * @param Model $model
     * @param string $action
     * @param array|null $oldValues
     * @param array|null $newValues
     * @return AuditLog
     */
    public function log(Model $model, string $action, ?array $oldValues = null, ?array $newValues = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'auditable_type' => $model->getMorphClass(),
            'auditable_id' => $model->getKey(),
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
        ]);
    }

    /**
     * Get the audit trail for a specific model.
     *
     * @param Model $model
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAuditTrail(Model $model)
    {
        return AuditLog::forModel($model)->latest()->get();
    }

    /**
     * Search the audit logs.
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function searchAuditLogs(array $filters = [])
    {
        $query = AuditLog::with('user');

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (isset($filters['auditable_type'])) {
            $query->where('auditable_type', $filters['auditable_type']);
        }
        
        if (isset($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }

        return $query->latest()->paginate(20);
    }
}
