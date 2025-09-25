<?php

namespace App\Http\Requests\StorePaymentMethod;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStorePaymentMethodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('storePaymentMethod'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'active' => ['sometimes', 'boolean'],
            'custom_name' => ['sometimes', 'string', 'max:40'],
            'instruction' => ['nullable', 'string'],
            'configs' => ['sometimes', 'array'],
            'require_proof_of_payment' => ['sometimes', 'boolean'],
            'enable_contact_seller_before_payment' => ['sometimes', 'boolean'],
            'mark_as_paid_on_customer_confirmation' => ['sometimes', 'boolean'],
            'position' => ['sometimes', 'integer', 'min:0', 'max:255'],
            'payment_method_id' => ['sometimes', 'uuid'],
        ];
    }
}
