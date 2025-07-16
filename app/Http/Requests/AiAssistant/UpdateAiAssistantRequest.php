<?php

namespace App\Http\Requests\AiAssistant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAiAssistantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('aiAssistant'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'total_paid_tokens' => ['sometimes', 'integer', 'min:0'],
            'remaining_free_tokens' => ['sometimes', 'integer', 'min:0'],
            'remaining_paid_tokens' => ['sometimes', 'integer', 'min:0'],
            'remaining_paid_top_up_tokens' => ['sometimes', 'integer', 'min:0'],
            'requires_subscription' => ['sometimes', 'boolean'],
            'user_id' => ['sometimes', 'uuid', 'exists:users,id'],
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
            'total_paid_tokens.integer' => 'The total paid tokens must be an integer.',
            'total_paid_tokens.min' => 'The total paid tokens must be at least 0.',
            'remaining_free_tokens.integer' => 'The remaining free tokens must be an integer.',
            'remaining_free_tokens.min' => 'The remaining free tokens must be at least 0.',
            'remaining_paid_tokens.integer' => 'The remaining paid tokens must be an integer.',
            'remaining_paid_tokens.min' => 'The remaining paid tokens must be at least 0.',
            'remaining_paid_top_up_tokens.integer' => 'The remaining paid top-up tokens must be an integer.',
            'remaining_paid_top_up_tokens.min' => 'The remaining paid top-up tokens must be at least 0.',
            'requires_subscription.boolean' => 'The requires subscription field must be a boolean.',
            'user_id.uuid' => 'The user ID must be a valid UUID.',
            'user_id.exists' => 'The specified user does not exist.',
        ];
    }
}
