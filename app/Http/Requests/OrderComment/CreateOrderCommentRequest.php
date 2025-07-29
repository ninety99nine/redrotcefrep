<?php

namespace App\Http\Requests\OrderComment;

use App\Models\OrderComment;
use Illuminate\Foundation\Http\FormRequest;

class CreateOrderCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', OrderComment::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'comment' => ['required', 'string', 'max:500'],
            'order_id' => ['required', 'uuid', 'exists:orders,id'],
            'photo' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:5120']
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
            'comment.required' => 'The comment is required.',
            'comment.string' => 'The comment must be a string.',
            'comment.max' => 'The comment must not exceed 500 characters.',
            'order_id.required' => 'The order ID is required.',
            'order_id.uuid' => 'The order ID must be a valid UUID.',
            'order_id.exists' => 'The specified order does not exist.',
            'photo.file' => 'The photo must be a valid file.',
            'photo.mimes' => 'The photo must be a JPEG, PNG, JPG, GIF, or SVG.',
            'photo.max' => 'The photo size must not exceed 5MB.'
        ];
    }
}
