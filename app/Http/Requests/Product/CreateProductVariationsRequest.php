<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductVariationsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('product'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'variant_attributes' => ['required', 'array', 'min:1'],
            'variant_attributes.*.name' => ['required', 'string', 'max:50'],
            'variant_attributes.*.values' => ['required', 'array', 'min:1'],
            'variant_attributes.*.values.*' => ['string', 'max:50'],
            'variant_attributes.*.instruction' => ['nullable', 'string', 'max:255'],
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
            'variant_attributes.required' => 'The variant attributes are required.',
            'variant_attributes.array' => 'The variant attributes must be an array.',
            'variant_attributes.min' => 'At least one variant attribute is required.',
            'variant_attributes.*.name.required' => 'The variant attribute name is required.',
            'variant_attributes.*.name.string' => 'The variant attribute name must be a string.',
            'variant_attributes.*.name.max' => 'The variant attribute name must not exceed 50 characters.',
            'variant_attributes.*.values.required' => 'The variant attribute values are required.',
            'variant_attributes.*.values.array' => 'The variant attribute values must be an array.',
            'variant_attributes.*.values.min' => 'At least one value is required for each variant attribute.',
            'variant_attributes.*.values.*.string' => 'Each variant attribute value must be a string.',
            'variant_attributes.*.values.*.max' => 'Each variant attribute value must not exceed 50 characters.',
            'variant_attributes.*.instruction.string' => 'The variant attribute instruction must be a string.',
            'variant_attributes.*.instruction.max' => 'The variant attribute instruction must not exceed 255 characters.',
        ];
    }
}
