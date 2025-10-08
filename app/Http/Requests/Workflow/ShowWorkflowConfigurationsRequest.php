<?php

namespace App\Http\Requests\Workflow;

use App\Models\Workflow;
use Illuminate\Foundation\Http\FormRequest;

class ShowWorkflowConfigurationsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Workflow::class);
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
            'target' => ['sometimes', 'string'],
            'trigger' => ['sometimes', 'string'],
            'action' => ['sometimes', 'string'],
            'template' => ['sometimes', 'string'],
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
            'target.string' => 'The target must be a string.',
            'trigger.string' => 'The trigger must be a string.',
            'action.string' => 'The action must be a string.',
            'template.string' => 'The template must be a string.',
        ];
    }
}
?>
