<?php

namespace App\Http\Requests\PricingPlan;

use Illuminate\Foundation\Http\FormRequest;

class ShowPricingPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('view', $this->route('pricingPlan'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [];
    }
}
