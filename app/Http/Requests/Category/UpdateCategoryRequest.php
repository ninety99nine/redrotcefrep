<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('category'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:60'],
            'visible' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:100'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['uuid', 'exists:products,id'],
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
            'name.string' => 'The category name must be a string.',
            'name.max' => 'The category name must not exceed 60 characters.',
            'visible' => ['nullable', 'boolean'],
            'description.max' => 'The description must not exceed 100 characters.',
            'product_ids.array' => 'The product IDs must be an array.',
            'product_ids.*.uuid' => 'Each product ID must be a valid UUID.',
            'product_ids.*.exists' => 'One or more product IDs do not exist.',
        ];
    }
}
