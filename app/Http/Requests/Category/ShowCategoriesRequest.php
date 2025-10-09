<?php

namespace App\Http\Requests\Category;

use App\Enums\Association;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ShowCategoriesRequest extends FormRequest
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
            'store_id' => ['sometimes', 'uuid'],
            'type' => ['sometimes', Rule::in(['parent'])],
            'association' => ['sometimes', Rule::enum(Association::class)->only([Association::SUPER_ADMIN, Association::TEAM_MEMBER, Association::SHOPPER->value])],
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
            'category_id.uuid' => 'The category ID must be a valid UUID.',
            'type.in' => 'The type must be one of: ' . Arr::join(['parent'], ', ', ' or '),
            'association.enum' => 'The association must be one of: ' . Arr::join([Association::SUPER_ADMIN->value, Association::TEAM_MEMBER->value, Association::SHOPPER->value], ', ', ' or '),
        ];
    }
}
