<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('review'));
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
            'name' => ['nullable', 'string', 'max:255'],
            'mobile_number' => ['nullable', 'phone:INTERNATIONAL'],
            'rating' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'store_id' => ['sometimes', 'uuid'],
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
            'visible.boolean' => 'The visible must be a boolean.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name must not exceed 255 characters.',
            'mobile_number.phone' => 'Please provide a valid mobile number (e.g., +26772000001).',
            'rating.integer' => 'The rating must be an integer.',
            'rating.min' => 'The rating must be at least 1.',
            'rating.max' => 'The rating must not exceed 5.',
            'comment.string' => 'The comment must be a string.',
            'comment.max' => 'The comment must not exceed 1000 characters.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
        ];
    }
}
