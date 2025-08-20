<?php

namespace App\Http\Requests\AiLesson;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAiLessonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('aiLesson'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'visible' => ['sometimes', 'boolean'],
            'title' => ['sometimes', 'string', 'max:60'],
            'prompt' => ['sometimes', 'string', 'max:500'],
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
            'visible.boolean' => 'The visible field must be a boolean.',
            'title.string' => 'The title must be a string.',
            'title.max' => 'The title may not be greater than 60 characters.',
            'prompt.string' => 'The prompt must be a string.',
            'prompt.max' => 'The prompt may not be greater than 500 characters.',
            'position.integer' => 'The position must be an integer.',
            'position.min' => 'The position must be at least 0.',
        ];
    }
}
