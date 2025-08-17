<?php

namespace App\Http\Requests\Address;

use App\Models\Address;
use App\Enums\Association;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ShowAddressesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Address::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'address_id' => ['sometimes', 'uuid'],
            'owner_id' => ['sometimes', 'uuid'],
            'owner_type' => ['sometimes', 'string', Rule::in(['store', 'customer'])],
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
            'address_id.uuid' => 'The address ID must be a valid UUID.',
            'owner_id.uuid' => 'The owner ID must be a valid UUID.',
            'owner_type.in' => 'The owner type must be one of: ' . Arr::join(['store', 'customer'], ', ', ' or '),
            'association.enum' => 'The association must be one of: ' . Arr::join([Association::ASSOCIATED, Association::UNASSOCIATED], ', ', ' or '),
        ];
    }
}
