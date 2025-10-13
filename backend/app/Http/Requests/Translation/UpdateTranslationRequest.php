<?php

namespace App\Http\Requests\Translation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role ?? null, ['admin', 'super_admin']);
    }

    public function rules(): array
    {
        return [
            'value' => ['required', 'string'],
        ];
    }
}
