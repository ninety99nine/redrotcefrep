<?php

namespace App\Http\Controllers;

use App\Models\DesignCard;
use App\Services\DesignCardService;
use App\Http\Resources\DesignCardResource;
use App\Http\Resources\DesignCardResources;
use App\Http\Requests\DesignCard\ShowDesignCardRequest;
use App\Http\Requests\DesignCard\ShowDesignCardsRequest;
use App\Http\Requests\DesignCard\DeleteDesignCardRequest;
use App\Http\Requests\DesignCard\CreateDesignCardRequest;
use App\Http\Requests\DesignCard\UpdateDesignCardRequest;
use App\Http\Requests\DesignCard\DeleteDesignCardsRequest;
use App\Http\Requests\DesignCard\ShowDesignCardConfigurationsRequest;
use App\Http\Requests\DesignCard\UpdateDesignCardArrangementRequest;

class DesignCardController extends Controller
{
    /**
     * @var DesignCardService
     */
    protected $service;

    /**
     * DesignCardController constructor.
     *
     * @param DesignCardService $service
     */
    public function __construct(DesignCardService $service)
    {
        $this->service = $service;
    }

    /**
     * Show design cards.
     *
     * @param ShowDesignCardsRequest $request
     * @return DesignCardResources|array
     */
    public function showDesignCards(ShowDesignCardsRequest $request): DesignCardResources|array
    {
        return $this->service->showDesignCards($request->validated());
    }

    /**
     * Create design card.
     *
     * @param CreateDesignCardRequest $request
     * @return array
     */
    public function createDesignCard(CreateDesignCardRequest $request): array
    {
        return $this->service->createDesignCard($request->validated());
    }

    /**
     * Delete multiple design cards.
     *
     * @param DeleteDesignCardsRequest $request
     * @return array
     */
    public function deleteDesignCards(DeleteDesignCardsRequest $request): array
    {
        $designCardIds = request()->input('design_card_ids', []);
        return $this->service->deleteDesignCards($designCardIds);
    }

    /**
     * Show design card configurations.
     *
     * @param ShowDesignCardConfigurationsRequest $request
     * @return array
     */
    public function showDesignCardConfigurations(ShowDesignCardConfigurationsRequest $request): array
    {
        return $this->service->showDesignCardConfigurations($request->validated());
    }

    /**
     * Update design card arrangement.
     *
     * @param UpdateDesignCardArrangementRequest $request
     * @return array
     */
    public function updateDesignCardArrangement(UpdateDesignCardArrangementRequest $request): array
    {
        return $this->service->updateDesignCardArrangement($request->validated());
    }

    /**
     * Show design card.
     *
     * @param ShowDesignCardRequest $request
     * @param DesignCard $designCard
     * @return DesignCardResource
     */
    public function showDesignCard(ShowDesignCardRequest $request, DesignCard $designCard): DesignCardResource
    {
        return $this->service->showDesignCard($designCard);
    }

    /**
     * Update design card.
     *
     * @param UpdateDesignCardRequest $request
     * @param DesignCard $designCard
     * @return array
     */
    public function updateDesignCard(UpdateDesignCardRequest $request, DesignCard $designCard): array
    {
        return $this->service->updateDesignCard($designCard, $request->validated());
    }

    /**
     * Delete design card.
     *
     * @param DeleteDesignCardRequest $request
     * @param DesignCard $designCard
     * @return array
     */
    public function deleteDesignCard(DeleteDesignCardRequest $request, DesignCard $designCard): array
    {
        return $this->service->deleteDesignCard($designCard);
    }
}
