<?php

namespace App\Http\Requests\AiAssistantTokenUsage;

use App\Enums\Association;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use App\Models\AiAssistantTokenUsage;
use Illuminate\Foundation\Http\FormRequest;

class ShowAiAssistantTokenUsagesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', AiAssistantTokenUsage::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'ai_assistant_token_usage_id' => ['sometimes', 'uuid'],
            'ai_assistant_id' => ['sometimes', 'uuid'],
            'association' => ['sometimes', Rule::enum(Association::class)->only([Association::ASSOCIATED, Association::UNASSOCIATED])],
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
            'ai_assistant_token_usage_id.uuid' => 'The AI assistant token usage ID must be a valid UUID.',
            'ai_assistant_id.uuid' => 'The AI assistant ID must be a valid UUID.',
            'association.enum' => 'The association must be one of: ' . Arr::join([Association::ASSOCIATED->value, Association::UNASSOCIATED->value], ', ', ' or '),
        ];
    }
}
