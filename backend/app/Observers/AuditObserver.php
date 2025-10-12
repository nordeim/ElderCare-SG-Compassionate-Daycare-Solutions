<?php

namespace App\Observers;

use App\Services\Audit\AuditService;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function created(Model $model)
    {
        $this->auditService->log($model, 'created', null, $model->getAttributes());
    }

    public function updated(Model $model)
    {
        $this->auditService->log($model, 'updated', $model->getOriginal(), $model->getChanges());
    }

    public function deleted(Model $model)
    {
        $this->auditService->log($model, 'deleted', $model->getAttributes());
    }

    public function restored(Model $model)
    {
        $this->auditService->log($model, 'restored');
    }
}
