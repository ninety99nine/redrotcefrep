<?php

namespace App\Http\Requests\Store;

use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;

class DeleteStoresRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('deleteAny', Store::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'store_ids' => ['required', 'array', 'min:1'],
            'store_ids.*' => ['uuid', 'exists:stores,id'],
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
            'store_ids.required' => 'The store IDs are required.',
            'store_ids.array' => 'The store IDs must be an array.',
            'store_ids.min' => 'At least one store ID is required.',
            'store_ids.*.uuid' => 'Each store ID must be a valid UUID.',
            'store_ids.*.exists' => 'One or more store IDs do not exist.',
        ];
    }
}
