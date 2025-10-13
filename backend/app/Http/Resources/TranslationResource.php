<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TranslationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'translatable_type' => $this->translatable_type,
            'translatable_id' => $this->translatable_id,
            'locale' => $this->locale,
            'field' => $this->field,
            'value' => $this->value,
            'translation_status' => $this->translation_status,
            'translated_by' => $this->translated_by,
            'reviewed_by' => $this->reviewed_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
