<?php

namespace App\Http\Requests\DeliveryMethod;

use App\Enums\Association;
use App\Models\DeliveryMethod;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ShowDeliveryMethodsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', DeliveryMethod::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'store_id' => ['sometimes', 'uuid', 'exists:stores,id'],
            'association' => ['sometimes', Rule::enum(Association::class)->only([Association::SHOPPER])],
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
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'store_id.exists' => 'The specified store does not exist.',
            'association.enum' => 'The association must be: shopper.',
        ];
    }
}
