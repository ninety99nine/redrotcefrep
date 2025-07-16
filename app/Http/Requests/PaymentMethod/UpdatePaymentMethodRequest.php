<?php

namespace App\Http\Requests\PaymentMethod;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentMethodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('paymentMethod'));
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
            'name' => ['string', 'max:40', Rule::unique('payment_methods')->ignore($this->paymentMethod->id)],
            'type' => ['string', 'max:40'],
            'automated_verification' => ['boolean'],
            'currencies' => ['nullable', 'array'],
            'countries' => ['nullable', 'array'],
            'allowed_countries' => ['nullable', 'array'],
            'ussd_codes' => ['nullable', 'array'],
            'config_schema' => ['nullable', 'array'],
            'position' => ['nullable', 'integer', 'min:0', 'max:255'],
        ];
    }
}
