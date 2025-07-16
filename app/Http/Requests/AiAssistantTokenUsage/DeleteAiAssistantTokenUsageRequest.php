<?php

namespace App\Http\Requests\AiAssistantTokenUsage;

use Illuminate\Foundation\Http\FormRequest;

class DeleteAiAssistantTokenUsageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('delete', $this->route('aiAssistantTokenUsage'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [];
    }
}
