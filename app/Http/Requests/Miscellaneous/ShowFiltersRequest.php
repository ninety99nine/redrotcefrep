<?php

namespace App\Http\Requests\Miscellaneous;

use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use App\Enums\FilterResourceType;
use Illuminate\Foundation\Http\FormRequest;

class ShowFiltersRequest extends FormRequest
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
            'type' => ['required', Rule::enum(FilterResourceType::class)],
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
            'type.enum' => 'The type must be one of: ' . Arr::join(FilterResourceType::values(), ', ', ' or '),
        ];
    }
}
