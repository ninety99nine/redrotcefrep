<?php

namespace App\Http\Controllers;

use App\Models\AiMessage;
use App\Services\AiMessageService;
use App\Http\Resources\AiMessageResource;
use App\Http\Resources\AiMessageResources;
use App\Http\Requests\AiMessage\ShowAiMessageRequest;
use App\Http\Requests\AiMessage\ShowAiMessagesRequest;
use App\Http\Requests\AiMessage\CreateAiMessageRequest;
use App\Http\Requests\AiMessage\UpdateAiMessageRequest;
use App\Http\Requests\AiMessage\DeleteAiMessageRequest;
use App\Http\Requests\AiMessage\DeleteAiMessagesRequest;

class AiMessageController extends Controller
{
    /**
     * @var AiMessageService
     */
    protected $service;

    /**
     * AiMessageController constructor.
     *
     * @param AiMessageService $service
     */
    public function __construct(AiMessageService $service)
    {
        $this->service = $service;
    }

    /**
     * Show AI messages.
     *
     * @param ShowAiMessagesRequest $request
     * @return AiMessageResources|array
     */
    public function showAiMessages(ShowAiMessagesRequest $request): AiMessageResources|array
    {
        return $this->service->showAiMessages($request->validated());
    }

    /**
     * Create AI message.
     *
     * @param CreateAiMessageRequest $request
     * @return array
     */
    public function createAiMessage(CreateAiMessageRequest $request): array
    {
        return $this->service->createAiMessage($request->validated());
    }

    /**
     * Delete multiple AI messages.
     *
     * @param DeleteAiMessagesRequest $request
     * @return array
     */
    public function deleteAiMessages(DeleteAiMessagesRequest $request): array
    {
        $aiMessageIds = request()->input('ai_message_ids', []);
        return $this->service->deleteAiMessages($aiMessageIds);
    }

    /**
     * Show AI message.
     *
     * @param ShowAiMessageRequest $request
     * @param AiMessage $aiMessage
     * @return AiMessageResource
     */
    public function showAiMessage(ShowAiMessageRequest $request, AiMessage $aiMessage): AiMessageResource
    {
        return $this->service->showAiMessage($aiMessage);
    }

    /**
     * Update AI message.
     *
     * @param UpdateAiMessageRequest $request
     * @param AiMessage $aiMessage
     * @return array
     */
    public function updateAiMessage(UpdateAiMessageRequest $request, AiMessage $aiMessage): array
    {
        return $this->service->updateAiMessage($aiMessage, $request->validated());
    }

    /**
     * Delete AI message.
     *
     * @param DeleteAiMessageRequest $request
     * @param AiMessage $aiMessage
     * @return array
     */
    public function deleteAiMessage(DeleteAiMessageRequest $request, AiMessage $aiMessage): array
    {
        return $this->service->deleteAiMessage($aiMessage);
    }
}
