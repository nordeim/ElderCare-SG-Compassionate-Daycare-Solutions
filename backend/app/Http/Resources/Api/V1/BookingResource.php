<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'booking_number' => $this->booking_number,
            'user_id' => $this->user_id,
            'center' => new CenterResource($this->whenLoaded('center')),
            'service' => $this->whenLoaded('service'),
            'booking_date' => $this->booking_date?->toDateString(),
            'booking_time' => $this->booking_time?->toTimeString(),
            'status' => $this->status,
            'calendly_event_uri' => $this->calendly_event_uri,
        ];
    }
}
