<?php

namespace App\Http\Requests\AutoBillingSchedule;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAutoBillingScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('autoBillingSchedule'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'active' => ['nullable', 'boolean']
        ];
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [];
    }
}
