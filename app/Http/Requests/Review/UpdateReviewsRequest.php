<?php

namespace App\Http\Requests\Review;

use App\Models\Review;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('updateAny', Review::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'store_id' => ['required', 'uuid'],
            'reviews' => ['required', 'array', 'min:1'],
            'reviews.*.id' => ['required', 'uuid'],
            'reviews.*.visible' => ['sometimes', 'boolean']
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
            'store_id.required' => 'The store ID is required.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'reviews.required' => 'At least one review is required.',
            'reviews.array' => 'The reviews must be an array.',
            'reviews.min' => 'At least one review must be provided.',
            'reviews.*.visible.boolean' => 'The visible must be a boolean.'
        ];
    }
}
