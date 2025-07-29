<?php

namespace App\Http\Requests\OrderComment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('orderComment'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'comment' => ['sometimes', 'string', 'max:500']
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
            'comment.string' => 'The comment must be a string.',
            'comment.max' => 'The comment must not exceed 500 characters.'
        ];
    }
}
