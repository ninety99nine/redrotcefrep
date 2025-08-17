<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'first_name' => ['nullable', 'string', 'max:20'],
            'last_name' => ['nullable', 'string', 'max:20'],
            'email' => ['required_without:mobile_number', 'nullable', 'email', 'unique:users,email'],
            'mobile_number' => ['required_without:email', 'nullable', 'phone:INTERNATIONAL', 'unique:users,mobile_number'],
            'google_id' => ['nullable', 'string', 'max:255', 'unique:users,google_id'],
            'facebook_id' => ['nullable', 'string', 'max:255', 'unique:users,facebook_id'],
            'linkedin_id' => ['nullable', 'string', 'max:255', 'unique:users,linkedin_id'],
            'password' => ['required', 'string', Password::min(6)],
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
            'first_name.required' => 'The first name is required.',
            'first_name.string' => 'The first name must be a string.',
            'first_name.max' => 'The first name must not exceed 20 characters.',
            'last_name.string' => 'The last name must be a string.',
            'last_name.max' => 'The last name must not exceed 20 characters.',
            'email.required_without' => 'The email is required when mobile number is not provided.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'mobile_number.required_without' => 'The mobile number is required when email is not provided.',
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
            'password.required' => 'The password is required.',
            'password.string' => 'The password must be a string.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }
}
