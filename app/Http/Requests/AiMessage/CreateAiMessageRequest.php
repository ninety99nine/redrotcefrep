<?php

namespace App\Http\Requests\AiMessage;

use App\Models\AiMessage;
use Illuminate\Foundation\Http\FormRequest;

class CreateAiMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', AiMessage::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
        'user_content' => ['required_without:ai_lesson_id', 'string', 'min:3', 'max:500'],
        'ai_lesson_id' => ['required_without:user_content', 'uuid']
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
            'user_content.required_without' => 'User content is required when AI Lesson ID is not provided.',
            'user_content.string' => 'User content must be a string.',
            'user_content.min' => 'User content must be at least 3 characters.',
            'user_content.max' => 'User content must not exceed 500 characters.',
            'ai_lesson_id.required_without' => 'AI Lesson ID is required when User content is not provided.',
            'ai_lesson_id.uuid' => 'AI Lesson ID must be a valid UUID.',
        ];
    }
}
