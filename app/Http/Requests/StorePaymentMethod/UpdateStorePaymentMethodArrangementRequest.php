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
        return $this->user()->can('updateAny', StorePaymentMethod::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'store_id' => ['required', 'uuid'],
            'store_payment_method_ids' => ['required', 'array'],
            'store_payment_method_ids.*' => ['uuid'],
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
            'store_payment_method_ids.array' => 'Store payment method IDs must be an array.',
            'store_payment_method_ids.*.uuid' => 'Store payment method ID must be a valid UUID.',
        ];
    }
}
