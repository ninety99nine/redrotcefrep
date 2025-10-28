<?php

namespace App\Http\Requests\TeamMember;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class AddTeamMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
        return $this->user()->can('add', User::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'role_id' => ['required', 'string'],
            'store_id' => ['required', 'uuid'],
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
            'email.required' => 'The email is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.max' => 'The email must not exceed 255 characters.',
            'role_id.required' => 'The role is required.',
            'role_id.string' => 'The role must be a string.',
            'store_id.required' => 'The store ID is required.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'first_name.string' => 'The first name must be a string.',
            'first_name.max' => 'The first name must not exceed 255 characters.',
            'last_name.string' => 'The last name must be a string.',
            'last_name.max' => 'The last name must not exceed 255 characters.'
        ];
    }
}
