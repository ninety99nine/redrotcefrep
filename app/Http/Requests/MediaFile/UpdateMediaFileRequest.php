<?php

namespace App\Http\Requests\MediaFile;

use App\Models\MediaFile;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMediaFileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('mediaFile'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'file' => ['sometimes', 'file', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:5120'],
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
            'file.file' => 'The file must be a valid file.',
            'file.mimes' => 'The file must be a JPEG, PNG, JPG, GIF, or SVG.',
            'file.max' => 'The file size must not exceed 5MB.',
        ];
    }
}
