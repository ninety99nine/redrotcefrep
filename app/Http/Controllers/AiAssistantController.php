<?php

namespace App\Http\Controllers;

use App\Models\AiAssistant;
use App\Services\AiAssistantService;
use App\Http\Resources\AiAssistantResource;
use App\Http\Resources\AiAssistantResources;
use App\Http\Requests\AiAssistant\ShowAiAssistantRequest;
use App\Http\Requests\AiAssistant\ShowAiAssistantsRequest;
use App\Http\Requests\AiAssistant\CreateAiAssistantRequest;
use App\Http\Requests\AiAssistant\UpdateAiAssistantRequest;
use App\Http\Requests\AiAssistant\DeleteAiAssistantRequest;
use App\Http\Requests\AiAssistant\DeleteAiAssistantsRequest;

class AiAssistantController extends Controller
{
    /**
     * @var AiAssistantService
     */
    protected $service;

    /**
     * AiAssistantController constructor.
     *
     * @param AiAssistantService $service
     */
    public function __construct(AiAssistantService $service)
    {
        $this->service = $service;
    }

    /**
     * Show AI assistants.
     *
     * @param ShowAiAssistantsRequest $request
     * @return AiAssistantResources|array
     */
    public function showAiAssistants(ShowAiAssistantsRequest $request): AiAssistantResources|array
    {
        return $this->service->showAiAssistants($request->validated());
    }

    /**
     * Create AI assistant.
     *
     * @param CreateAiAssistantRequest $request
     * @return array
     */
    public function createAiAssistant(CreateAiAssistantRequest $request): array
    {
        return $this->service->createAiAssistant($request->validated());
    }

    /**
     * Delete multiple AI assistants.
     *
     * @param DeleteAiAssistantsRequest $request
     * @return array
     */
    public function deleteAiAssistants(DeleteAiAssistantsRequest $request): array
    {
        $aiAssistantIds = request()->input('ai_assistant_ids', []);
        return $this->service->deleteAiAssistants($aiAssistantIds);
    }

    /**
     * Show AI assistant.
     *
     * @param ShowAiAssistantRequest $request
     * @param AiAssistant $aiAssistant
     * @return AiAssistantResource
     */
    public function showAiAssistant(ShowAiAssistantRequest $request, AiAssistant $aiAssistant): AiAssistantResource
    {
        return $this->service->showAiAssistant($aiAssistant);
    }

    /**
     * Update AI assistant.
     *
     * @param UpdateAiAssistantRequest $request
     * @param AiAssistant $aiAssistant
     * @return array
     */
    public function updateAiAssistant(UpdateAiAssistantRequest $request, AiAssistant $aiAssistant): array
    {
        return $this->service->updateAiAssistant($aiAssistant, $request->validated());
    }

    /**
     * Delete AI assistant.
     *
     * @param DeleteAiAssistantRequest $request
     * @param AiAssistant $aiAssistant
     * @return array
     */
    public function deleteAiAssistant(DeleteAiAssistantRequest $request, AiAssistant $aiAssistant): array
    {
        return $this->service->deleteAiAssistant($aiAssistant);
    }
}
