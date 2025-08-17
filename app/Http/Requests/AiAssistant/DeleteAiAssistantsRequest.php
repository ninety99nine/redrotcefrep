<?php

namespace App\Http\Requests\AiAssistant;

use App\Models\AiAssistant;
use Illuminate\Foundation\Http\FormRequest;

class DeleteAiAssistantsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('deleteAny', AiAssistant::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'ai_assistant_ids' => ['required', 'array', 'min:1'],
            'ai_assistant_ids.*' => ['uuid'],
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
            'ai_assistant_ids.required' => 'The AI assistant IDs are required.',
            'ai_assistant_ids.array' => 'The AI assistant IDs must be an array.',
            'ai_assistant_ids.min' => 'At least one AI assistant ID is required.',
            'ai_assistant_ids.*.uuid' => 'Each AI assistant ID must be a valid UUID.',
        ];
    }
}
