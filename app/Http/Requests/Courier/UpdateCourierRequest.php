<?php

namespace App\Http\Requests\Courier;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('courier'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'tracking_page' => ['sometimes', 'string', 'max:255', 'url'],
            'position' => ['sometimes', 'integer', 'min:0', 'max:255'],
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
            'name.string' => 'The courier name must be a string.',
            'name.max' => 'The courier name must not exceed 255 characters.',
            'tracking_page.string' => 'The tracking page must be a string.',
            'tracking_page.max' => 'The tracking page must not exceed 255 characters.',
            'tracking_page.url' => 'The tracking page must be a valid URL.',
            'position.integer' => 'The position must be an integer.',
            'position.min' => 'The position must be at least 0.',
            'position.max' => 'The position must not exceed 255.',
        ];
    }
}
