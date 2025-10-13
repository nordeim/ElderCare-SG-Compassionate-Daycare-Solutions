<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TestimonialResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'rating' => $this->rating,
            'status' => $this->status,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name ?? null,
                ];
            }),
            'center' => $this->whenLoaded('center', function () {
                return [
                    'id' => $this->center->id,
                    'name' => $this->center->name ?? null,
                ];
            }),
            'created_at' => $this->created_at,
        ];
    }
}
