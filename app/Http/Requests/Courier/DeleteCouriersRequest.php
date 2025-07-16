<?php

namespace App\Http\Requests\Courier;

use App\Models\Courier;
use Illuminate\Foundation\Http\FormRequest;

class DeleteCouriersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('deleteAny', Courier::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'courier_ids' => ['required', 'array', 'min:1'],
            'courier_ids.*' => ['uuid', 'exists:couriers,id'],
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
            'courier_ids.required' => 'The courier IDs are required.',
            'courier_ids.array' => 'The courier IDs must be an array.',
            'courier_ids.min' => 'At least one courier ID is required.',
            'courier_ids.*.uuid' => 'Each courier ID must be a valid UUID.',
            'courier_ids.*.exists' => 'One or more courier IDs do not exist.',
        ];
    }
}
