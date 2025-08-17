<?php

namespace App\Http\Requests\Customer;

use App\Http\Requests\Address\CreateAddressRequest;
use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;

class CreateCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Customer::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules($importing = false): array
    {
        return [
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required_without:mobile_number', , 'email', 'max:255', $importing ? '' : 'unique:customers,email'],
            'mobile_number' => ['required_without:email', 'phone:INTERNATIONAL', $importing ? '' : 'unique:customers,mobile_number'],
            'birthday' => ['nullable', 'date'],
            'referral_code' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:500'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
            'store_id' => ['required', 'uuid'],

            'address' => ['nullable', 'array'],
            ...(new CreateAddressRequest())->rules(
                'address',
                [
                    'address_line' => ['required_with:address', 'string', 'max:255'],
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
            'email.required_without' => 'The email is required when mobile number is not provided.',
            'email.email' => 'The email must be a valid email address.',
            'email.max' => 'The email must not exceed 255 characters.',
            'email.unique' => 'The email is used by another customer.',
            'mobile_number.required_without' => 'The mobile number is required when email is not provided.',
            'mobile_number.phone' => 'Please provide a valid mobile number (e.g., +26772000001).',
            'mobile_number.unique' => 'The mobile number is used by another customer.',
            'referral_code.string' => 'The referral code must be a string.',
            'referral_code.max' => 'The referral code must not exceed 40 characters.',
            'notes.string' => 'The notes must be a string.',
            'notes.max' => 'The notes must not exceed 500 characters.',
            'tags.array' => 'The tags must be an array.',
            'tags.string' => 'The tags must be a string.',
            'store_id.required' => 'The store ID is required.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',

            'address.array' => 'The address must be an array.',
            ...(new CreateAddressRequest())->messages('address', ['address_line.required_with' => 'The address line is required when an address is provided.'])
        ];
    }
}
