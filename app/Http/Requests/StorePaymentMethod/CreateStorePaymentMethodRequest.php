<?php

namespace App\Http\Requests\StorePaymentMethod;

use App\Models\StorePaymentMethod;
use Illuminate\Foundation\Http\FormRequest;

class CreateStorePaymentMethodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', StorePaymentMethod::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'active' => ['boolean'],
            'custom_name' => ['required', 'string', 'max:40'],
            'instruction' => ['nullable', 'string'],
            'configs' => ['nullable', 'array'],
            'require_proof_of_payment' => ['sometimes', 'boolean'],
            'enable_contact_seller_before_payment' => ['sometimes', 'boolean'],
            'mark_as_paid_on_customer_confirmation' => ['sometimes', 'boolean'],
            'position' => ['nullable', 'integer', 'min:0', 'max:255'],
            'store_id' => ['required', 'uuid'],
            'payment_method_id' => ['required', 'uuid'],
        ];
    }
}
