<?php

namespace App\Http\Requests\AiTopic;

use App\Models\AiTopic;
use Illuminate\Foundation\Http\FormRequest;

class ShowAiTopicsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', AiTopic::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [

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

        ];
    }
}
