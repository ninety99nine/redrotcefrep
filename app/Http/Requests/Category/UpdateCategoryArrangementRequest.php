<?php

namespace App\Http\Requests\Category;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryArrangementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('updateAny', Category::class);
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
            'category_ids' => ['required', 'array'],
            'category_ids.*' => ['uuid']
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
            'category_ids.required' => 'Category IDs are required.',
            'category_ids.array' => 'Category IDs must be an array.',
            'category_ids.*.uuid' => 'The category ID must be a valid UUID.'
        ];
    }
}
