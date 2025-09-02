<?php

namespace App\Http\Requests\DesignCard;

use App\Models\DesignCard;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDesignCardArrangementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('updateAny', DesignCard::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'store_id' => ['required', 'uuid'],
            'design_card_ids' => ['required', 'array'],
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
            'store_id.required' => 'The store ID is required.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'design_card_ids.required' => 'The design card IDs are required.',
            'design_card_ids.array' => 'The design card IDs must be an array.',
            'design_card_ids.*.uuid' => 'Each design card ID must be a valid UUID.',
        ];
    }
}
