<?php

namespace App\Http\Requests\StorePaymentMethod;

use Illuminate\Foundation\Http\FormRequest;

use App\Models\StorePaymentMethod;

class UpdateStorePaymentMethodArrangementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', StorePaymentMethod::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'store_id' => ['required', 'uuid', 'exists:stores,id'],
            'store_payment_method_ids' => ['required', 'array'],
            'store_payment_method_ids.*' => ['uuid', 'exists:store_payment_method,id'],
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
            'store_id.exists' => 'The specified store does not exist.',
            'store_payment_method_ids.array' => 'Store payment method IDs must be an array.',
            'store_payment_method_ids.*.uuid' => 'Store payment method ID must be a valid UUID.',
            'store_payment_method_ids.*.exists' => 'Store payment method ID do not exist.',
        ];
    }
}
