<?php

namespace App\Http\Requests\AiLesson;

use App\Models\AiLesson;
use Illuminate\Foundation\Http\FormRequest;

class DeleteAiLessonsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('deleteAny', AiLesson::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'ai_lesson_ids' => ['required', 'array', 'min:1'],
            'ai_lesson_ids.*' => ['uuid'],
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
            'ai_lesson_ids.required' => 'The AI lesson IDs are required.',
            'ai_lesson_ids.array' => 'The AI lesson IDs must be an array.',
            'ai_lesson_ids.min' => 'At least one AI lesson ID is required.',
            'ai_lesson_ids.*.uuid' => 'Each AI lesson ID must be a valid UUID.',
        ];
    }
}
