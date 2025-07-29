<?php

namespace App\Http\Requests\PricingPlan;

use App\Enums\Platform;
use App\Enums\Association;
use App\Models\PricingPlan;
use Illuminate\Support\Arr;
use App\Enums\PricingPlanType;
use Illuminate\Validation\Rule;
use App\Enums\PricingPlanBillingType;
use Illuminate\Foundation\Http\FormRequest;

class ShowPricingPlansRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', PricingPlan::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'platform' => ['nullable', Rule::enum(Platform::class)],
            'type' => ['nullable', Rule::enum(PricingPlanType::class)],
            'billing_type' => ['nullable', Rule::enum(PricingPlanBillingType::class)],
            'association' => ['sometimes', Rule::enum(Association::class)->only([Association::SUPER_ADMIN])]
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
            'association.enum' => 'The association must be one o: ' . Arr::join([Association::SUPER_ADMIN->value], ', ', ' or '),
            'type.enum' => 'The type must be one of: ' . Arr::join(PricingPlanType::values(), ', ', ' or '),
            'platform.enum' => 'The platform must be one of: ' . Arr::join(Platform::values(), ', ', ' or '),
            'billing_type.enum' => 'The billing type must be one of: ' . Arr::join(PricingPlanBillingType::values(), ', ', ' or '),
        ];
    }
}
