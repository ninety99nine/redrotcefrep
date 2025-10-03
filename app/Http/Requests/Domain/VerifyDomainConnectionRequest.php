<?php

namespace App\Http\Requests\Domain;

use App\Models\Domain;
use Illuminate\Foundation\Http\FormRequest;

class VerifyDomainConnectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('verifyAny', Domain::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['required','string','regex:/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/'],
            'store_id' => ['required', 'uuid'],
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
            'name.required' => 'The domain name is required.',
            'name.string' => 'The domain name must be a string.',
            'name.regex' => 'The domain name must be a valid domain (e.g., example.com).',
            'store_id.required' => 'The store ID is required.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
        ];
    }
}
