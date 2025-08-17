<?php

namespace App\Http\Requests\DeliveryAddress;

use App\Enums\Association;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use App\Models\DeliveryAddress;
use Illuminate\Foundation\Http\FormRequest;

class ShowDeliveryAddressesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', DeliveryAddress::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'delivery_address_id' => ['sometimes', 'uuid'],
            'order_id' => ['sometimes', 'uuid'],
            'association' => ['sometimes', Rule::enum(Association::class)->only([Association::ASSOCIATED, Association::UNASSOCIATED])],
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
            'delivery_address_id.uuid' => 'The delivery address ID must be a valid UUID.',
            'order_id.uuid' => 'The order ID must be a valid UUID.',
            'association.enum' => 'The association must be one of: ' . Arr::join([Association::ASSOCIATED->value, Association::UNASSOCIATED->value], ', ', ' or '),
        ];
    }
}
