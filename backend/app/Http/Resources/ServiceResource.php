<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray($request)
    {
        $service = $this->resource;
        return [
            'id' => $service->id,
            'name' => $service->name,
            'slug' => $service->slug,
            'description' => $service->description,
            'price' => $service->price,
            'price_unit' => $service->price_unit,
            'duration' => $service->duration,
            'features' => $service->features,
            'status' => $service->status,
        ];
    }
}
