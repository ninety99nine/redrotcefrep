<?php

namespace App\Http\Requests\Auth;

use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

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
            'last_name' => 'nullable|string|max:20',
            'first_name' => 'sometimes|string|max:20',
            'password' => ['sometimes','string',Password::min(6)]
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
            'last_name.string' => 'The last name must be a string.',
            'last_name.max' => 'The last name must not be greater than 20 characters.',
            'first_name.string' => 'The first name must be a string.',
            'first_name.max' => 'The first name must not be greater than 20 characters.',
            'password.min' => 'The password must be at least 6 characters long.',
        ];
    }
}
