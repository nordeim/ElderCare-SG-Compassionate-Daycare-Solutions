<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\StoreServiceRequest;
use App\Http\Requests\Service\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Services\Center\ServiceManagementService;

class ServiceController extends Controller
{
    protected ServiceManagementService $service;

    public function __construct(ServiceManagementService $service)
    {
        $this->service = $service;
    }

    public function store(StoreServiceRequest $request, int $centerId)
    {
        $service = $this->service->create($centerId, $request->validated());
        return new ServiceResource($service);
    }

    public function update(UpdateServiceRequest $request, int $centerId, int $id)
    {
        $service = $this->service->update($centerId, $id, $request->validated());
        return new ServiceResource($service);
    }

    public function destroy(int $centerId, int $id)
    {
        $this->service->delete($centerId, $id);
        return response()->json(['message' => 'Service deleted']);
    }
}
