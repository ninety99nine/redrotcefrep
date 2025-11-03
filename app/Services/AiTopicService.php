<?php

namespace App\Services;

use Exception;
use App\Models\AiTopic;
use App\Http\Resources\AiTopicResource;
use App\Http\Resources\AiTopicResources;

class AiTopicService extends BaseService
{
    /**
     * Show AI topics.
     *
     * @param array $data
     * @return AiTopicResources|array
     */
    public function showAiTopics(array $data): AiTopicResources|array
    {
        $query = AiTopic::when(!request()->has('_sort'), fn($query) => $query->latest());
        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create AI topic.
     *
     * @param array $data
     * @return array
     */
    public function createAiTopic(array $data): array
    {
        $aiTopic = AiTopic::create($data);
        return $this->showCreatedResource($aiTopic);
    }

    /**
     * Delete AI topics.
     *
     * @param array $aiTopicIds
     * @return array
     * @throws Exception
     */
    public function deleteAiTopics(array $aiTopicIds): array
    {
        $aiTopics = AiTopic::whereIn('id', $aiTopicIds)->get();

        if ($totalAiTopics = $aiTopics->count()) {

            foreach ($aiTopics as $aiTopic) {

                $this->deleteAiTopic($aiTopic);

            }

            return ['message' => $totalAiTopics . ($totalAiTopics == 1 ? ' AI topic' : ' AI topics') . ' deleted'];

        } else {
            throw new Exception('No AI topics deleted');
        }
    }

    /**
     * Show AI topic.
     *
     * @param AiTopic $aiTopic
     * @return AiTopicResource
     */
    public function showAiTopic(AiTopic $aiTopic): AiTopicResource
    {
        return $this->showResource($aiTopic);
    }

    /**
     * Update AI topic.
     *
     * @param AiTopic $aiTopic
     * @param array $data
     * @return array
     */
    public function updateAiTopic(AiTopic $aiTopic, array $data): array
    {
        $aiTopic->update($data);
        return $this->showUpdatedResource($aiTopic);
    }

    /**
     * Delete AI topic.
     *
     * @param AiTopic $aiTopic
     * @return array
     * @throws Exception
     */
    public function deleteAiTopic(AiTopic $aiTopic): array
    {
        $deleted = $aiTopic->delete();

        return [
            'deleted' => $deleted,
            'message' => $deleted ? 'AI topic deleted' : 'AI topic delete unsuccessful'
        ];
    }
}
