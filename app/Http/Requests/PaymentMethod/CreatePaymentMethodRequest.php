<?php

namespace App\Http\Requests\PaymentMethod;

use App\Models\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentMethodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', PaymentMethod::class);
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
            'name' => ['required', 'string', 'max:40', 'unique:payment_methods,name'],
            'type' => ['required', 'string', 'max:40'],
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
