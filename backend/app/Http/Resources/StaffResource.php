<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
{
    public function toArray($request)
    {
        $staff = $this->resource;
        return [
            'id' => $staff->id,
            'name' => $staff->name,
            'role' => $staff->role,
            'qualifications' => $staff->qualifications,
            'status' => $staff->status,
        ];
    }
}
