<?php

namespace App\Http\Requests\DeliveryMethod;

use App\Models\DeliveryMethod;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDeliveryMethodArrangementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('updateAny', DeliveryMethod::class);
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
            'delivery_method_ids' => ['required', 'array'],
            'delivery_method_ids.*' => ['uuid'],
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
            'delivery_method_ids.required' => 'The delivery method IDs are required.',
            'delivery_method_ids.array' => 'The delivery method IDs must be an array.',
            'delivery_method_ids.*.uuid' => 'Each delivery method ID must be a valid UUID.',
        ];
    }
}
