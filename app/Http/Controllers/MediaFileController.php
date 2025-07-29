<?php

namespace App\Http\Controllers;

use App\Models\MediaFile;
use Illuminate\Http\Response;
use App\Services\MediaFileService;
use App\Http\Resources\MediaFileResource;
use App\Http\Resources\MediaFileResources;
use App\Http\Requests\MediaFile\ShowMediaFileRequest;
use App\Http\Requests\MediaFile\ShowMediaFilesRequest;
use App\Http\Requests\MediaFile\CreateMediaFileRequest;
use App\Http\Requests\MediaFile\UpdateMediaFileRequest;
use App\Http\Requests\MediaFile\DeleteMediaFileRequest;
use App\Http\Requests\MediaFile\DeleteMediaFilesRequest;
use App\Http\Requests\MediaFile\DownloadMediaFileRequest;

class MediaFileController extends Controller
{
    /**
     * @var MediaFileService
     */
    protected $service;

    /**
     * MediaFileController constructor.
     *
     * @param MediaFileService $service
     */
    public function __construct(MediaFileService $service)
    {
        $this->service = $service;
    }

    /**
     * Show media files.
     *
     * @param ShowMediaFilesRequest $request
     * @return MediaFileResources|array
     */
    public function showMediaFiles(ShowMediaFilesRequest $request): MediaFileResources|array
    {
        return $this->service->showMediaFiles($request->validated());
    }

    /**
     * Create media file.
     *
     * @param CreateMediaFileRequest $request
     * @return array
     */
    public function createMediaFile(CreateMediaFileRequest $request): array
    {
        return $this->service->createMediaFile($request->validated());
    }

    /**
     * Delete multiple media files.
     *
     * @param DeleteMediaFilesRequest $request
     * @return array
     */
    public function deleteMediaFiles(DeleteMediaFilesRequest $request): array
    {
        $promotionIds = request()->input('promotion_ids', []);
        return $this->service->deleteMediaFiles($promotionIds);
    }

    /**
     * Show media file.
     *
     * @param ShowMediaFileRequest $request
     * @param MediaFile $mediaFile
     * @return MediaFileResource
     */
    public function showMediaFile(ShowMediaFileRequest $request, MediaFile $mediaFile): MediaFileResource
    {
        return $this->service->showMediaFile($mediaFile);
    }

    /**
     * Update media file.
     *
     * @param UpdateMediaFileRequest $request
     * @param MediaFile $mediaFile
     * @return array
     */
    public function updateMediaFile(UpdateMediaFileRequest $request, MediaFile $mediaFile): array
    {
        return $this->service->updateMediaFile($mediaFile, $request->validated());
    }

    /**
     * Delete media file.
     *
     * @param DeleteMediaFileRequest $request
     * @param MediaFile $mediaFile
     * @return array
     */
    public function deleteMediaFile(DeleteMediaFileRequest $request, MediaFile $mediaFile): array
    {
        return $this->service->deleteMediaFile($mediaFile);
    }

    /**
     * Download media file.
     *
     * @param DownloadMediaFileRequest $request
     * @param MediaFile $mediaFile
     * @return Response
     */
    public function downloadMediaFile(MediaFile $mediaFile): Response
    {
        return $this->service->downloadMediaFile($mediaFile);
    }
}
