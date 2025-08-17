<?php

namespace App\Http\Requests\Address;

use App\Models\Address;
use Illuminate\Foundation\Http\FormRequest;

class DeleteAddressesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('deleteAny', Address::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'address_ids' => ['required', 'array', 'min:1'],
            'address_ids.*' => ['uuid'],
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
            'address_ids.required' => 'The address IDs are required.',
            'address_ids.array' => 'The address IDs must be an array.',
            'address_ids.min' => 'At least one address ID is required.',
            'address_ids.*.uuid' => 'Each address ID must be a valid UUID.',
        ];
    }
}
