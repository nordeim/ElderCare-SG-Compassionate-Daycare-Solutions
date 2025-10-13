<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray($request)
    {
        $s = $this->resource;
        return [
            'id' => $s->id,
            'email' => $s->email,
            'mailchimp_status' => $s->mailchimp_status,
        ];
    }
}
