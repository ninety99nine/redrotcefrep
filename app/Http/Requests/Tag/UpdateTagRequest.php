<?php

namespace App\Http\Requests\Tag;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTagRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('tag'));
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
            'product_ids_to_add' => ['nullable', 'array'],
            'product_ids_to_add.*' => ['uuid'],
            'product_ids_to_remove' => ['nullable', 'array'],
            'product_ids_to_remove.*' => ['uuid'],
            'customer_ids_to_add' => ['nullable', 'array'],
            'customer_ids_to_add.*' => ['uuid'],
            'customer_ids_to_remove' => ['nullable', 'array'],
            'customer_ids_to_remove.*' => ['uuid'],
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
            'name.string' => 'The tag name must be a string.',
            'name.max' => 'The tag name must not exceed 60 characters.',
            'product_ids_to_add.array' => 'The product IDs must be an array.',
            'product_ids_to_add.*.uuid' => 'Each product ID must be a valid UUID.',
            'product_ids_to_remove.array' => 'The product IDs must be an array.',
            'product_ids_to_remove.*.uuid' => 'Each product ID must be a valid UUID.',
            'customer_ids_to_add.array' => 'The customer IDs must be an array.',
            'customer_ids_to_add.*.uuid' => 'Each customer ID must be a valid UUID.',
            'customer_ids_to_remove.array' => 'The customer IDs must be an array.',
            'customer_ids_to_remove.*.uuid' => 'Each customer ID must be a valid UUID.',
        ];
    }
}
