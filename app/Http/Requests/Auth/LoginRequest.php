<?php

namespace App\Http\Requests\Auth;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['email', 'mobile_number'])],
            'email' => [
                Rule::requiredIf($this->type === 'email'),
                Rule::excludeIf($this->type !== 'email'),
                'email'
            ],
            'mobile_number' => [
                Rule::requiredIf($this->type === 'mobile_number'),
                Rule::excludeIf($this->type !== 'mobile_number'),
                'phone:INTERNATIONAL'
            ],
            'password' => ['required','string',Password::min(6)]
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'The type field is required',
            'type.in' => 'The type must be either email or mobile_number.',
            'email.required' => 'The email field is required.',
            'email.email' => 'Please provide a valid email address.',
            'mobile_number.required' => 'The mobile number field is required.',
            'mobile_number.phone' => 'Please provide a valid mobile number (e.g., +26772000001).',
            'password.required' => 'The password field is required.',
            'password.min' => 'The password must be at least 6 characters long.'
        ];
    }
}
