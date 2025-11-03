<?php

namespace App\Http\Requests\Role;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRolesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('updateAny', Role::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'store_id' => ['required', 'uuid'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*.id' => ['required', 'uuid'],
            'roles.*.name' => ['sometimes', 'string', 'max:255'],
            'roles.*.permissions' => ['sometimes', 'array'],
            'roles.*.permissions.*' => ['uuid'],
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
            'store_id.required' => 'The store ID is required.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'roles.required' => 'At least one role is required.',
            'roles.array' => 'The roles must be an array.',
            'roles.min' => 'At least one role must be provided.',
            'roles.*.id.required' => 'Each role must have an ID.',
            'roles.*.id.uuid' => 'Each role ID must be a valid UUID.',
            'roles.*.name.string' => 'The name must be a string.',
            'roles.*.name.max' => 'The name must not exceed 255 characters.',
            'roles.*.permissions.array' => 'The permissions must be an array.',
            'roles.*.permissions.*.uuid' => 'Each permission ID must be a valid UUID.',
        ];
    }
}
