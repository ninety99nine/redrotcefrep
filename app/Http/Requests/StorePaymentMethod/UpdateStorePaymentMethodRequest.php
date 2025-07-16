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
            'custom_name' => ['sometimes', 'string', 'max:20'],
            'instruction' => ['sometimes', 'string'],
            'configs' => ['sometimes', 'array'],
            'position' => ['sometimes', 'integer', 'min:0', 'max:255'],
            'payment_method_id' => ['sometimes', 'uuid', 'exists:payment_methods,id'],
        ];
    }
}
