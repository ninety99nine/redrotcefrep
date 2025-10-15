<?php

namespace App\Http\Requests\Review;

use App\Models\Review;
use App\Enums\Association;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ShowReviewsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Review::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'uuid'],
            'store_id' => ['sometimes', 'uuid'],
            'association' => ['sometimes', Rule::enum(Association::class)->only([Association::SUPER_ADMIN, Association::TEAM_MEMBER])],
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
            'user_id.uuid' => 'The user ID must be a valid UUID.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'association.enum' => 'The association must be one of: ' . Arr::join([Association::SUPER_ADMIN->value, Association::TEAM_MEMBER->value], ', ', ' or '),
        ];
    }
}
