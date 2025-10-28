<?php

namespace App\Http\Requests\TeamMember;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTeamMemberRequest extends FormRequest
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
        return [
            'email' => ['sometimes', 'email', 'max:255'],
            'role_id' => ['required', 'string'],
            'store_id' => ['required', 'uuid']
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
            'email.email' => 'The email must be a valid email address.',
            'email.max' => 'The email must not exceed 255 characters.',
            'role_id.required' => 'The role is required.',
            'role_id.string' => 'The role must be a string.',
            'store_id.required' => 'The store ID is required.',
            'store_id.uuid' => 'The store ID must be a valid UUID.'
        ];
    }
}
