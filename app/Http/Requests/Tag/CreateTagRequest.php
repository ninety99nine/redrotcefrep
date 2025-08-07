<?php

namespace App\Http\Requests\Tag;

use App\Models\Tag;
use Illuminate\Foundation\Http\FormRequest;

class CreateTagRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Tag::class);
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
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['uuid', 'exists:products,id'],
            'store_id' => ['required', 'uuid', 'exists:stores,id']
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
            'name.required' => 'The tag name is required.',
            'name.string' => 'The tag name must be a string.',
            'name.max' => 'The tag name must not exceed 60 characters.',
            'product_ids.array' => 'The product IDs must be an array.',
            'product_ids.*.uuid' => 'Each product ID must be a valid UUID.',
            'product_ids.*.exists' => 'One or more product IDs do not exist.',
            'store_id.required' => 'The store ID is required.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'store_id.exists' => 'The specified store does not exist.',
        ];
    }
}
