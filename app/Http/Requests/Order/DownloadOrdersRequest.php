<?php

namespace App\Http\Requests\Order;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class DownloadOrdersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('downloadAny', Order::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['uuid'],
            'store_id' => ['sometimes', 'uuid'],
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
            'order_ids.required' => 'The order IDs are required.',
            'order_ids.array' => 'The order IDs must be an array.',
            'order_ids.min' => 'At least one order ID is required.',
            'order_ids.*.uuid' => 'Each order ID must be a valid UUID.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
        ];
    }
}
