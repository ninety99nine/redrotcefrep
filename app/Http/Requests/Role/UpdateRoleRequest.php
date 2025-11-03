<?php

namespace App\Http\Requests\Role;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('role'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $roleId = $this->route('role')->id;

        return [
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($roleId)->where('store_id', $this->input('store_id'))],
            'store_id' => ['sometimes', 'uuid'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['uuid'],
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
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name must not exceed 255 characters.',
            'name.unique' => 'The name is already used by another role in this store.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'permissions.array' => 'The permissions must be an array.',
            'permissions.*.uuid' => 'Each permission ID must be a valid UUID.',
        ];
    }
}
