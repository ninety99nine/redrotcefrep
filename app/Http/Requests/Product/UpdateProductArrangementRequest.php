<?php

namespace App\Http\Requests\Product;

use App\Models\Product;
use Illuminate\Support\Arr;
use App\Enums\SortProductBy;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductArrangementRequest extends FormRequest
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
            'product_ids' => ['required_without:sort_by', 'array'],
            'product_ids.*' => ['uuid', 'exists:products,id'],
            'parent_product_id' => ['sometimes', 'uuid', 'exists:products,id'],
            'sort_by' => ['required_without:product_ids', Rule::enum(SortProductBy::class)],
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
            'product_ids.required_without' => 'Product IDs are required when sort_by is not provided.',
            'product_ids.array' => 'Product IDs must be an array.',
            'product_ids.*.uuid' => 'The product ID must be a valid UUID.',
            'product_ids.*.exists' => 'The product ID do not exist.',
            'parent_product_id.uuid' => 'The parent product ID must be a valid UUID.',
            'parent_product_id.exists' => 'The specified parent product does not exist.',
            'sort_by.required_without' => 'Sort by is required when product IDs are not provided.',
            'sort_by.enum' => 'The sort by value must be one of: ' . Arr::join(SortProductBy::values(), ', ', ' or '),
        ];
    }
}
