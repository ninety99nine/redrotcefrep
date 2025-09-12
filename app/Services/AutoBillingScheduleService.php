<?php

namespace App\Services;

use Exception;
use App\Enums\Association;
use App\Models\AutoBillingSchedule;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\AutoBillingScheduleResource;
use App\Http\Resources\AutoBillingScheduleResources;
use App\Jobs\AutoBilling\SendAutoBillingDisabledSms;

class AutoBillingScheduleService extends BaseService
{
    /**
     * Show Auto billing schedules.
     *
     * @param array $data
     * @return AutoBillingScheduleResources|array
     */
    public function showAutoBillingSchedules(array $data): AutoBillingScheduleResources|array
    {
        $active = $data['active'] ?? null;
        $userId = $data['user_id'] ?? null;
        $association = isset($data['association']) ? Association::tryFrom($data['association']) : null;

        if($association == Association::SUPER_ADMIN) {
            $query = AutoBillingSchedule::query();
        }else {
            $query = AutoBillingSchedule::where('user_id', $userId ?? Auth::user()->id);
        }

        if(!is_null($active)) $query = $query->where('active', $active);

        $query = $query->when(!request()->has('_sort'), fn($query) => $query->latest());
        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create Auto billing schedule.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function createAutoBillingSchedule(array $data): array
    {
        $autoBillingSchedule = AutoBillingSchedule::create($data);
        return $this->showCreatedResource($autoBillingSchedule);
    }

    /**
     * Delete Auto billing schedules.
     *
     * @param array $autoBillingScheduleIds
     * @return array
     * @throws Exception
     */
    public function deleteAutoBillingSchedules(array $autoBillingScheduleIds): array
    {
        $autoBillingSchedules = AutoBillingSchedule::whereIn('id', $autoBillingScheduleIds)->get();

        if ($totalAutoBillingSchedules = $autoBillingSchedules->count()) {

            foreach ($autoBillingSchedules as $autoBillingSchedule) {

                $this->deleteAutoBillingSchedule($autoBillingSchedule);

            }

            return ['message' => $totalAutoBillingSchedules . ($totalAutoBillingSchedules == 1 ? ' Auto billing schedule' : ' Auto billing schedules') . ' deleted'];
        } else {
            throw new Exception('No Auto billing schedules deleted');
        }
    }

    /**
     * Show Auto billing schedule.
     *
     * @param AutoBillingSchedule $autoBillingSchedule
     * @return AutoBillingScheduleResource
     */
    public function showAutoBillingSchedule(AutoBillingSchedule $autoBillingSchedule): AutoBillingScheduleResource
    {
        return $this->showResource($autoBillingSchedule);
    }

    /**
     * Update Auto billing schedule.
     *
     * @param AutoBillingSchedule $autoBillingSchedule
     * @param array $data
     * @return array
     */
    public function updateAutoBillingSchedule(AutoBillingSchedule $autoBillingSchedule, array $data): array
    {
        if (isset($data['active']) && !$data['active']) {
            $data['attempt'] = 0;
            $data['active'] = false;
            $data['next_attempt_date'] = null;
        }

        $autoBillingSchedule->update($data);

        if (!$autoBillingSchedule->active) {
            SendAutoBillingDisabledSms::dispatch($autoBillingSchedule->id);
        }

        return $this->showUpdatedResource($autoBillingSchedule);
    }

    /**
     * Delete Auto billing schedule.
     *
     * @param AutoBillingSchedule $autoBillingSchedule
     * @return array
     * @throws Exception
     */
    public function deleteAutoBillingSchedule(AutoBillingSchedule $autoBillingSchedule): array
    {
        $deleted = $autoBillingSchedule->delete();

        if ($deleted) {
            return ['message' => 'Auto billing schedule deleted'];
        } else {
            throw new Exception('Auto billing schedule delete unsuccessful');
        }
    }
}
