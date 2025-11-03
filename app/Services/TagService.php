<?php

namespace App\Services;

use Exception;
use App\Models\Tag;
use App\Enums\TagType;
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
        $customerIds = $data['customer_ids'] ?? null;

        if(!is_null($productIds)) {
            $data['type'] = TagType::PRODUCT->value;
        }else if(!is_null($customerIds)) {
            $data['type'] = TagType::CUSTOMER->value;
        }

        $tag = Tag::create($data);

        if(!is_null($productIds)) {
            $tag->products()->sync($productIds);
        }else if(!is_null($customerIds)) {
            $tag->customers()->sync($customerIds);
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
        $productIdsToAdd = $data['product_ids_to_add'] ?? null;
        $productIdsToRemove = $data['product_ids_to_remove'] ?? null;

        $customerIdsToAdd = $data['customer_ids_to_add'] ?? null;
        $customerIdsToRemove = $data['customer_ids_to_remove'] ?? null;

        $tag->update($data);

        if ($tag->type == TagType::PRODUCT->value) {

            if (!is_null($productIdsToAdd) && !empty($productIdsToAdd)) {

                $now = now();
                $pivotData = [];
                $productIdsToAdd = array_reverse($productIdsToAdd); // Reverse the order

                foreach ($productIdsToAdd as $index => $productId) {

                    $date = clone($now)->addSeconds($index);

                    $pivotData[$productId] = [
                        'created_at' => $date,
                        'updated_at' => $date
                    ];
                }

                $tag->products()->syncWithoutDetaching($pivotData);

            }

            if (!is_null($productIdsToRemove) && !empty($productIdsToRemove)) {

                $tag->products()->detach($productIdsToRemove);

            }

        }

        if ($tag->type == TagType::CUSTOMER->value) {

            if (!is_null($customerIdsToAdd) && !empty($customerIdsToAdd)) {

                $now = now();
                $pivotData = [];
                $customerIdsToAdd = array_reverse($customerIdsToAdd); // Reverse the order

                foreach ($customerIdsToAdd as $index => $customerId) {

                    $date = clone($now)->addSeconds($index);

                    $pivotData[$customerId] = [
                        'created_at' => $date,
                        'updated_at' => $date
                    ];

                }

                $tag->customers()->syncWithoutDetaching($pivotData);

            }

            if (!is_null($customerIdsToRemove) && !empty($customerIdsToRemove)) {

                $tag->customers()->detach($customerIdsToRemove);

            }

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

        return [
            'deleted' => $deleted,
            'message' => $deleted ? 'Tag deleted' : 'Tag delete unsuccessful'
        ];
    }
}
