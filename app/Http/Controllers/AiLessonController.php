<?php

namespace App\Http\Controllers;

use App\Models\AiLesson;
use App\Services\AiLessonService;
use App\Http\Resources\AiLessonResource;
use App\Http\Resources\AiLessonResources;
use App\Http\Requests\AiLesson\ShowAiLessonRequest;
use App\Http\Requests\AiLesson\ShowAiLessonsRequest;
use App\Http\Requests\AiLesson\CreateAiLessonRequest;
use App\Http\Requests\AiLesson\UpdateAiLessonRequest;
use App\Http\Requests\AiLesson\DeleteAiLessonRequest;
use App\Http\Requests\AiLesson\DeleteAiLessonsRequest;

class AiLessonController extends Controller
{
    /**
     * @var AiLessonService
     */
    protected $service;

    /**
     * AiLessonController constructor.
     *
     * @param AiLessonService $service
     */
    public function __construct(AiLessonService $service)
    {
        $this->service = $service;
    }

    /**
     * Show AI lessons.
     *
     * @param ShowAiLessonsRequest $request
     * @return AiLessonResources|array
     */
    public function showAiLessons(ShowAiLessonsRequest $request): AiLessonResources|array
    {
        return $this->service->showAiLessons($request->validated());
    }

    /**
     * Create AI lesson.
     *
     * @param CreateAiLessonRequest $request
     * @return array
     */
    public function createAiLesson(CreateAiLessonRequest $request): array
    {
        return $this->service->createAiLesson($request->validated());
    }

    /**
     * Delete multiple AI lessons.
     *
     * @param DeleteAiLessonsRequest $request
     * @return array
     */
    public function deleteAiLessons(DeleteAiLessonsRequest $request): array
    {
        $aiLessonIds = request()->input('pricing_plan_ids', []);
        return $this->service->deleteAiLessons($aiLessonIds);
    }

    /**
     * Show AI lesson.
     *
     * @param ShowAiLessonRequest $request
     * @param AiLesson $aiLesson
     * @return AiLessonResource
     */
    public function showAiLesson(ShowAiLessonRequest $request, AiLesson $aiLesson): AiLessonResource
    {
        return $this->service->showAiLesson($aiLesson);
    }

    /**
     * Update AI lesson.
     *
     * @param UpdateAiLessonRequest $request
     * @param AiLesson $aiLesson
     * @return array
     */
    public function updateAiLesson(UpdateAiLessonRequest $request, AiLesson $aiLesson): array
    {
        return $this->service->updateAiLesson($aiLesson, $request->validated());
    }

    /**
     * Delete AI lesson.
     *
     * @param DeleteAiLessonRequest $request
     * @param AiLesson $aiLesson
     * @return array
     */
    public function deleteAiLesson(DeleteAiLessonRequest $request, AiLesson $aiLesson): array
    {
        return $this->service->deleteAiLesson($aiLesson);
    }
}
