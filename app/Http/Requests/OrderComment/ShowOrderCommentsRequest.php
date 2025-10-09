<?php

namespace App\Http\Requests\OrderComment;

use App\Enums\Association;
use Illuminate\Support\Arr;
use App\Models\OrderComment;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ShowOrderCommentsRequest extends FormRequest
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
            'order_id' => ['required_without:association', 'uuid'],
            'association' => ['sometimes', Rule::enum(Association::class)->only([Association::SUPER_ADMIN])]
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
            'order_id.required_without' => 'The order ID is required when association is not provided.',
            'order_id.uuid' => 'The order ID must be a valid UUID.',
            'association.enum' => 'The association must be one of: ' . Arr::join([Association::SUPER_ADMIN->value], ', ', ' or '),
        ];
    }
}
