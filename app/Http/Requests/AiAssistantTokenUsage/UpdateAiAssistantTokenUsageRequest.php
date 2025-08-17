<?php

namespace App\Http\Requests\AiAssistantTokenUsage;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAiAssistantTokenUsageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('aiAssistantTokenUsage'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'request_tokens_used' => ['sometimes', 'integer', 'min:0'],
            'response_tokens_used' => ['sometimes', 'integer', 'min:0'],
            'free_tokens_used' => ['sometimes', 'integer', 'min:0'],
            'paid_tokens_used' => ['sometimes', 'integer', 'min:0'],
            'paid_top_up_tokens_used' => ['sometimes', 'integer', 'min:0'],
            'ai_assistant_id' => ['sometimes', 'uuid'],
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
            'request_tokens_used.integer' => 'The request tokens used must be an integer.',
            'request_tokens_used.min' => 'The request tokens used must be at least 0.',
            'response_tokens_used.integer' => 'The response tokens used must be an integer.',
            'response_tokens_used.min' => 'The response tokens used must be at least 0.',
            'free_tokens_used.integer' => 'The free tokens used must be an integer.',
            'free_tokens_used.min' => 'The free tokens used must be at least 0.',
            'paid_tokens_used.integer' => 'The paid tokens used must be an integer.',
            'paid_tokens_used.min' => 'The paid tokens used must be at least 0.',
            'paid_top_up_tokens_used.integer' => 'The paid top-up tokens used must be an integer.',
            'paid_top_up_tokens_used.min' => 'The paid top-up tokens used must be at least 0.',
            'ai_assistant_id.uuid' => 'The AI assistant ID must be a valid UUID.',
        ];
    }
}
