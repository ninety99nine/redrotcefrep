<?php

namespace App\Http\Controllers;

use App\Models\AiTopic;
use App\Services\AiTopicService;
use App\Http\Resources\AiTopicResource;
use App\Http\Resources\AiTopicResources;
use App\Http\Requests\AiTopic\ShowAiTopicRequest;
use App\Http\Requests\AiTopic\ShowAiTopicsRequest;
use App\Http\Requests\AiTopic\CreateAiTopicRequest;
use App\Http\Requests\AiTopic\UpdateAiTopicRequest;
use App\Http\Requests\AiTopic\DeleteAiTopicRequest;
use App\Http\Requests\AiTopic\DeleteAiTopicsRequest;

class AiTopicController extends Controller
{
    /**
     * @var AiTopicService
     */
    protected $service;

    /**
     * AiTopicController constructor.
     *
     * @param AiTopicService $service
     */
    public function __construct(AiTopicService $service)
    {
        $this->service = $service;
    }

    /**
     * Show AI topics.
     *
     * @param ShowAiTopicsRequest $request
     * @return AiTopicResources|array
     */
    public function showAiTopics(ShowAiTopicsRequest $request): AiTopicResources|array
    {
        return $this->service->showAiTopics($request->validated());
    }

    /**
     * Create AI topic.
     *
     * @param CreateAiTopicRequest $request
     * @return array
     */
    public function createAiTopic(CreateAiTopicRequest $request): array
    {
        return $this->service->createAiTopic($request->validated());
    }

    /**
     * Delete multiple AI topics.
     *
     * @param DeleteAiTopicsRequest $request
     * @return array
     */
    public function deleteAiTopics(DeleteAiTopicsRequest $request): array
    {
        $aiTopicIds = request()->input('pricing_plan_ids', []);
        return $this->service->deleteAiTopics($aiTopicIds);
    }

    /**
     * Show AI topic.
     *
     * @param ShowAiTopicRequest $request
     * @param AiTopic $aiTopic
     * @return AiTopicResource
     */
    public function showAiTopic(ShowAiTopicRequest $request, AiTopic $aiTopic): AiTopicResource
    {
        return $this->service->showAiTopic($aiTopic);
    }

    /**
     * Update AI topic.
     *
     * @param UpdateAiTopicRequest $request
     * @param AiTopic $aiTopic
     * @return array
     */
    public function updateAiTopic(UpdateAiTopicRequest $request, AiTopic $aiTopic): array
    {
        return $this->service->updateAiTopic($aiTopic, $request->validated());
    }

    /**
     * Delete AI topic.
     *
     * @param DeleteAiTopicRequest $request
     * @param AiTopic $aiTopic
     * @return array
     */
    public function deleteAiTopic(DeleteAiTopicRequest $request, AiTopic $aiTopic): array
    {
        return $this->service->deleteAiTopic($aiTopic);
    }
}
