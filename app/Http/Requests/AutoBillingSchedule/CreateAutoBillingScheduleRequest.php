<?php

namespace App\Http\Requests\AutoBillingSchedule;

use App\Models\AutoBillingSchedule;
use Illuminate\Foundation\Http\FormRequest;

class CreateAutoBillingScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', AutoBillingSchedule::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'active' => ['nullable', 'boolean'],
            'user_id' => ['required', 'uuid'],
            'pricing_plan_id' => ['required', 'uuid'],
            'payment_method_id' => ['required', 'uuid'],
            'store_id' => ['nullable', 'uuid'],
            'next_attempt_date' => ['nullable', 'date'],
            'attempt' => ['nullable', 'integer', 'min:0'],
            'overall_attempts' => ['nullable', 'integer', 'min:0'],
            'overall_failed_attempts' => ['nullable', 'integer', 'min:0'],
            'overall_successful_attempts' => ['nullable', 'integer', 'min:0'],
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
            'user_id.required' => 'The user ID is required.',
            'user_id.uuid' => 'The user ID must be a valid UUID.',
            'pricing_plan_id.required' => 'The pricing plan ID is required.',
            'pricing_plan_id.uuid' => 'The pricing plan ID must be a valid UUID.',
            'payment_method_id.required' => 'The payment method ID is required.',
            'payment_method_id.uuid' => 'The payment method ID must be a valid UUID.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'next_attempt_date.date' => 'The next attempt date must be a valid date.',
        ];
    }
}
