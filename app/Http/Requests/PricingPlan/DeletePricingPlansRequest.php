<?php

namespace App\Http\Requests\PricingPlan;

use App\Models\PricingPlan;
use Illuminate\Foundation\Http\FormRequest;

class DeletePricingPlansRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('deleteAny', PricingPlan::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'pricing_plan_ids' => ['required', 'array', 'min:1'],
            'pricing_plan_ids.*' => ['uuid', 'exists:pricing_plans,id'],
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
            'pricing_plan_ids.required' => 'The pricing plan IDs are required.',
            'pricing_plan_ids.array' => 'The pricing plan IDs must be an array.',
            'pricing_plan_ids.min' => 'At least one pricing plan ID is required.',
            'pricing_plan_ids.*.uuid' => 'Each pricing plan ID must be a valid UUID.',
            'pricing_plan_ids.*.exists' => 'One or more pricing plan IDs do not exist.',
        ];
    }
}
