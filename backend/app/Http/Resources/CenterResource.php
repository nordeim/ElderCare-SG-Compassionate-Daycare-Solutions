<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CenterResource extends JsonResource
{
    public function toArray($request)
    {
        $center = $this->resource;

        return [
            'id' => $center->id,
            'name' => $center->name,
            'slug' => $center->slug,
            'city' => $center->city,
            'address' => $center->address,
            'moh_license_number' => $center->moh_license_number,
            'license_expiry_date' => $center->license_expiry_date,
            'capacity' => $center->capacity,
            'current_occupancy' => $center->current_occupancy,
            'description' => $center->description,
            'status' => $center->status,
            'media' => $center->whenLoaded('media') ? $center->media->map(function ($m) {
                return [
                    'url' => $m->url,
                    'type' => $m->type,
                ];
            }) : null,
            'services_count' => $center->services_count ?? null,
            'staff_count' => $center->staff_count ?? null,
            'bookings_count' => $center->bookings_count ?? null,
            'testimonials_count' => $center->testimonials_count ?? null,
        ];
    }
}
