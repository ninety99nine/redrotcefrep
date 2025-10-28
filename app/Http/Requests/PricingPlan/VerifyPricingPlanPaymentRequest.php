<?php

namespace App\Http\Requests\PricingPlan;

use App\Models\PricingPlan;
use Illuminate\Foundation\Http\FormRequest;

class VerifyPricingPlanPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('verifyAny', PricingPlan::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [

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

        ];
    }
}
