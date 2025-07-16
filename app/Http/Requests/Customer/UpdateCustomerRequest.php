<?php

namespace App\Http\Requests\Customer;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('customer'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $customerId = $this->route('customer')->id;

        return [
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customerId)],
            'mobile_number' => ['nullable', 'string', 'max:20', Rule::unique('customers', 'mobile_number')->ignore($customerId)],
            'birthday' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'currency' => ['nullable', 'string', 'size:3'],
            'store_id' => ['sometimes', 'uuid', 'exists:stores,id'],
            'last_order_at' => ['nullable', 'date'],
            'total_orders' => ['nullable', 'integer', 'min:0'],
            'total_spend' => ['nullable', 'numeric', 'min:0'],
            'total_average_spend' => ['nullable', 'numeric', 'min:0'],
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
            'first_name.string' => 'The first name must be a string.',
            'first_name.max' => 'The first name must not exceed 255 characters.',
            'last_name.string' => 'The last name must be a string.',
            'last_name.max' => 'The last name must not exceed 255 characters.',
            'email.email' => 'The email must be a valid email address.',
            'email.max' => 'The email must not exceed 255 characters.',
            'email.unique' => 'The email is already in use.',
            'mobile_number.string' => 'The mobile number must be a string.',
            'mobile_number.max' => 'The mobile number must not exceed 20 characters.',
            'mobile_number.unique' => 'The mobile number is already in use.',
            'birthday.date' => 'The birthday must be a valid date.',
            'currency.string' => 'The currency must be a string.',
            'currency.size' => 'The currency must be exactly 3 characters.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'store_id.exists' => 'The specified store does not exist.',
            'last_order_at.date' => 'The last order date must be a valid date.',
            'total_orders.integer' => 'The total orders must be an integer.',
            'total_orders.min' => 'The total orders must be at least 0.',
            'total_spend.numeric' => 'The total spend must be a number.',
            'total_spend.min' => 'The total spend must be at least 0.',
            'total_average_spend.numeric' => 'The total average spend must be a number.',
            'total_average_spend.min' => 'The total average spend must be at least 0.',
        ];
    }
}
