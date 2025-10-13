<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Center\StoreCenterRequest;
use App\Http\Requests\Center\UpdateCenterRequest;
use App\Http\Resources\CenterResource;
use App\Models\Center;
use App\Services\Center\CenterService;

class CenterController extends Controller
{
    protected CenterService $service;

    public function __construct(CenterService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $centers = $this->service->list(request()->all());
        return CenterResource::collection($centers);
    }

    public function show($slug)
    {
        $center = $this->service->getBySlug($slug);
        return new CenterResource($center);
    }

    public function store(StoreCenterRequest $request)
    {
        $center = $this->service->create($request->validated());
        return new CenterResource($center);
    }

    public function update(UpdateCenterRequest $request, int $id)
    {
        $center = $this->service->update($id, $request->validated());
        return new CenterResource($center);
    }

    public function destroy(int $id)
    {
        $this->service->delete($id);
        return response()->json(['message' => 'Center deleted']);
    }
}
