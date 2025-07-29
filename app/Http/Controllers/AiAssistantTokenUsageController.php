<?php

namespace App\Http\Controllers;

use App\Models\AiAssistantTokenUsage;
use App\Services\AiAssistantTokenUsageService;
use App\Http\Resources\AiAssistantTokenUsageResource;
use App\Http\Resources\AiAssistantTokenUsageResources;
use App\Http\Requests\AiAssistantTokenUsage\ShowAiAssistantTokenUsageRequest;
use App\Http\Requests\AiAssistantTokenUsage\ShowAiAssistantTokenUsagesRequest;
use App\Http\Requests\AiAssistantTokenUsage\CreateAiAssistantTokenUsageRequest;
use App\Http\Requests\AiAssistantTokenUsage\UpdateAiAssistantTokenUsageRequest;
use App\Http\Requests\AiAssistantTokenUsage\DeleteAiAssistantTokenUsageRequest;
use App\Http\Requests\AiAssistantTokenUsage\DeleteAiAssistantTokenUsagesRequest;

class AiAssistantTokenUsageController extends Controller
{
    /**
     * @var AiAssistantTokenUsageService
     */
    protected $service;

    /**
     * AiAssistantTokenUsageController constructor.
     *
     * @param AiAssistantTokenUsageService $service
     */
    public function __construct(AiAssistantTokenUsageService $service)
    {
        $this->service = $service;
    }

    /**
     * Show AI assistant token usages.
     *
     * @param ShowAiAssistantTokenUsagesRequest $request
     * @return AiAssistantTokenUsageResources|array
     */
    public function showAiAssistantTokenUsages(ShowAiAssistantTokenUsagesRequest $request): AiAssistantTokenUsageResources|array
    {
        return $this->service->showAiAssistantTokenUsages($request->validated());
    }

    /**
     * Create AI assistant token usage.
     *
     * @param CreateAiAssistantTokenUsageRequest $request
     * @return array
     */
    public function createAiAssistantTokenUsage(CreateAiAssistantTokenUsageRequest $request): array
    {
        return $this->service->createAiAssistantTokenUsage($request->validated());
    }

    /**
     * Delete multiple AI assistant token usages.
     *
     * @param DeleteAiAssistantTokenUsagesRequest $request
     * @return array
     */
    public function deleteAiAssistantTokenUsages(DeleteAiAssistantTokenUsagesRequest $request): array
    {
        $aiAssistantTokenUsageIds = request()->input('ai_assistant_token_usage_ids', []);
        return $this->service->deleteAiAssistantTokenUsages($aiAssistantTokenUsageIds);
    }

    /**
     * Show AI assistant token usage.
     *
     * @param ShowAiAssistantTokenUsageRequest $request
     * @param AiAssistantTokenUsage $aiAssistantTokenUsage
     * @return AiAssistantTokenUsageResource
     */
    public function showAiAssistantTokenUsage(ShowAiAssistantTokenUsageRequest $request, AiAssistantTokenUsage $aiAssistantTokenUsage): AiAssistantTokenUsageResource
    {
        return $this->service->showAiAssistantTokenUsage($aiAssistantTokenUsage);
    }

    /**
     * Update AI assistant token usage.
     *
     * @param UpdateAiAssistantTokenUsageRequest $request
     * @param AiAssistantTokenUsage $aiAssistantTokenUsage
     * @return array
     */
    public function updateAiAssistantTokenUsage(UpdateAiAssistantTokenUsageRequest $request, AiAssistantTokenUsage $aiAssistantTokenUsage): array
    {
        return $this->service->updateAiAssistantTokenUsage($aiAssistantTokenUsage, $request->validated());
    }

    /**
     * Delete AI assistant token usage.
     *
     * @param DeleteAiAssistantTokenUsageRequest $request
     * @param AiAssistantTokenUsage $aiAssistantTokenUsage
     * @return array
     */
    public function deleteAiAssistantTokenUsage(DeleteAiAssistantTokenUsageRequest $request, AiAssistantTokenUsage $aiAssistantTokenUsage): array
    {
        return $this->service->deleteAiAssistantTokenUsage($aiAssistantTokenUsage);
    }
}
