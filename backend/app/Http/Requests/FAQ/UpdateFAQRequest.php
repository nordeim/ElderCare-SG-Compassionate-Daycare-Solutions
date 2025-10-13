<?php

namespace App\Http\Requests\FAQ;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFAQRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'category' => 'sometimes|required|string|max:100',
            'question' => 'sometimes|required|string',
            'answer' => 'sometimes|required|string',
            'status' => 'sometimes|nullable|in:draft,published',
        ];
    }
}
