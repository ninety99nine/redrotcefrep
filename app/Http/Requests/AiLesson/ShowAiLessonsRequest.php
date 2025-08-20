<?php

namespace App\Http\Requests\AiLesson;

use App\Models\AiLesson;
use Illuminate\Foundation\Http\FormRequest;

class ShowAiLessonsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', AiLesson::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'ai_lesson_id' => ['sometimes', 'uuid'],
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
            'ai_lesson_id.uuid' => 'The store ID must be a valid UUID.',
        ];
    }
}
