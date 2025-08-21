<?php

namespace App\Http\Requests\AutoBillingSchedule;

use App\Models\AutoBillingSchedule;
use Illuminate\Foundation\Http\FormRequest;

class DeleteAutoBillingSchedulesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('deleteAny', AutoBillingSchedule::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'auto_billing_schedule_ids' => ['required', 'array', 'min:1'],
            'auto_billing_schedule_ids.*' => ['uuid'],
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
            'auto_billing_schedule_ids.required' => 'The AI assistant IDs are required.',
            'auto_billing_schedule_ids.array' => 'The AI assistant IDs must be an array.',
            'auto_billing_schedule_ids.min' => 'At least one AI assistant ID is required.',
            'auto_billing_schedule_ids.*.uuid' => 'Each AI assistant ID must be a valid UUID.',
        ];
    }
}
