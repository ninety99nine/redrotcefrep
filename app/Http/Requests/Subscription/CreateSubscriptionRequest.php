<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Support\Arr;
use App\Models\Subscription;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CreateSubscriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Subscription::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'user_id' => ['nullable', 'uuid', 'exists:users,id'],
            'transaction_id' => ['nullable', 'uuid', 'exists:transactions,id'],
            'pricing_plan_id' => ['nullable', 'uuid', 'exists:pricing_plans,id'],
            'owner_id' => ['required', 'uuid'],
            'owner_type' => ['required', 'string', Rule::in(['user', 'store'])],
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
            'user_id.exists' => 'The specified user does not exist.',
            'transaction_id.uuid' => 'The transaction ID must be a valid UUID.',
            'transaction_id.exists' => 'The specified transaction does not exist.',
            'pricing_plan_id.uuid' => 'The pricing plan ID must be a valid UUID.',
            'pricing_plan_id.exists' => 'The specified pricing plan does not exist.',
            'owner_id.required' => 'The owner ID is required.',
            'owner_id.uuid' => 'The owner ID must be a valid UUID.',
            'owner_type.required' => 'The owner type is required.',
            'owner_type.in' => 'The owner type must be one of: ' . Arr::join(['user', 'store'], ',', 'or'),
        ];
    }
}
