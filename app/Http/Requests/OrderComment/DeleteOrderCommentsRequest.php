<?php

namespace App\Http\Requests\OrderComment;

use App\Models\OrderComment;
use Illuminate\Foundation\Http\FormRequest;

class DeleteOrderCommentsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('deleteAny', OrderComment::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'order_comment_ids' => ['required', 'array', 'min:1'],
            'order_comment_ids.*' => ['uuid'],
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
            'order_comment_ids.required' => 'The order comment IDs are required.',
            'order_comment_ids.array' => 'The order comment IDs must be an array.',
            'order_comment_ids.min' => 'At least one order comment ID is required.',
            'order_comment_ids.*.uuid' => 'Each order comment ID must be a valid UUID.',
        ];
    }
}
