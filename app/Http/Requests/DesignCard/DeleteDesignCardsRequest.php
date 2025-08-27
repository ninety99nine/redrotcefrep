<?php

namespace App\Http\Requests\DesignCard;

use App\Models\DesignCard;
use Illuminate\Foundation\Http\FormRequest;

class DeleteDesignCardsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('deleteAny', DesignCard::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'design_card_ids' => ['required', 'array', 'min:1'],
            'design_card_ids.*' => ['uuid'],
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
            'design_card_ids.required' => 'The design card IDs are required.',
            'design_card_ids.array' => 'The design card IDs must be an array.',
            'design_card_ids.min' => 'At least one design card ID is required.',
            'design_card_ids.*.uuid' => 'Each design card ID must be a valid UUID.',
        ];
    }
}
