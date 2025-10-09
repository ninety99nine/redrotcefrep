<?php

namespace App\Http\Requests\Product;

use App\Enums\Association;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ShowProductsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'tag_id' => ['sometimes', 'uuid'],
            'store_id' => ['sometimes', 'uuid'],
            'category_ids' => ['sometimes', 'array'],
            'category_ids.*' => ['uuid'],
            'association' => ['sometimes', Rule::enum(Association::class)->only([Association::SUPER_ADMIN, Association::TEAM_MEMBER, Association::SHOPPER])],
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
            'tag_id.uuid' => 'The tag ID must be a valid UUID.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'association.enum' => 'The association must be one of: ' . Arr::join([Association::SUPER_ADMIN->value, Association::TEAM_MEMBER->value, Association::SHOPPER->value], ', ', ' or '),
            'category_ids.array' => 'The category IDs must be an array.',
            'category_ids.*.uuid' => 'Each category ID must be a valid UUID.',
        ];
    }
}
