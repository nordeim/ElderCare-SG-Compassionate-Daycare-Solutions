<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'center_id' => ['required', 'exists:centers,id'],
            'service_id' => ['nullable', 'exists:services,id'],
            'booking_date' => ['required', 'date', 'after:today'],
            'booking_time' => ['required', 'date_format:H:i'],
            'booking_type' => ['sometimes', 'in:visit,consultation,trial_day'],
            'questionnaire_responses' => ['nullable', 'array'],
            'questionnaire_responses.elderly_age' => ['sometimes', 'integer', 'min:1', 'max:120'],
            'questionnaire_responses.medical_conditions' => ['sometimes', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'booking_date.after' => 'Booking date must be in the future',
            'booking_time.date_format' => 'Booking time must be in HH:MM format (e.g., 14:30)',
        ];
    }
}
