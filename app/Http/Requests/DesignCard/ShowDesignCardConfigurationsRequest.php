<?php

namespace App\Http\Requests\DesignCard;

use App\Enums\DesignCardType;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ShowDesignCardConfigurationsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', \App\Models\DesignCard::class);
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
            'type' => ['sometimes', Rule::in(DesignCardType::values())],
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
            'type.in' => 'The type must be one of: ' . implode(', ', DesignCardType::values()),
        ];
    }
}
?>
