<?php

namespace App\Http\Requests\DeliveryAddress;

use App\Enums\AddressType;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDeliveryAddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('deliveryAddress'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'type' => ['sometimes', Rule::enum(AddressType::class)],
            'address_line' => ['sometimes', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'size:2'],
            'place_id' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'min:-90', 'max:90'],
            'longitude' => ['nullable', 'numeric', 'min:-180', 'max:180'],
            'description' => ['nullable', 'string'],
            'order_id' => ['sometimes', 'uuid', 'exists:orders,id'],
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
            'type.enum' => 'The address type must be one of: ' . Arr::join(AddressType::values(), ',', 'or'),
            'address_line.string' => 'The address line must be a string.',
            'address_line.max' => 'The address line must not exceed 255 characters.',
            'address_line2.string' => 'The second address line must be a string.',
            'address_line2.max' => 'The second address line must not exceed 255 characters.',
            'city.string' => 'The city must be a string.',
            'city.max' => 'The city must not exceed 100 characters.',
            'state.string' => 'The state must be a string.',
            'state.max' => 'The state must not exceed 100 characters.',
            'postal_code.string' => 'The postal code must be a string.',
            'postal_code.max' => 'The postal code must not exceed 20 characters.',
            'country.string' => 'The country must be a string.',
            'country.size' => 'The country code must be exactly 2 characters.',
            'place_id.string' => 'The place ID must be a string.',
            'place_id.max' => 'The place ID must not exceed 255 characters.',
            'latitude.numeric' => 'The latitude must be a number.',
            'latitude.min' => 'The latitude must be at least -90.',
            'latitude.max' => 'The latitude must not exceed 90.',
            'longitude.numeric' => 'The longitude must be a number.',
            'longitude.min' => 'The longitude must be at least -180.',
            'longitude.max' => 'The longitude must not exceed 180.',
            'order_id.uuid' => 'The order ID must be a valid UUID.',
            'order_id.exists' => 'The specified order does not exist.',
        ];
    }
}
