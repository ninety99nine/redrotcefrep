<?php

namespace App\Http\Requests\Subscription;

use App\Models\Subscription;
use Illuminate\Foundation\Http\FormRequest;

class DeleteSubscriptionsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('deleteAny', Subscription::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'subscription_ids' => ['required', 'array', 'min:1'],
            'subscription_ids.*' => ['uuid', 'exists:subscriptions,id'],
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
            'subscription_ids.required' => 'The subscription IDs are required.',
            'subscription_ids.array' => 'The subscription IDs must be an array.',
            'subscription_ids.min' => 'At least one subscription ID is required.',
            'subscription_ids.*.uuid' => 'Each subscription ID must be a valid UUID.',
            'subscription_ids.*.exists' => 'One or more subscription IDs do not exist.',
        ];
    }
}
