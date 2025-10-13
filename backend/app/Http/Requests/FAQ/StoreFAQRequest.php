<?php

namespace App\Http\Requests\FAQ;

use Illuminate\Foundation\Http\FormRequest;

class StoreFAQRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'category' => 'required|string|max:100',
            'question' => 'required|string',
            'answer' => 'required|string',
            'status' => 'nullable|in:draft,published',
        ];
    }
}
