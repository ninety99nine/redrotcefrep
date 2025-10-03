<?php

namespace App\Http\Requests\Domain;

use App\Enums\DomainStatus;
use App\Enums\DomainType;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDomainRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('domain'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $domain = $this->route('domain');

        return [
            'name' => [
                'sometimes',
                'string',
                'regex:/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/',
                Rule::unique('domains')
                    ->ignore($domain->id)
                    ->where('store_id', $this->input('store_id', $domain->store_id)),
            ],
            'type' => ['sometimes', Rule::enum(DomainType::class)],
            'status' => ['sometimes', Rule::enum(DomainStatus::class)]
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
            'name.string' => 'The domain name must be a string.',
            'name.regex' => 'The domain name must be a valid domain (e.g., example.com).',
            'name.unique' => 'This domain is already connected to the store.',
            'type.enum' => 'The type must be one of: ' . Arr::join(DomainType::values(), ', ', ' or '),
            'status.enum' => 'The status must be one of: ' . Arr::join(DomainStatus::values(), ', ', ' or ')
        ];
    }
}
