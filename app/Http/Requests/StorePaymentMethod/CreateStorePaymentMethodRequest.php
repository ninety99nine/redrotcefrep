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
            'custom_name' => ['required', 'string', 'max:20'],
            'instruction' => ['nullable', 'string'],
            'configs' => ['nullable', 'array'],
            'position' => ['nullable', 'integer', 'min:0', 'max:255'],
            'store_id' => ['required', 'uuid'],
            'payment_method_id' => ['required', 'uuid'],
        ];
    }
}
