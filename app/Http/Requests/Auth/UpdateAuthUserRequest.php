<?php

namespace App\Http\Requests\Auth;

use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAuthUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Protected by auth:sanctum middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'string', 'max:20'],
            'last_name' => ['sometimes', 'string', 'max:20'],
            'email' => [
                'sometimes','nullable','email',
                Rule::unique('users', 'email')->ignore($this->user()->id),
            ],
            'mobile_number' => [
                'sometimes','nullable','phone:INTERNATIONAL',
                Rule::unique('users', 'mobile_number')->ignore($this->user()->id),
            ],
            'password' => ['sometimes', 'string', Password::min(6)],
        ];
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'first_name.string' => 'The first name must be a string.',
            'first_name.max' => 'The first name must not be greater than 20 characters.',
            'last_name.string' => 'The last name must be a string.',
            'last_name.max' => 'The last name must not be greater than 20 characters.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'mobile_number.phone' => 'Please provide a valid mobile number (e.g., +26772000001).',
            'mobile_number.unique' => 'This mobile number is already registered.',
            'password.min' => 'The password must be at least 6 characters long.',
        ];
    }
}
