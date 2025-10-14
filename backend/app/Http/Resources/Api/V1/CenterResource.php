<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class CenterResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'city' => $this->city,
            'address' => $this->address,
            'average_rating' => $this->average_rating,
        ];
    }
}
