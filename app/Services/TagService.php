<?php

namespace App\Services;

use Exception;
use App\Models\Tag;
use App\Enums\Association;
use App\Http\Resources\TagResource;
use App\Http\Resources\TagResources;
use \Symfony\Component\HttpFoundation\BinaryFileResponse;

class TagService extends BaseService
{
    /**
     * Show tags.
     *
     * @param array $data
     * @return TagResources|BinaryFileResponse|array
     */
    public function showTags(array $data): TagResources|BinaryFileResponse|array
    {
        $storeId = $data['store_id'] ?? null;
        $association = isset($data['association']) ? Association::tryFrom($data['association']) : null;

        if($association == Association::SUPER_ADMIN) {
            $query = Tag::query();
        }else {
            $query = Tag::where('store_id', $storeId);
        }

        $query = $query->when(!request()->has('_sort'), fn($query) => $query->latest());
        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create tag.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function createTag(array $data): array
    {
        $productIds = $data['product_ids'] ?? null;

        $tag = Tag::create($data);

        if(!is_null($productIds)) {
            $tag->products()->sync($productIds);
        }

        return $this->showCreatedResource($tag);
    }

    /**
     * Delete Tags.
     *
     * @param array $tagIds
     * @return array
     * @throws Exception
     */
    public function deleteTags(array $tagIds): array
    {
        $tags = Tag::whereIn('id', $tagIds)->get();

        if ($totalTags = $tags->count()) {

            foreach ($tags as $tag) {

                $this->deleteTag($tag);

            }

            return ['message' => $totalTags . ($totalTags == 1 ? ' Tag' : ' Tags') . ' deleted'];

        } else {
            throw new Exception('No Tags deleted');
        }
    }

    /**
     * Show tag.
     *
     * @param Tag $tag
     * @return TagResource
     */
    public function showTag(Tag $tag): TagResource
    {
        return $this->showResource($tag);
    }

    /**
     * Update tag.
     *
     * @param Tag $tag
     * @param array $data
     * @return array
     */
    public function updateTag(Tag $tag, array $data): array
    {
        $productIds = $data['product_ids'] ?? null;

        $tag->update($data);

        if(!is_null($productIds)) {
            $tag->products()->sync($productIds);
        }

        return $this->showUpdatedResource($tag);
    }

    /**
     * Delete tag.
     *
     * @param Tag $tag
     * @return array
     * @throws Exception
     */
    public function deleteTag(Tag $tag): array
    {
        $deleted = $tag->delete();

        if ($deleted) {
            return ['message' => 'Tag deleted'];
        } else {
            throw new Exception('Tag delete unsuccessful');
        }
    }
}
