<?php

namespace App\Services;

use Exception;
use App\Models\Store;
use App\Models\Workflow;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\WorkflowResource;
use App\Http\Resources\WorkflowResources;

class WorkflowService extends BaseService
{
    /**
     * Show workflows.
     *
     * @param array $data
     * @return WorkflowResources|array
     */
    public function showWorkflows(array $data): WorkflowResources|array
    {
        $storeId = $data['store_id'] ?? null;

        $query = Workflow::query();

        if ($storeId) {
            $query = $query->where('store_id', $storeId);
        }

        $query = $query->when(!request()->has('_sort'), fn($query) => $query->orderBy('position'));
        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create workflow.
     *
     * @param array $data
     * @return array
     */
    public function createWorkflow(array $data): array
    {
        $storeId = $data['store_id'];
        $workflow = Workflow::create($data);

        $this->updateWorkflowArrangement([
            'store_id' => $storeId,
            'workflow_ids' => [$workflow->id]
        ]);

        return $this->showCreatedResource($workflow);
    }

    /**
     * Delete workflows.
     *
     * @param array $workflowIds
     * @return array
     * @throws Exception
     */
    public function deleteWorkflows(array $workflowIds): array
    {
        $workflows = Workflow::whereIn('id', $workflowIds)->get();

        if ($totalWorkflows = $workflows->count()) {

            foreach ($workflows as $workflow) {

                $this->deleteWorkflow($workflow);

            }

            return ['message' => $totalWorkflows . ($totalWorkflows == 1 ? ' Workflow' : ' Workflows') . ' deleted'];
        } else {
            throw new Exception('No Workflows deleted');
        }
    }

    /**
     * Update workflow arrangement.
     *
     * @param array $data
     * @return array
     */
    public function updateWorkflowArrangement(array $data): array
    {
        $storeId = $data['store_id'];
        $store = Store::find($storeId);

        $workflows = $store->workflows()->orderBy('position', 'asc')->get();
        $workflowIds = $data['workflow_ids'];

        $originalWorkflowPositions = $workflows->pluck('position', 'id');

        $arrangement = collect($workflowIds)->filter(function ($workflowId) use ($originalWorkflowPositions) {
            return collect($originalWorkflowPositions)->keys()->contains($workflowId);
        })->toArray();

        $movedWorkflowPositions = collect($arrangement)->mapWithKeys(function ($workflowId, $newPosition) {
            return [$workflowId => ($newPosition + 1)];
        })->toArray();

        $adjustedOriginalWorkflowPositions = $originalWorkflowPositions->except(collect($movedWorkflowPositions)->keys())->keys()->mapWithKeys(function ($id, $index) use ($movedWorkflowPositions) {
            return [$id => count($movedWorkflowPositions) + $index + 1];
        })->toArray();

        $workflowPositions = array_merge($movedWorkflowPositions, $adjustedOriginalWorkflowPositions);

        if (count($workflowPositions)) {
            DB::table('workflows')
                ->where('store_id', $store->id)
                ->whereIn('id', array_keys($workflowPositions))
                ->update(['position' => DB::raw('CASE id ' . implode(' ', array_map(function ($id, $position) {
                    return 'WHEN "' . $id . '" THEN ' . $position . ' ';
                }, array_keys($workflowPositions), $workflowPositions)) . 'END')]);

            return ['message' => 'Workflow arrangement has been updated'];
        }

        return ['message' => 'No matching workflows to update'];
    }

    /**
     * Show workflow.
     *
     * @param Workflow $workflow
     * @return WorkflowResource
     */
    public function showWorkflow(Workflow $workflow): WorkflowResource
    {
        return $this->showResource($workflow);
    }

    /**
     * Update workflow.
     *
     * @param Workflow $workflow
     * @param array $data
     * @return array
     */
    public function updateWorkflow(Workflow $workflow, array $data): array
    {
        $workflow->update($data);
        return $this->showUpdatedResource($workflow);
    }

    /**
     * Delete workflow.
     *
     * @param Workflow $workflow
     * @return array
     * @throws Exception
     */
    public function deleteWorkflow(Workflow $workflow): array
    {
        $deleted = $workflow->delete();

        if ($deleted) {
            return ['message' => 'Workflow deleted'];
        } else {
            throw new Exception('Workflow delete unsuccessful');
        }
    }
}
