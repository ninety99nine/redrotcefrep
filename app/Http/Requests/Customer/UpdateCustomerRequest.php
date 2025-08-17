<?php

namespace App\Http\Requests\Customer;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Address\UpdateAddressRequest;

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
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customerId)],
            'mobile_number' => ['nullable', 'phone:INTERNATIONAL', Rule::unique('customers', 'mobile_number')->ignore($customerId)],
            'birthday' => ['nullable', 'date'],
            'referral_code' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:500'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
            'store_id' => ['sometimes', 'uuid'],

            'address' => ['nullable', 'array'],
            ...(new UpdateAddressRequest())->rules(
                'address',
                [
                    'owner_type' => ['exclude'],
                    'owner_id' => ['exclude']
                ]
            )
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
            'email.unique' => 'The email is used by another customer.',
            'mobile_number.phone' => 'Please provide a valid mobile number (e.g., +26772000001).',
            'mobile_number.unique' => 'The mobile number is used by another customer.',
            'birthday.date' => 'The birthday must be a valid date.',
            'referral_code.string' => 'The referral code must be a string.',
            'referral_code.max' => 'The referral code must not exceed 40 characters.',
            'notes.string' => 'The notes must be a string.',
            'notes.max' => 'The notes must not exceed 500 characters.',
            'tags.array' => 'The tags must be an array.',
            'tags.string' => 'The tags must be a string.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',

            'address.array' => 'The address must be an array.',
            ...(new UpdateAddressRequest())->messages('address')
        ];
    }
}
