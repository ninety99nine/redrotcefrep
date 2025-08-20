<?php

namespace App\Http\Requests\AiTopic;

use App\Models\AiTopic;
use Illuminate\Foundation\Http\FormRequest;

class CreateAiTopicRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', AiTopic::class);
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
            'name' => ['required', 'string', 'max:60'],
            'position' => ['nullable', 'integer', 'min:0'],
            'system_prompt' => ['nullable', 'string', 'max:500'],
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
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 60 characters.',
            'position.integer' => 'The position must be an integer.',
            'position.min' => 'The position must be at least 0.',
            'system_prompt.string' => 'The system prompt must be a string.',
            'system_prompt.max' => 'The system prompt may not be greater than 500 characters.',
        ];
    }
}
