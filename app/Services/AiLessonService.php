<?php

namespace App\Services;

use Exception;
use App\Models\AiLesson;
use App\Http\Resources\AiLessonResource;
use App\Http\Resources\AiLessonResources;

class AiLessonService extends BaseService
{
    /**
     * Show AI lessons.
     *
     * @param array $data
     * @return AiLessonResources|array
     */
    public function showAiLessons(array $data): AiLessonResources|array
    {
        $aiLessonId = $data['ai_lesson_id'] ?? null;

        $query = AiLesson::query();
        if($aiLessonId) $query = $query->where('ai_lesson_id', $aiLessonId);

        $query = $query->when(!request()->has('_sort'), fn($query) => $query->latest());
        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create AI lesson.
     *
     * @param array $data
     * @return array
     */
    public function createAiLesson(array $data): array
    {
        $aiLesson = AiLesson::create($data);
        return $this->showCreatedResource($aiLesson);
    }

    /**
     * Delete AI lessons.
     *
     * @param array $aiLessonIds
     * @return array
     * @throws Exception
     */
    public function deleteAiLessons(array $aiLessonIds): array
    {
        $aiLessons = AiLesson::whereIn('id', $aiLessonIds)->get();

        if ($totalAiLessons = $aiLessons->count()) {

            foreach ($aiLessons as $aiLesson) {

                $this->deleteAiLesson($aiLesson);

            }

            return ['message' => $totalAiLessons . ($totalAiLessons == 1 ? ' AI lesson' : ' AI lessons') . ' deleted'];

        } else {
            throw new Exception('No AI lessons deleted');
        }
    }

    /**
     * Show AI lesson.
     *
     * @param AiLesson $aiLesson
     * @return AiLessonResource
     */
    public function showAiLesson(AiLesson $aiLesson): AiLessonResource
    {
        return $this->showResource($aiLesson);
    }

    /**
     * Update AI lesson.
     *
     * @param AiLesson $aiLesson
     * @param array $data
     * @return array
     */
    public function updateAiLesson(AiLesson $aiLesson, array $data): array
    {
        $aiLesson->update($data);
        return $this->showUpdatedResource($aiLesson);
    }

    /**
     * Delete AI lesson.
     *
     * @param AiLesson $aiLesson
     * @return array
     * @throws Exception
     */
    public function deleteAiLesson(AiLesson $aiLesson): array
    {
        $deleted = $aiLesson->delete();

        if ($deleted) {
            return ['message' => 'AI lesson deleted'];
        } else {
            throw new Exception('AI lesson delete unsuccessful');
        }
    }
}
