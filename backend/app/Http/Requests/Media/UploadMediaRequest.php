<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class UploadMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role ?? null, ['admin', 'super_admin']);
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:51200'], // max 50MB
            'mediable_type' => ['required', 'string'],
            'mediable_id' => ['required', 'integer'],
            'type' => ['required', 'in:image,video,document'],
            'caption' => ['sometimes', 'string', 'max:500'],
            'alt_text' => ['sometimes', 'string', 'max:255'],
            'display_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
