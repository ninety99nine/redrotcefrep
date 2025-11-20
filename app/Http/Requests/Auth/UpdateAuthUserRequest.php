<?php

namespace App\Http\Requests\Auth;

use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UpdateAuthUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Protected by auth:sanctum
    }

    public function rules(): array
    {
        $user = $this->user();
        $hasPassword = !is_null($user->password);

        return [
            'first_name'    => ['sometimes', 'string', 'min:3', 'max:20'],
            'last_name'     => ['sometimes', 'string', 'min:3', 'max:20'],
            'email' => [
                'sometimes', 'nullable', 'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'mobile_number' => [
                'sometimes', 'nullable', 'phone:INTERNATIONAL',
                Rule::unique('users', 'mobile_number')->ignore($user->id),
            ],
            'password' => [
                'sometimes', 'nullable', 'confirmed', Password::min(6)
            ],
            // Current password is REQUIRED only if:
            // 1. User already has a password
            // 2. AND they are trying to set a new one
            'current_password' => [
                'required_if:password,!=,null','string',
                function ($attribute, $value, $fail) use ($user, $hasPassword) {
                    // If user has no password → skip current_password check
                    if (!$hasPassword) {
                        return;
                    }

                    if (!Hash::check($value, $user->password)) {
                        $fail('The current password is incorrect.');
                    }
                },
            ],

            // Social IDs — allow nulling for disconnect
            'google_id'    => ['nullable'],
            'facebook_id'  => ['nullable'],
            'linkedin_id'  => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.max'           => 'First name must not exceed 20 characters.',
            'last_name.max'            => 'Last name must not exceed 20 characters.',
            'email.email'              => 'Please provide a valid email address.',
            'email.unique'             => 'This email is already in use.',
            'mobile_number.phone'      => 'Please provide a valid mobile number (e.g., +26772000001).',
            'mobile_number.unique'     => 'This mobile number is already registered.',
            'password.confirmed'       => 'The password confirmation does not match.',
            'password.min'             => 'Password must be at least 8 characters.',
            'current_password.required_if' => 'Current password is required to set a new password.',
        ];
    }
}
