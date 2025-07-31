<?php

namespace App\Services;

use Exception;
use App\Models\AiAssistant;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\AiAssistantResource;
use App\Http\Resources\AiAssistantResources;

class AiAssistantService extends BaseService
{
    /**
     * Show AI assistants.
     *
     * @param array $data
     * @return AiAssistantResources|array
     */
    public function showAiAssistants(array $data): AiAssistantResources|array
    {
        $query = AiAssistant::query()->when(!request()->has('_sort'), fn($query) => $query->latest());
        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create AI assistant.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function createAiAssistant(array $data): array
    {
        $aiAssistant = AiAssistant::create($data);
        return $this->showCreatedResource($aiAssistant);
    }

    /**
     * Delete AI assistants.
     *
     * @param array $aiAssistantIds
     * @return array
     * @throws Exception
     */
    public function deleteAiAssistants(array $aiAssistantIds): array
    {
        $aiAssistants = AiAssistant::whereIn('id', $aiAssistantIds)->get();

        if ($totalAiAssistants = $aiAssistants->count()) {

            foreach ($aiAssistants as $aiAssistant) {

                $this->deleteAiAssistant($aiAssistant);

            }

            return ['message' => $totalAiAssistants . ($totalAiAssistants == 1 ? ' AI Assistant' : ' AI Assistants') . ' deleted'];
        } else {
            throw new Exception('No AI Assistants deleted');
        }
    }

    /**
     * Show AI assistant.
     *
     * @param AiAssistant $aiAssistant
     * @return AiAssistantResource
     */
    public function showAiAssistant(AiAssistant $aiAssistant): AiAssistantResource
    {
        return $this->showResource($aiAssistant);
    }

    /**
     * Update AI assistant.
     *
     * @param AiAssistant $aiAssistant
     * @param array $data
     * @return array
     */
    public function updateAiAssistant(AiAssistant $aiAssistant, array $data): array
    {
        $aiAssistant->update($data);
        return $this->showUpdatedResource($aiAssistant);
    }

    /**
     * Delete AI assistant.
     *
     * @param AiAssistant $aiAssistant
     * @return array
     * @throws Exception
     */
    public function deleteAiAssistant(AiAssistant $aiAssistant): array
    {
        $deleted = $aiAssistant->delete();

        if ($deleted) {
            return ['message' => 'AI Assistant deleted'];
        } else {
            throw new Exception('AI Assistant delete unsuccessful');
        }
    }
}
