<?php

namespace App\Http\Requests\Center;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCenterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|nullable|string',
            'city' => 'sometimes|nullable|string|max:100',
            'moh_license_number' => 'sometimes|nullable|string|max:100',
            'license_expiry_date' => 'sometimes|nullable|date',
            'capacity' => 'sometimes|nullable|integer|min:1',
            'description' => 'sometimes|nullable|string',
            'status' => 'sometimes|nullable|in:draft,published,archived',
        ];
    }
}
