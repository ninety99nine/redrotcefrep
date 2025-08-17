<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('subscription'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'start_at' => ['sometimes', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'user_id' => ['nullable', 'uuid'],
            'transaction_id' => ['nullable', 'uuid'],
            'pricing_plan_id' => ['nullable', 'uuid'],
            'owner_id' => ['sometimes', 'uuid'],
            'owner_type' => ['sometimes', 'string', Rule::in(['user', 'store'])],
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
            'start_at.date' => 'The start date must be a valid date.',
            'end_at.date' => 'The end date must be a valid date.',
            'end_at.after_or_equal' => 'The end date must be on or after the start date.',
            'user_id.uuid' => 'The user ID must be a valid UUID.',
            'transaction_id.uuid' => 'The transaction ID must be a valid UUID.',
            'pricing_plan_id.uuid' => 'The pricing plan ID must be a valid UUID.',
            'owner_id.uuid' => 'The owner ID must be a valid UUID.',
            'owner_type.in' => 'The owner type must be one of: ' . Arr::join(['user', 'store'], ', ', ' or '),
        ];
    }
}
