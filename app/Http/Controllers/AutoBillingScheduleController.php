<?php

namespace App\Http\Controllers;

use App\Models\AutoBillingSchedule;
use App\Services\AutoBillingScheduleService;
use App\Http\Resources\AutoBillingScheduleResource;
use App\Http\Resources\AutoBillingScheduleResources;
use App\Http\Requests\AutoBillingSchedule\ShowAutoBillingScheduleRequest;
use App\Http\Requests\AutoBillingSchedule\ShowAutoBillingSchedulesRequest;
use App\Http\Requests\AutoBillingSchedule\CreateAutoBillingScheduleRequest;
use App\Http\Requests\AutoBillingSchedule\UpdateAutoBillingScheduleRequest;
use App\Http\Requests\AutoBillingSchedule\DeleteAutoBillingScheduleRequest;
use App\Http\Requests\AutoBillingSchedule\DeleteAutoBillingSchedulesRequest;

class AutoBillingScheduleController extends Controller
{
    /**
     * @var AutoBillingScheduleService
     */
    protected $service;

    /**
     * AutoBillingScheduleController constructor.
     *
     * @param AutoBillingScheduleService $service
     */
    public function __construct(AutoBillingScheduleService $service)
    {
        $this->service = $service;
    }

    /**
     * Show Auto billing schedules.
     *
     * @param ShowAutoBillingSchedulesRequest $request
     * @return AutoBillingScheduleResources|array
     */
    public function showAutoBillingSchedules(ShowAutoBillingSchedulesRequest $request): AutoBillingScheduleResources|array
    {
        return $this->service->showAutoBillingSchedules($request->validated());
    }

    /**
     * Create Auto billing schedule.
     *
     * @param CreateAutoBillingScheduleRequest $request
     * @return array
     */
    public function createAutoBillingSchedule(CreateAutoBillingScheduleRequest $request): array
    {
        return $this->service->createAutoBillingSchedule($request->validated());
    }

    /**
     * Delete multiple Auto billing schedules.
     *
     * @param DeleteAutoBillingSchedulesRequest $request
     * @return array
     */
    public function deleteAutoBillingSchedules(DeleteAutoBillingSchedulesRequest $request): array
    {
        $autoBillingScheduleIds = request()->input('auto_billing_schedule_ids', []);
        return $this->service->deleteAutoBillingSchedules($autoBillingScheduleIds);
    }

    /**
     * Show Auto billing schedule.
     *
     * @param ShowAutoBillingScheduleRequest $request
     * @param AutoBillingSchedule $autoBillingSchedule
     * @return AutoBillingScheduleResource
     */
    public function showAutoBillingSchedule(ShowAutoBillingScheduleRequest $request, AutoBillingSchedule $autoBillingSchedule): AutoBillingScheduleResource
    {
        return $this->service->showAutoBillingSchedule($autoBillingSchedule);
    }

    /**
     * Update Auto billing schedule.
     *
     * @param UpdateAutoBillingScheduleRequest $request
     * @param AutoBillingSchedule $autoBillingSchedule
     * @return array
     */
    public function updateAutoBillingSchedule(UpdateAutoBillingScheduleRequest $request, AutoBillingSchedule $autoBillingSchedule): array
    {
        return $this->service->updateAutoBillingSchedule($autoBillingSchedule, $request->validated());
    }

    /**
     * Delete Auto billing schedule.
     *
     * @param DeleteAutoBillingScheduleRequest $request
     * @param AutoBillingSchedule $autoBillingSchedule
     * @return array
     */
    public function deleteAutoBillingSchedule(DeleteAutoBillingScheduleRequest $request, AutoBillingSchedule $autoBillingSchedule): array
    {
        return $this->service->deleteAutoBillingSchedule($autoBillingSchedule);
    }
}
