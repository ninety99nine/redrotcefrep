<?php

namespace App\Http\Requests\Auth;

use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:20'],
            'last_name' => ['required', 'string', 'max:20'],
            'email' => ['required_without:mobile_number', 'nullable', 'email', 'unique:users,email'],
            'mobile_number' => ['required_without:email', 'nullable', 'phone:INTERNATIONAL', 'unique:users,mobile_number'],
            'password' => ['required', 'string', Password::min(6)],
            'confirm_password' => ['required', 'same:password']
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'The first name is required.',
            'first_name.string' => 'The first name must be a string.',
            'first_name.max' => 'The first name must not exceed 20 characters.',
            'last_name.required' => 'The last name is required.',
            'last_name.string' => 'The last name must be a string.',
            'last_name.max' => 'The last name must not exceed 20 characters.',
            'email.required_without' => 'The email is required when mobile number is not provided.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'mobile_number.required_without' => 'The mobile number is required when email is not provided.',
            'mobile_number.phone' => 'Please provide a valid mobile number (e.g., +26772000001).',
            'mobile_number.unique' => 'This mobile number is already registered.',
            'password.required' => 'The password is required.',
            'password.min' => 'The password must be at least 6 characters.',
            'confirm_password.required' => 'The confirm password is required.',
            'confirm_password.same' => 'The passwords do not match.'
        ];
    }
}
