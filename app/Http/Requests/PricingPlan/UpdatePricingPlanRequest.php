<?php

namespace App\Http\Requests\PricingPlan;

use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use App\Enums\PricingPlanBillingType;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePricingPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('pricingPlan'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'active' => ['sometimes', 'boolean'],
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'billing_type' => ['sometimes', Rule::enum(PricingPlanBillingType::class)],
            'currency' => ['sometimes', 'string', 'size:3'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'discount_percentage_rate' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'max_auto_billing_attempts' => ['sometimes', 'integer', 'min:0'],
            'auto_billing_disabled_sms_message' => ['nullable', 'string'],
            'supports_web' => ['sometimes', 'boolean'],
            'supports_ussd' => ['sometimes', 'boolean'],
            'supports_mobile' => ['sometimes', 'boolean'],
            'metadata' => ['nullable', 'json'],
            'features' => ['nullable', 'json'],
            'position' => ['nullable', 'integer', 'min:0'],
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
            'active.boolean' => 'The active field must be a boolean.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'type.string' => 'The type must be a string.',
            'type.max' => 'The type may not be greater than 255 characters.',
            'description.string' => 'The description must be a string.',
            'billing_type.in' => 'The billing type must be one of: ' . Arr::join(PricingPlanBillingType::values(), ', ', ' or '),
            'currency.size' => 'The currency must be exactly 3 characters.',
            'price.numeric' => 'The price must be a number.',
            'price.min' => 'The price must be at least 0.',
            'discount_percentage_rate.integer' => 'The discount percentage rate must be an integer.',
            'discount_percentage_rate.min' => 'The discount percentage rate must be at least 0.',
            'discount_percentage_rate.max' => 'The discount percentage rate may not be greater than 100.',
            'max_auto_billing_attempts.integer' => 'The max auto billing attempts must be an integer.',
            'max_auto_billing_attempts.min' => 'The max auto billing attempts must be at least 0.',
            'auto_billing_disabled_sms_message.string' => 'The auto billing disabled SMS message must be a string.',
            'supports_web.boolean' => 'The supports web field must be a boolean.',
            'supports_ussd.boolean' => 'The supports USSD field must be a boolean.',
            'supports_mobile.boolean' => 'The supports mobile field must be a boolean.',
            'metadata.json' => 'The metadata must be valid JSON.',
            'features.json' => 'The features must be valid JSON.',
            'position.integer' => 'The position must be an integer.',
            'position.min' => 'The position must be at least 0.',
        ];
    }
}
