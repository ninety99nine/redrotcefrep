<?php

namespace App\Http\Requests\Workflow;

use App\Models\Workflow;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkflowArrangementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('updateAny', Workflow::class);
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
            'workflow_ids' => ['required', 'array'],
            'workflow_ids.*' => ['uuid'],
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
            'workflow_ids.required' => 'The workflow IDs are required.',
            'workflow_ids.array' => 'The workflow IDs must be an array.',
            'workflow_ids.*.uuid' => 'Each workflow ID must be a valid UUID.',
        ];
    }
}
