<?php

namespace App\Http\Requests\Promotion;

use App\Models\Promotion;
use Illuminate\Foundation\Http\FormRequest;

class DeletePromotionsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('deleteAny', Promotion::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'promotion_ids' => ['required', 'array', 'min:1'],
            'promotion_ids.*' => ['uuid'],
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
            'promotion_ids.required' => 'The promotion IDs are required.',
            'promotion_ids.array' => 'The promotion IDs must be an array.',
            'promotion_ids.min' => 'At least one promotion ID is required.',
            'promotion_ids.*.uuid' => 'Each promotion ID must be a valid UUID.',
        ];
    }
}
