<?php

namespace App\Services;

use Exception;
use App\Models\User;
use App\Models\Store;
use App\Models\Product;
use App\Models\MediaFile;
use App\Enums\UploadFolderName;
use App\Models\StorePaymentMethod;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\MediaFileResource;
use App\Http\Resources\MediaFileResources;

class MediaFileService extends BaseService
{
    /**
     * @param array $data
     * Show media files.
     *
     * @return MediaFileResources|array
     */
    public function showMediaFiles(array $data): MediaFileResources|array
    {
        $query = MediaFile::query()->when(!request()->has('_sort'), fn($query) => $query->latest());
        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create media file.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function createMediaFile(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $file = $data['file'];
            $type = $data['type'];
            $mediableData = $this->getMediableData($data);
            $uploadFolderName = UploadFolderName::from($type);

            $this->handleSingleFileTypes($type, $mediableData);
            $this->checkMediaFileLimits($type, $mediableData);

            $filePath = AWSService::store($uploadFolderName, $file);

            $mediaFile = MediaFile::create([
                'type' => $type,
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'mediable_id' => $mediableData['id'],
                'mediable_type' => $mediableData['type'],
                'width' => getimagesize($file)[0] ?? null,
                'height' => getimagesize($file)[1] ?? null,
                'file_name' => $file->getClientOriginalName(),
            ]);

            return $this->showCreatedResource($mediaFile);
        });
    }

    /**
     * Delete media files.
     *
     * @param array $mediaFileIds
     * @return array
     * @throws Exception
     */
    public function deleteMediaFiles(array $mediaFileIds): array
    {
        $mediaFiles = MediaFile::whereIn('id', $mediaFileIds)->get();

        if ($totalMediaFiles = $mediaFiles->count()) {

            foreach ($mediaFiles as $mediaFile) {
                $this->deleteMediaFile($mediaFile);
            }

            return ['message' => $totalMediaFiles . ($totalMediaFiles == 1 ? ' Media File' : ' Media Files') . ' deleted'];

        } else {
            throw new Exception('No Media Files deleted');
        }
    }

    /**
     * Update media file.
     *
     * @param MediaFile $mediaFile
     * @param array $data
     * @return array
     */
    public function updateMediaFile(MediaFile $mediaFile, array $data): array
    {
        return DB::transaction(function () use ($mediaFile, $data) {

            if (isset($data['file'])) {

                // Delete old file from S3
                AWSService::delete($mediaFile->file_path);

                // Upload new file
                $file = $data['file'];
                $uploadFolderName = UploadFolderName::from($mediaFile->type);
                $filePath = AWSService::store($uploadFolderName, $file);

                $updateData = [
                    'file_path' => $filePath,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'width' => getimagesize($file)[0] ?? null,
                    'height' => getimagesize($file)[1] ?? null,
                    'file_name' => $file->getClientOriginalName(),
                ];

                $mediaFile->update($updateData);

            }

            return $this->showUpdatedResource($mediaFile);
        });
    }

    /**
     * Show media file.
     *
     * @param MediaFile $mediaFile
     * @return MediaFileResource
     */
    public function showMediaFile(MediaFile $mediaFile): MediaFileResource
    {
        return $this->showResource($mediaFile);
    }

    /**
     * Delete media file.
     *
     * @param MediaFile $mediaFile
     * @return array
     * @throws Exception
     */
    public function deleteMediaFile(MediaFile $mediaFile): array
    {
        return DB::transaction(function () use ($mediaFile) {

            AWSService::delete($mediaFile->file_path);
            $deleted = $mediaFile->delete();

            if ($deleted) {
                return ['message' => 'Media File deleted'];
            } else {
                throw new Exception('Media File delete unsuccessful');
            }

        });
    }

    /**
     * Get mediable data based on input.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    private function getMediableData(array $data): array
    {
        $uploadFolderName = UploadFolderName::tryFrom($data['type']);

        switch ($uploadFolderName) {
            case UploadFolderName::PROFILE_PHOTO:
                return ['id' => $data['user_id'], 'type' => User::class];
            case UploadFolderName::STORE_LOGO:
                return ['id' => $data['store_id'], 'type' => Store::class];
            case UploadFolderName::STORE_PAYMENT_METHOD_LOGO:
                return ['id' => $data['store_payment_method_id'], 'type' => StorePaymentMethod::class];
            case UploadFolderName::STORE_PAYMENT_METHOD_PHOTO:
                return ['id' => $data['store_payment_method_id'], 'type' => StorePaymentMethod::class];
            case UploadFolderName::PRODUCT_PHOTO:
                return ['id' => $data['product_id'], 'type' => Product::class];
            default:
                throw new Exception('Invalid media file type.');
        }
    }

    /**
     * Handle single file types by deleting existing files for STORE_LOGO and PROFILE_PHOTO.
     *
     * @param string $type
     * @param array $mediableData
     * @return void
     * @throws Exception
     */
    private function handleSingleFileTypes(string $type, array $mediableData): void
    {
        if (in_array($type, [
            UploadFolderName::STORE_LOGO->value, UploadFolderName::PROFILE_PHOTO->value,
            UploadFolderName::STORE_PAYMENT_METHOD_LOGO->value, UploadFolderName::STORE_PAYMENT_METHOD_PHOTO->value,
        ])) {
            $existingFile = MediaFile::where([
                'mediable_id' => $mediableData['id'],
                'mediable_type' => $mediableData['type'],
                'type' => $type,
            ])->first();

            if ($existingFile) {
                $this->deleteMediaFile($existingFile);
            }
        }
    }

    /**
     * Check media file limits for types with maximum file counts.
     *
     * @param string $type
     * @param array $mediableData
     * @return void
     * @throws Exception
     */
    private function checkMediaFileLimits(string $type, array $mediableData): void
    {
        $limits = [
            UploadFolderName::PRODUCT_PHOTO->value => [5, 'this product'],
            // Add future types and their limits here, e.g. UploadFolderName::EXAMPLE->value => [10, 'example']
        ];

        if (isset($limits[$type])) {
            $currentCount = MediaFile::where([
                'mediable_id' => $mediableData['id'],
                'mediable_type' => $mediableData['type'],
                'type' => $type,
            ])->count();

            if ($currentCount >= $limits[$type][0]) {
                throw new Exception("You have reached your upload limit for {$limits[$type][1]}.");
            }
        }
    }
}
