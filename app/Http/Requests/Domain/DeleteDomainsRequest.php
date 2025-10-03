<?php

namespace App\Http\Requests\Domain;

use App\Models\Domain;
use Illuminate\Foundation\Http\FormRequest;

class DeleteDomainsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('deleteAny', Domain::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'domain_ids' => ['required', 'array', 'min:1'],
            'domain_ids.*' => ['uuid'],
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
            'domain_ids.required' => 'The domain IDs are required.',
            'domain_ids.array' => 'The domain IDs must be an array.',
            'domain_ids.min' => 'At least one domain ID is required.',
            'domain_ids.*.uuid' => 'Each domain ID must be a valid UUID.',
        ];
    }
}
