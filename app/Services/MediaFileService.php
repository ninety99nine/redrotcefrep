<?php

namespace App\Services;

use Exception;
use App\Models\MediaFile;
use Illuminate\Http\Response;
use App\Enums\UploadFolderName;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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
            $mediableId = $data['mediable_id'];
            $mediableType = $data['mediable_type'];
            $uploadFolderName = UploadFolderName::from($data['upload_folder_name']);

            $this->handleSingleFileTypes($mediableId, $mediableType, $uploadFolderName);
            $this->checkMediaFileLimits($mediableId, $mediableType, $uploadFolderName);

            $filePath = AWSService::store($uploadFolderName, $file);

            $mediaFile = MediaFile::create([
                'path' => $filePath,
                'mediable_id' => $mediableId,
                'size' => $file->getSize(),
                'mediable_type' => $mediableType,
                'type' => $uploadFolderName->value,
                'mime_type' => $file->getMimeType(),
                'name' => $file->getClientOriginalName(),
                'width' => getimagesize($file)[0] ?? null,
                'height' => getimagesize($file)[1] ?? null,
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
                AWSService::delete($mediaFile->path);

                // Upload new file
                $file = $data['file'];
                $uploadFolderName = UploadFolderName::from($mediaFile->type);
                $filePath = AWSService::store($uploadFolderName, $file);

                $updateData = [
                    'path' => $filePath,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'name' => $file->getClientOriginalName(),
                    'width' => getimagesize($file)[0] ?? null,
                    'height' => getimagesize($file)[1] ?? null,
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

            AWSService::delete($mediaFile->path);
            $deleted = $mediaFile->delete();

            if ($deleted) {
                return ['message' => 'Media File deleted'];
            } else {
                throw new Exception('Media File delete unsuccessful');
            }

        });
    }

    /**
     * Download media file.
     *
     * @param MediaFile $mediaFile
     * @return Response
     */
    public function downloadMediaFile(MediaFile $mediaFile): Response
    {
        $response = Http::get($mediaFile->path);

        return response($response->body(), 200)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'inline; filename="' . $mediaFile->name . '"');
    }

    /**
     * Handle single file types by deleting existing files for STORE_LOGO and PROFILE_PHOTO.
     *
     * @param string $mediableId
     * @param string $mediableType
     * @param UploadFolderName $uploadFolderName
     * @return void
     * @throws Exception
     * $mediableId, $mediableType
     */
    private function handleSingleFileTypes(string $mediableId, string $mediableType, UploadFolderName $uploadFolderName): void
    {
        if (in_array($uploadFolderName->value, [
            UploadFolderName::STORE_LOGO->value,
            UploadFolderName::PROFILE_PHOTO->value,
            UploadFolderName::CATEGORY_PHOTO->value,
            UploadFolderName::DESIGN_CARD_PHOTO->value,
            UploadFolderName::TRANSACTION_PHOTO->value,
            UploadFolderName::STORE_PAYMENT_METHOD_LOGO->value,
            UploadFolderName::STORE_PAYMENT_METHOD_PHOTO->value,
        ])) {
            $existingFile = MediaFile::where([
                'mediable_id' => $mediableId,
                'mediable_type' => $mediableType,
                'type' => $uploadFolderName->value,
            ])->first();

            if ($existingFile) {
                $this->deleteMediaFile($existingFile);
            }
        }
    }

    /**
     * Check media file limits for types with maximum file counts.
     *
     * @param string $mediableId
     * @param string $mediableType
     * @param UploadFolderName $uploadFolderName
     * @return void
     * @throws Exception
     */
    private function checkMediaFileLimits(string $mediableId, string $mediableType, UploadFolderName $uploadFolderName): void
    {
        $limits = [
            UploadFolderName::PRODUCT_PHOTO->value => [5, 'this product'],
            UploadFolderName::ORDER_COMMENT_PHOTO->value => [6, 'this order comment'],
            // Add future types and their limits here, e.g. UploadFolderName::EXAMPLE->value => [10, 'example']
        ];

        if (isset($limits[$uploadFolderName->value])) {
            $currentCount = MediaFile::where([
                'mediable_id' => $mediableId,
                'mediable_type' => $mediableType,
                'type' => $uploadFolderName->value,
            ])->count();

            if ($currentCount >= $limits[$uploadFolderName->value][0]) {
                throw new Exception("You have reached your upload limit for {$limits[$uploadFolderName->value][1]}.");
            }
        }
    }
}
