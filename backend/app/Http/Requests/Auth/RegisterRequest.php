<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+65[689]\d{7}$/'],
            'password' => ['required', 'confirmed', Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised()],
            'preferred_language' => ['nullable', 'string', 'in:en,zh,ms,ta'],
            'consent_account' => ['required', 'accepted'],
            'consent_marketing_email' => ['nullable', 'boolean'],
            'consent_marketing_sms' => ['nullable', 'boolean'],
            'consent_analytics_cookies' => ['nullable', 'boolean'],
            'consent_functional_cookies' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please provide your full name',
            'email.required' => 'Email address is required',
            'email.email' => 'Please provide a valid email address',
            'email.unique' => 'This email address is already registered',
            'phone.regex' => 'Please provide a valid Singapore phone number (e.g., +6591234567)',
            'password.required' => 'Password is required',
            'password.confirmed' => 'Password confirmation does not match',
            'consent_account.accepted' => 'You must accept the terms of service to create an account',
        ];
    }

    public function attributes(): array
    {
        return [
            'consent_account' => 'terms of service agreement',
            'consent_marketing_email' => 'email marketing consent',
            'consent_marketing_sms' => 'SMS marketing consent',
        ];
    }
}
