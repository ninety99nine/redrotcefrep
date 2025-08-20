<?php

namespace App\Http\Requests\AiMessage;

use App\Models\AiMessage;
use Illuminate\Foundation\Http\FormRequest;

class DeleteAiMessagesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('deleteAny', AiMessage::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'ai_message_ids' => ['required', 'array', 'min:1'],
            'ai_message_ids.*' => ['uuid'],
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
            'ai_message_ids.required' => 'The AI message IDs are required.',
            'ai_message_ids.array' => 'The AI message IDs must be an array.',
            'ai_message_ids.min' => 'At least one AI message ID is required.',
            'ai_message_ids.*.uuid' => 'Each AI message ID must be a valid UUID.',
        ];
    }
}
