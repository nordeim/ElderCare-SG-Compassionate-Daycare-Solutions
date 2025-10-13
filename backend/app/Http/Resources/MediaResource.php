<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'thumbnail_url' => $this->thumbnail_url,
            'filename' => $this->filename,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'caption' => $this->caption,
            'alt_text' => $this->alt_text,
            'display_order' => $this->display_order,
            'created_at' => $this->created_at,
        ];
    }
}
