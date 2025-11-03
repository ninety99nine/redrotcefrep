<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\AiAssistantTokenUsage;
use App\Http\Resources\AiAssistantTokenUsageResource;
use App\Http\Resources\AiAssistantTokenUsageResources;

class AiAssistantTokenUsageService extends BaseService
{
    /**
     * Show AI assistant token usages.
     *
     * @param array $data
     * @return AiAssistantTokenUsageResources|array
     */
    public function showAiAssistantTokenUsages(array $data): AiAssistantTokenUsageResources|array
    {
        $query = AiAssistantTokenUsage::query()->when(!request()->has('_sort'), fn($query) => $query->latest());
        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create AI assistant token usage.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function createAiAssistantTokenUsage(array $data): array
    {
        $aiAssistantTokenUsage = AiAssistantTokenUsage::create($data);
        return $this->showCreatedResource($aiAssistantTokenUsage);
    }

    /**
     * Delete AI assistant token usages.
     *
     * @param array $aiAssistantTokenUsageIds
     * @return array
     * @throws Exception
     */
    public function deleteAiAssistantTokenUsages(array $aiAssistantTokenUsageIds): array
    {
        $aiAssistantTokenUsages = AiAssistantTokenUsage::whereIn('id', $aiAssistantTokenUsageIds)->get();

        if ($totalAiAssistantTokenUsages = $aiAssistantTokenUsages->count()) {

            foreach ($aiAssistantTokenUsages as $aiAssistantTokenUsage) {

                $this->deleteAiAssistantTokenUsage($aiAssistantTokenUsage);

            }

            return ['message' => $totalAiAssistantTokenUsages . ($totalAiAssistantTokenUsages == 1 ? ' AI Assistant Token Usage' : ' AI Assistant Token Usages') . ' deleted'];
        } else {
            throw new Exception('No AI Assistant Token Usages deleted');
        }
    }

    /**
     * Show AI assistant token usage.
     *
     * @param AiAssistantTokenUsage $aiAssistantTokenUsage
     * @return AiAssistantTokenUsageResource
     */
    public function showAiAssistantTokenUsage(AiAssistantTokenUsage $aiAssistantTokenUsage): AiAssistantTokenUsageResource
    {
        return $this->showResource($aiAssistantTokenUsage);
    }

    /**
     * Update AI assistant token usage.
     *
     * @param AiAssistantTokenUsage $aiAssistantTokenUsage
     * @param array $data
     * @return array
     */
    public function updateAiAssistantTokenUsage(AiAssistantTokenUsage $aiAssistantTokenUsage, array $data): array
    {
        $aiAssistantTokenUsage->update($data);
        return $this->showUpdatedResource($aiAssistantTokenUsage);
    }

    /**
     * Delete AI assistant token usage.
     *
     * @param AiAssistantTokenUsage $aiAssistantTokenUsage
     * @return array
     * @throws Exception
     */
    public function deleteAiAssistantTokenUsage(AiAssistantTokenUsage $aiAssistantTokenUsage): array
    {
        return DB::transaction(function () use ($aiAssistantTokenUsage) {

            $deleted = $aiAssistantTokenUsage->delete();

            return [
                'deleted' => $deleted,
                'message' => $deleted ? 'AI Assistant Token Usage deleted' : 'AI Assistant Token Usage delete unsuccessful'
            ];

        });
    }
}
