<?php

namespace App\Http\Requests\AiAssistant;

use App\Enums\Association;
use App\Models\AiAssistant;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ShowAiAssistantsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', AiAssistant::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'ai_assistant_id' => ['sometimes', 'uuid', 'exists:ai_assistants,id'],
            'user_id' => ['sometimes', 'uuid', 'exists:users,id'],
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
            'ai_assistant_id.uuid' => 'The AI assistant ID must be a valid UUID.',
            'ai_assistant_id.exists' => 'The specified AI assistant does not exist.',
            'user_id.uuid' => 'The user ID must be a valid UUID.',
            'user_id.exists' => 'The specified user does not exist.',
            'association.enum' => 'The association must be one of: ' . Arr::join([Association::ASSOCIATED->value, Association::UNASSOCIATED->value], ', ', ' or '),
        ];
    }
}
