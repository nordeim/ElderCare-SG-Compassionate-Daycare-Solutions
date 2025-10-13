<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'price' => 'sometimes|nullable|numeric|min:0',
            'price_unit' => 'sometimes|nullable|string',
            'duration' => 'sometimes|nullable|string',
            'features' => 'sometimes|nullable|array',
            'status' => 'sometimes|nullable|in:draft,published,archived',
        ];
    }
}
