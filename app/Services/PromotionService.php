<?php

namespace App\Services;

use Exception;
use App\Models\Promotion;
use App\Http\Resources\PromotionResource;
use App\Http\Resources\PromotionResources;

class PromotionService extends BaseService
{
    /**
     * Show promotions.
     *
     * @param array $data
     * @return PromotionResources|array
     */
    public function showPromotions(array $data): PromotionResources|array
    {
        $query = Promotion::query()->when(!request()->has('_sort'), fn($query) => $query->latest());
        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create promotion.
     *
     * @param array $data
     * @return array
     */
    public function createPromotion(array $data): array
    {
        $promotion = Promotion::create($data);
        return $this->showCreatedResource($promotion);
    }

    /**
     * Show promotion by code.
     *
     * @param string $code
     * @return PromotionResource
     */
    public function showPromotionByCode(string $code): PromotionResource
    {
        $promotion = Promotion::where('id', $code)->orWhere('code', $code)
                    ->with($this->getRequestRelationships())
                    ->withCount($this->getRequestCountableRelationships())
                    ->firstOrFail();

        return $this->showResource($promotion);
    }

    /**
     * Delete Promotions.
     *
     * @param array $promotionIds
     * @return array
     * @throws Exception
     */
    public function deletePromotions(array $promotionIds): array
    {
        $promotions = Promotion::whereIn('id', $promotionIds)->get();

        if ($totalPromotions = $promotions->count()) {

            foreach ($promotions as $promotion) {
                $promotion->delete();
            }

            return ['message' => $totalPromotions . ($totalPromotions == 1 ? ' Promotion' : ' Promotions') . ' deleted'];

        } else {
            throw new Exception('No Promotions deleted');
        }
    }

    /**
     * Show promotion.
     *
     * @param Promotion $promotion
     * @return PromotionResource
     */
    public function showPromotion(Promotion $promotion): PromotionResource
    {
        return $this->showResource($promotion);
    }

    /**
     * Update promotion.
     *
     * @param Promotion $promotion
     * @param array $data
     * @return array
     */
    public function updatePromotion(Promotion $promotion, array $data): array
    {
        $promotion->update($data);
        return $this->showUpdatedResource($promotion);
    }

    /**
     * Delete promotion.
     *
     * @param Promotion $promotion
     * @return array
     * @throws Exception
     */
    public function deletePromotion(Promotion $promotion): array
    {
        $deleted = $promotion->delete();

        if ($deleted) {
            return ['message' => 'Promotion deleted'];
        } else {
            throw new Exception('Promotion delete unsuccessful');
        }
    }
}
