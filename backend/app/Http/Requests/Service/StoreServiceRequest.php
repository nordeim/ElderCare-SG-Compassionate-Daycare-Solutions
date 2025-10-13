<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'price_unit' => 'nullable|string',
            'duration' => 'nullable|string',
            'features' => 'nullable|array',
            'status' => 'nullable|in:draft,published,archived',
        ];
    }
}
