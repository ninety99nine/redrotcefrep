<?php

namespace App\Http\Requests\Customer;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('updateAny', Customer::class);
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
            'customers' => ['required', 'array', 'min:1'],
            'customers.*.id' => ['required', 'uuid'],
            'customers.*.first_name' => ['sometimes', 'string', 'max:255'],
            'customers.*.last_name' => ['nullable', 'string', 'max:255'],
            'customers.*.email' => ['nullable', 'email', 'max:255', function ($attribute, $value, $fail) {
                $index = explode('.', $attribute)[1] ?? null;
                $customerId = $this->input("customers.$index.id");

                if ($value && Customer::where('email', $value)
                    ->where('id', '!=', $customerId)
                    ->exists()) {
                    $fail("The email {$value} is duplicated.");
                }
            }],
            'customers.*.mobile_number' => ['nullable', 'phone:INTERNATIONAL', function ($attribute, $value, $fail) {
                $index = explode('.', $attribute)[1] ?? null;
                $customerId = $this->input("customers.$index.id");

                if ($value && Customer::where('mobile_number', $value)
                    ->where('id', '!=', $customerId)
                    ->exists()) {
                    $fail("The mobile number {$value} is duplicated.");
                }
            }],
            'customers.*.birthday' => ['nullable', 'date'],
            'customers.*.referral_code' => ['nullable', 'string', 'max:40'],
            'customers.*.notes' => ['nullable', 'string', 'max:500'],
            'customers.*.tags' => ['nullable', 'array'],
            'customers.*.tags.*' => ['string'],
            'notes' => ['nullable', 'string', 'max:500'],
            'tags_to_add' => ['nullable', 'array'],
            'tags_to_add.*' => ['uuid'],
            'tags_to_remove' => ['nullable', 'array'],
            'tags_to_remove.*' => ['uuid'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            foreach ($this->input('customers', []) as $index => $customer) {

                $emailExists = array_key_exists('email', $customer);
                $mobileExists = array_key_exists('mobile_number', $customer);

                if (
                    $emailExists && $mobileExists &&
                    empty($customer['email']) && empty($customer['mobile_number'])
                ) {
                    $validator->errors()->add(
                        "customers.$index.email",
                        'Either an email or mobile number is required for each customer, both cannot be empty.'
                    );
                }
            }
        });
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
            'customers.required' => 'At least one customer is required.',
            'customers.array' => 'The customers must be an array.',
            'customers.min' => 'At least one customer must be provided.',
            'customers.*.id.required' => 'Each customer must have an ID.',
            'customers.*.id.uuid' => 'Each customer ID must be a valid UUID.',
            'customers.*.first_name.required' => 'The first name is required.',
            'customers.*.first_name.string' => 'The first name must be a string.',
            'customers.*.first_name.max' => 'The first name must not exceed 255 characters.',
            'customers.*.last_name.string' => 'The last name must be a string.',
            'customers.*.last_name.max' => 'The last name must not exceed 255 characters.',
            'customers.*.email.email' => 'The email must be a valid email address.',
            'customers.*.email.max' => 'The email must not exceed 255 characters.',
            'customers.*.mobile_number.phone' => 'Please provide a valid mobile number (e.g., +26772000001).',
            'customers.*.mobile_number.unique' => 'The mobile number is used by another customer.',
            'customers.*.birthday.date' => 'The customers.*.birthday must be a valid date.',
            'customers.*.referral_code.string' => 'The referral code must be a string.',
            'customers.*.referral_code.max' => 'The referral code must not exceed 40 characters.',
            'customers.*.notes.string' => 'The customers.*.notes must be a string.',
            'customers.*.notes.max' => 'The customers.*.notes must not exceed 500 characters.',
            'customers.*.tags.array' => 'The tags must be an array.',
            'customers.*.tags.string' => 'The tags must be a string.',
            'notes.string' => 'The customers.*.notes must be a string.',
            'notes.max' => 'The customers.*.notes must not exceed 500 characters.',
            'tags_to_add.array' => 'The tags to add must be an array.',
            'tags_to_add.*.uuid' => 'Each tag ID to add must be a valid UUID.',
            'tags_to_remove.array' => 'The tags to remove must be an array.',
            'tags_to_remove.*.uuid' => 'Each tag ID to remove must be a valid UUID.',
        ];
    }
}
