<?php

namespace App\Http\Requests\Product;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductVisibilityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', Product::class);
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
            'visibility' => ['required', 'array'],
            'visibility.*.visible' => ['bail', 'boolean'],
            'visibility.*.id' => ['bail', 'uuid', 'distinct', 'exists:products,id'],
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
            'visibility.required' => 'The visibility data is required.',
            'visibility.array' => 'The visibility data must be an array.',
            'visibility.*.visible.boolean' => 'The product visibility must be a boolean.',
            'visibility.*.id.uuid' => 'The product ID must be a valid UUID.',
            'visibility.*.id.exists' => 'The product ID do not exist.',
            'visibility.*.id.distinct' => 'Duplicate product IDs are not allowed.',
        ];
    }
}
