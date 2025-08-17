<?php

namespace App\Http\Requests\DeliveryAddress;

use App\Models\DeliveryAddress;
use Illuminate\Foundation\Http\FormRequest;

class DeleteDeliveryAddressesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('deleteAny', DeliveryAddress::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'delivery_address_ids' => ['required', 'array', 'min:1'],
            'delivery_address_ids.*' => ['uuid'],
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
            'delivery_address_ids.required' => 'The delivery address IDs are required.',
            'delivery_address_ids.array' => 'The delivery address IDs must be an array.',
            'delivery_address_ids.min' => 'At least one delivery address ID is required.',
            'delivery_address_ids.*.uuid' => 'Each delivery address ID must be a valid UUID.',
        ];
    }
}
