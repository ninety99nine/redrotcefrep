<?php

namespace App\Http\Requests\MediaFile;

use App\Models\MediaFile;
use Illuminate\Foundation\Http\FormRequest;

class ShowMediaFilesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', MediaFile::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'media_file_id' => ['sometimes', 'uuid', 'exists:media_files,id'],
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
            'media_file_id.uuid' => 'The media file ID must be a valid UUID.',
            'media_file_id.exists' => 'The specified media file does not exist.',
        ];
    }
}
