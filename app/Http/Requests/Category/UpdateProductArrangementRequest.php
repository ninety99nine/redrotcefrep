<?php

namespace App\Http\Requests\Category;

use App\Models\Category;
use Illuminate\Support\Arr;
use App\Enums\SortCategoryBy;
use Illuminate\Validation\Rule;
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
            'category_ids' => ['required_without:sort_by', 'array'],
            'category_ids.*' => ['uuid'],
            'sort_by' => ['required_without:category_ids', Rule::enum(SortCategoryBy::class)],
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
            'category_ids.required_without' => 'Category IDs are required when sort_by is not provided.',
            'category_ids.array' => 'Category IDs must be an array.',
            'category_ids.*.uuid' => 'The category ID must be a valid UUID.',
            'sort_by.required_without' => 'Sort by is required when category IDs are not provided.',
            'sort_by.enum' => 'The sort by value must be one of: ' . Arr::join(SortCategoryBy::values(), ', ', ' or '),
        ];
    }
}
