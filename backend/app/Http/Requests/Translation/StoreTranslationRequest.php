<?php

namespace App\Http\Requests\Translation;

use Illuminate\Foundation\Http\FormRequest;

class StoreTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role ?? null, ['admin', 'super_admin']);
    }

    public function rules(): array
    {
        return [
            'translatable_type' => ['required', 'string'],
            'translatable_id' => ['required', 'integer'],
            'locale' => ['required', 'string', 'in:en,zh,ms,ta'],
            'field' => ['required', 'string'],
            'value' => ['required', 'string'],
        ];
    }
}
