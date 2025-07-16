<?php

namespace App\Http\Requests\Miscellaneous;

use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use App\Enums\SortResourceType;
use Illuminate\Foundation\Http\FormRequest;

class ShowSortingRequest extends FormRequest
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
            'type' => ['required', Rule::enum(SortResourceType::class)],
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
            'type.enum' => 'The type must be one of: ' . Arr::join(SortResourceType::values(), ',', 'or'),
        ];
    }
}
