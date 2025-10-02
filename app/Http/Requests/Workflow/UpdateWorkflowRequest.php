<?php

namespace App\Http\Requests\Workflow;

use Illuminate\Support\Arr;
use App\Enums\WorkflowTarget;
use App\Enums\WorkflowTrigger;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkflowRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('workflow'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $workflow = $this->route('workflow');

        return [
            'active' => ['nullable', 'boolean'],
            'name' => [
                'sometimes', 'string', 'max:40',
                Rule::unique('workflows')
                    ->ignore($workflow->id)
                    ->where(function ($query) use ($workflow) {
                        return $query->where('store_id', $this->input('store_id', $workflow->store_id));
                    }),
            ],
            'target' => ['sometimes', Rule::enum(WorkflowTarget::class)],
            'trigger' => ['sometimes', Rule::enum(WorkflowTrigger::class)],
            'actions' => ['nullable', 'array'],
            'position' => ['nullable', 'integer', 'min:0', 'max:255']
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
            'name.string' => 'The workflow name must be a string.',
            'name.max' => 'The workflow name must not exceed 40 characters.',
            'target.enum' => 'The fee type must be one of: ' . Arr::join(WorkflowTarget::values(), ', ', ' or '),
            'trigger.enum' => 'The fee type must be one of: ' . Arr::join(WorkflowTrigger::values(), ', ', ' or '),
            'actions.array' => 'The actions must be an array.',
            'position.integer' => 'The position must be an integer.',
            'position.min' => 'The position must be at least 0.',
            'position.max' => 'The position must not exceed 255.'
        ];
    }
}
