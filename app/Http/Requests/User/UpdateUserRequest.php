<?php

namespace App\Http\Requests\User;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'first_name' => ['sometimes', 'string', 'max:20'],
            'last_name' => ['nullable', 'string', 'max:20'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'mobile_number' => ['sometimes', 'string', 'max:20', 'phone:INTERNATIONAL', Rule::unique('users', 'mobile_number')->ignore($userId)],
            'google_id' => ['sometimes', 'string', 'max:255', Rule::unique('users', 'google_id')->ignore($userId)],
            'facebook_id' => ['sometimes', 'string', 'max:255', Rule::unique('users', 'facebook_id')->ignore($userId)],
            'linkedin_id' => ['sometimes', 'string', 'max:255', Rule::unique('users', 'linkedin_id')->ignore($userId)],
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
            'first_name.max' => 'The first name must not exceed 20 characters.',
            'last_name.string' => 'The last name must be a string.',
            'last_name.max' => 'The last name must not exceed 20 characters.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'mobile_number.phone' => 'Please provide a valid mobile number (e.g., +26772000001).',
            'mobile_number.unique' => 'This mobile number is already registered.',
            'google_id.string' => 'The Google ID must be a string.',
            'google_id.max' => 'The Google ID must not exceed 255 characters.',
            'google_id.unique' => 'The Google ID is already in use.',
            'facebook_id.string' => 'The Facebook ID must be a string.',
            'facebook_id.max' => 'The Facebook ID must not exceed 255 characters.',
            'facebook_id.unique' => 'The Facebook ID is already in use.',
            'linkedin_id.string' => 'The LinkedIn ID must be a string.',
            'linkedin_id.max' => 'The LinkedIn ID must not exceed 255 characters.',
            'linkedin_id.unique' => 'The LinkedIn ID is already in use.',
            'password.string' => 'The password must be a string.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }
}
