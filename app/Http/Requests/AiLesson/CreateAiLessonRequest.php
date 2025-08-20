<?php

namespace App\Http\Requests\AiLesson;

use App\Models\AiLesson;
use Illuminate\Foundation\Http\FormRequest;

class CreateAiLessonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', AiLesson::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'ai_topic_id' => ['required', 'uuid'],
            'visible' => ['sometimes', 'boolean'],
            'title' => ['required', 'string', 'max:60'],
            'prompt' => ['required', 'string', 'max:500'],
            'position' => ['nullable', 'integer', 'min:0']
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
            'ai_topic_id.required' => 'The AI topic ID is required.',
            'ai_topic_id.uuid' => 'The AI topic ID must be a valid UUID.',
            'visible.boolean' => 'The visible field must be a boolean.',
            'title.required' => 'The title field is required.',
            'title.string' => 'The title must be a string.',
            'title.max' => 'The title may not be greater than 60 characters.',
            'prompt.required' => 'The prompt field is required.',
            'prompt.string' => 'The prompt must be a string.',
            'prompt.max' => 'The prompt may not be greater than 500 characters.',
            'position.integer' => 'The position must be an integer.',
            'position.min' => 'The position must be at least 0.',
        ];
    }
}
