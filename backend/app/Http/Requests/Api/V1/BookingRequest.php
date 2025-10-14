<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'center_id' => 'required|exists:centers,id',
            'service_id' => 'nullable|exists:services,id',
            'booking_date' => 'required|date_format:Y-m-d',
            'booking_time' => 'required|date_format:H:i:s',
            'booking_type' => 'nullable|string',
            'questionnaire_responses' => 'nullable|array',
        ];
    }
}
