<?php

namespace App\Http\Requests\AiAssistantTokenUsage;

use App\Models\AiAssistantTokenUsage;
use Illuminate\Foundation\Http\FormRequest;

class DeleteAiAssistantTokenUsagesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('deleteAny', AiAssistantTokenUsage::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'ai_assistant_token_usage_ids' => ['required', 'array', 'min:1'],
            'ai_assistant_token_usage_ids.*' => ['uuid', 'exists:ai_assistant_token_usage,id'],
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
            'ai_assistant_token_usage_ids.required' => 'The AI assistant token usage IDs are required.',
            'ai_assistant_token_usage_ids.array' => 'The AI assistant token usage IDs must be an array.',
            'ai_assistant_token_usage_ids.min' => 'At least one AI assistant token usage ID is required.',
            'ai_assistant_token_usage_ids.*.uuid' => 'Each AI assistant token usage ID must be a valid UUID.',
            'ai_assistant_token_usage_ids.*.exists' => 'One or more AI assistant token usage IDs do not exist.',
        ];
    }
}
