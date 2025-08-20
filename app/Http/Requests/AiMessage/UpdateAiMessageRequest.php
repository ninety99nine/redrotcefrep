<?php

namespace App\Http\Requests\AiMessage;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAiMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('aiMessage'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'user_content' => ['required', 'string', 'min:3', 'max:500'],
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
            'user_content.required' => 'User content is required.',
            'user_content.string' => 'User content must be a string.',
            'user_content.max' => 'User content must be at least 3 characters.',
            'user_content.max' => 'User content must not exceed 500 characters.',
        ];
    }
}
