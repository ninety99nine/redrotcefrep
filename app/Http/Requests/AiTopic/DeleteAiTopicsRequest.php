<?php

namespace App\Http\Requests\AiTopic;

use App\Models\AiTopic;
use Illuminate\Foundation\Http\FormRequest;

class DeleteAiTopicsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('deleteAny', AiTopic::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'ai_topic_ids' => ['required', 'array', 'min:1'],
            'ai_topic_ids.*' => ['uuid'],
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
            'ai_topic_ids.required' => 'The AI topic IDs are required.',
            'ai_topic_ids.array' => 'The AI topic IDs must be an array.',
            'ai_topic_ids.min' => 'At least one AI topic ID is required.',
            'ai_topic_ids.*.uuid' => 'Each AI topic ID must be a valid UUID.',
        ];
    }
}
