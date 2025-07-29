<?php

namespace App\Http\Requests\MediaFile;

use App\Models\MediaFile;
use Illuminate\Support\Arr;
use App\Enums\UploadFolderName;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CreateMediaFileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', MediaFile::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:5120'],
            'mediable_id' => ['required', 'uuid'],
            'mediable_type' => ['required', 'string', Rule::in(['user', 'store', 'store payment method', 'product', 'transaction', 'order comment'])],
            'upload_folder_name' => ['required', Rule::enum(UploadFolderName::class)->except(UploadFolderName::QR_CODES)],
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
            'file.required' => 'The file is required.',
            'file.file' => 'The file must be a valid file.',
            'file.mimes' => 'The file must be a JPEG, PNG, JPG, GIF, or SVG.',
            'file.max' => 'The file size must not exceed 5MB.',
            'mediable_id.required' => 'The mediable ID is required.',
            'mediable_id.uuid' => 'The mediable ID must be a valid UUID.',
            'mediable_type.required' => 'The mediable type is required.',
            'mediable_type.in' => 'The mediable type must be one of: ' . Arr::join(['user', 'store', 'store payment method', 'product', 'transaction', 'order comment'], ', ', ' or '),
            'upload_folder_name.required' => 'The upload folder name is required.',
            'upload_folder_name.enum' => 'The upload folder name must be one of: ' . Arr::join(UploadFolderName::values(), ', ', ' or '),
        ];
    }
}
