<?php

namespace App\Http\Requests\Category;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class DeleteCategoriesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('deleteAny', Category::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['uuid', 'exists:categories,id'],
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
            'category_ids.required' => 'The category IDs are required.',
            'category_ids.array' => 'The category IDs must be an array.',
            'category_ids.min' => 'At least one category ID is required.',
            'category_ids.*.uuid' => 'Each category ID must be a valid UUID.',
            'category_ids.*.exists' => 'One or more category IDs do not exist.',
        ];
    }
}
