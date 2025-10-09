<?php

namespace App\Http\Requests\DesignCard;

use App\Enums\Association;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use App\Enums\DesignCardPlacement;
use Illuminate\Foundation\Http\FormRequest;

class ShowDesignCardsRequest extends FormRequest
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
            'placement' => ['sometimes', Rule::enum(DesignCardPlacement::class)],
            'association' => ['sometimes', Rule::enum(Association::class)->only([Association::SHOPPER])],
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
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'association.enum' => 'The association must be: shopper.',
            'placement.enum' => 'The design card placement must be one of: ' . Arr::join(DesignCardPlacement::values(), ', ', ' or '),
        ];
    }
}
