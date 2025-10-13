<?php

namespace App\Http\Requests\Center;

use Illuminate\Foundation\Http\FormRequest;

class StoreCenterRequest extends FormRequest
{
    public function authorize()
    {
        // Authorization should be handled via policies or middleware
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'moh_license_number' => 'nullable|string|max:100',
            'license_expiry_date' => 'nullable|date',
            'capacity' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'status' => 'nullable|in:draft,published,archived',
        ];
    }
}
