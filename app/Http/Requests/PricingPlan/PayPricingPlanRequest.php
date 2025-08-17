<?php

namespace App\Http\Requests\PricingPlan;

use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use App\Enums\PaymentMethodType;
use Illuminate\Foundation\Http\FormRequest;

class PayPricingPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('pay', $this->route('pricingPlan'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $pricingPlan = $this->route('pricingPlan');

        return [
            'store_id' => [
                $pricingPlan->offersSmsCredits() ||
                $pricingPlan->offersEmailCredits() ||
                $pricingPlan->offersWhatsappCredits() ||
                $pricingPlan->offersStoreSubscription() ? 'required' : 'sometimes', 'uuid'
            ],
            'payment_method_id' => ['required_without:payment_method_type', 'uuid'],
            'payment_method_type' => ['required_without:payment_method_id','string', 'in:' . implode(',', PaymentMethodType::values())],
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
            'store_id.required' => 'The store ID is required.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'payment_method_id.required_without' => 'The payment method ID is required when payment method type is not provided.',
            'payment_method_id.uuid' => 'The payment method ID must be a valid UUID.',
            'payment_method_type.required_without' => 'The payment method type is required when payment method ID is not provided.',
            'payment_method_type.in' => 'The payment method type must be one of: ' . Arr::join(PaymentMethodType::values(), ', ', ' or '),
        ];
    }
}
