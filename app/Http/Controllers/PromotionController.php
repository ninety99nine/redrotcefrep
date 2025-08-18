<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Services\PromotionService;
use App\Http\Resources\PromotionResource;
use App\Http\Resources\PromotionResources;
use App\Http\Requests\Promotion\ShowPromotionRequest;
use App\Http\Requests\Promotion\ShowPromotionsRequest;
use App\Http\Requests\Promotion\CreatePromotionRequest;
use App\Http\Requests\Promotion\UpdatePromotionRequest;
use App\Http\Requests\Promotion\DeletePromotionRequest;
use App\Http\Requests\Promotion\DeletePromotionsRequest;

class PromotionController extends Controller
{
    /**
     * @var PromotionService
     */
    protected $service;

    /**
     * PromotionController constructor.
     *
     * @param PromotionService $service
     */
    public function __construct(PromotionService $service)
    {
        $this->service = $service;
    }

    /**
     * Show promotions.
     *
     * @param ShowPromotionsRequest $request
     * @return PromotionResources|array
     */
    public function showPromotions(ShowPromotionsRequest $request): PromotionResources|array
    {
        return $this->service->showPromotions($request->validated());
    }

    /**
     * Create promotion.
     *
     * @param CreatePromotionRequest $request
     * @return array
     */
    public function createPromotion(CreatePromotionRequest $request): array
    {
        return $this->service->createPromotion($request->validated());
    }

    /**
     * Delete multiple promotions.
     *
     * @param DeletePromotionsRequest $request
     * @return array
     */
    public function deletePromotions(DeletePromotionsRequest $request): array
    {
        $promotionIds = request()->input('promotion_ids', []);
        return $this->service->deletePromotions($promotionIds);
    }

    /**
     * Show promotion.
     *
     * @param ShowPromotionRequest $request
     * @param Promotion $promotion
     * @return PromotionResource
     */
    public function showPromotion(ShowPromotionRequest $request, Promotion $promotion): PromotionResource
    {
        return $this->service->showPromotion($promotion);
    }

    /**
     * Update promotion.
     *
     * @param UpdatePromotionRequest $request
     * @param Promotion $promotion
     * @return array
     */
    public function updatePromotion(UpdatePromotionRequest $request, Promotion $promotion): array
    {
        return $this->service->updatePromotion($promotion, $request->validated());
    }

    /**
     * Delete promotion.
     *
     * @param DeletePromotionRequest $request
     * @param Promotion $promotion
     * @return array
     */
    public function deletePromotion(DeletePromotionRequest $request, Promotion $promotion): array
    {
        return $this->service->deletePromotion($promotion);
    }
}
