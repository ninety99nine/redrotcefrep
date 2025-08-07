<?php

namespace App\Http\Requests\Product;

use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('updateAny', Product::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'store_id' => ['required', 'uuid', 'exists:stores,id'],
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['uuid', 'exists:products,id'],
            'visible' => ['nullable', 'boolean'],
            'tags_to_add' => ['nullable', 'array'],
            'tags_to_add.*' => ['uuid', 'exists:tags,id'],
            'tags_to_remove' => ['nullable', 'array'],
            'tags_to_remove.*' => ['uuid', 'exists:tags,id'],
            'categories_to_add' => ['nullable', 'array'],
            'categories_to_add.*' => ['uuid', 'exists:categories,id'],
            'categories_to_remove' => ['nullable', 'array'],
            'categories_to_remove.*' => ['uuid', 'exists:categories,id'],
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
            'store_id.exists' => 'The specified store does not exist.',
            'product_ids.required' => 'At least one product ID is required.',
            'product_ids.array' => 'The product IDs must be an array.',
            'product_ids.min' => 'At least one product ID must be provided.',
            'product_ids.*.uuid' => 'Each product ID must be a valid UUID.',
            'product_ids.*.exists' => 'One or more product IDs are invalid.',
            'visible.boolean' => 'The visible field must be a boolean.',
            'tags_to_add.array' => 'The tags to add must be an array.',
            'tags_to_add.*.uuid' => 'Each tag ID to add must be a valid UUID.',
            'tags_to_add.*.exists' => 'One or more tag IDs to add are invalid.',
            'tags_to_remove.array' => 'The tags to remove must be an array.',
            'tags_to_remove.*.uuid' => 'Each tag ID to remove must be a valid UUID.',
            'tags_to_remove.*.exists' => 'One or more tag IDs to remove are invalid.',
            'categories_to_add.array' => 'The categories to add must be an array.',
            'categories_to_add.*.uuid' => 'Each category ID to add must be a valid UUID.',
            'categories_to_add.*.exists' => 'One or more category IDs to add are invalid.',
            'categories_to_remove.array' => 'The categories to remove must be an array.',
            'categories_to_remove.*.uuid' => 'Each category ID to remove must be a valid UUID.',
            'categories_to_remove.*.exists' => 'One or more category IDs to remove are invalid.',
            'data_collection_fields.array' => 'The data collection fields must be an array.',
        ];
    }
}
