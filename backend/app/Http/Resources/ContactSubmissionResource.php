<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ContactSubmissionResource extends JsonResource
{
    public function toArray($request)
    {
        $s = $this->resource;
        return [
            'id' => $s->id,
            'name' => $s->name,
            'email' => $s->email,
            'phone' => $s->phone,
            'subject' => $s->subject,
            'message' => $s->message,
            'status' => $s->status,
        ];
    }
}
