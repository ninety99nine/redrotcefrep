<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Services\TagService;
use App\Http\Resources\TagResource;
use App\Http\Resources\TagResources;
use App\Http\Requests\Tag\ShowTagRequest;
use App\Http\Requests\Tag\ShowTagsRequest;
use App\Http\Requests\Tag\CreateTagRequest;
use App\Http\Requests\Tag\UpdateTagRequest;
use App\Http\Requests\Tag\DeleteTagRequest;
use App\Http\Requests\Tag\DeleteTagsRequest;
use \Symfony\Component\HttpFoundation\BinaryFileResponse;

class TagController extends Controller
{
    /**
     * @var TagService
     */
    protected $service;

    /**
     * TagController constructor.
     *
     * @param TagService $service
     */
    public function __construct(TagService $service)
    {
        $this->service = $service;
    }

    /**
     * Show tags.
     *
     * @param ShowTagsRequest $request
     * @return TagResources|BinaryFileResponse|array
     */
    public function showTags(ShowTagsRequest $request): TagResources|BinaryFileResponse|array
    {
        return $this->service->showTags($request->validated());
    }

    /**
     * Create tag.
     *
     * @param CreateTagRequest $request
     * @return array
     */
    public function createTag(CreateTagRequest $request): array
    {
        return $this->service->createTag($request->validated());
    }

    /**
     * Delete multiple tags.
     *
     * @param DeleteTagsRequest $request
     * @return array
     */
    public function deleteTags(DeleteTagsRequest $request): array
    {
        $tagIds = request()->input('tag_ids', []);
        return $this->service->deleteTags($tagIds);
    }

    /**
     * Show tag.
     *
     * @param ShowTagRequest $request
     * @param Tag $tag
     * @return TagResource
     */
    public function showTag(ShowTagRequest $request, Tag $tag): TagResource
    {
        return $this->service->showTag($tag);
    }

    /**
     * Update tag.
     *
     * @param UpdateTagRequest $request
     * @param Tag $tag
     * @return array
     */
    public function updateTag(UpdateTagRequest $request, Tag $tag): array
    {
        return $this->service->updateTag($tag, $request->validated());
    }

    /**
     * Delete tag.
     *
     * @param DeleteTagRequest $request
     * @param Tag $tag
     * @return array
     */
    public function deleteTag(DeleteTagRequest $request, Tag $tag): array
    {
        return $this->service->deleteTag($tag);
    }
}
