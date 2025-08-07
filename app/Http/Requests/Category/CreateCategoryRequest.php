<?php

namespace App\Http\Requests\Category;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class CreateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Category::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60'],
            'visible' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:100'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['uuid', 'exists:products,id'],
            'store_id' => ['required', 'uuid', 'exists:stores,id'],
            'photo' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:5120']
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
            'name.required' => 'The category name is required.',
            'name.string' => 'The category name must be a string.',
            'name.max' => 'The category name must not exceed 60 characters.',
            'visible' => ['nullable', 'boolean'],
            'description.max' => 'The description must not exceed 100 characters.',
            'product_ids.array' => 'The product IDs must be an array.',
            'product_ids.*.uuid' => 'Each product ID must be a valid UUID.',
            'product_ids.*.exists' => 'One or more product IDs do not exist.',
            'store_id.required' => 'The store ID is required.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'store_id.exists' => 'The specified store does not exist.',
            'photo.file' => 'The photo must be a valid file.',
            'photo.mimes' => 'The photo must be a JPEG, PNG, JPG, GIF, or SVG.',
            'photo.max' => 'The photo size must not exceed 5MB.'
        ];
    }
}
