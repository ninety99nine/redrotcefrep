<?php

namespace App\Services;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use Exception;
use App\Models\Store;
use App\Models\Workflow;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\WorkflowResource;
use App\Http\Resources\WorkflowResources;
use App\Enums\WorkflowTarget;
use App\Enums\WorkflowTrigger;
use App\Enums\WorkflowAction;
use App\Enums\WorkflowTemplate;

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
     * Show workflow configurations.
     *
     * @return array
     */
    public function showWorkflowConfigurations(): array
    {
        $targets = collect(WorkflowTarget::cases())->map(function ($target) {
            $triggers = $this->getTriggerTypes($target->value);
            $triggerConfigs = collect($triggers)->map(function ($trigger) use ($target) {
                return [
                    'target' => $target->value,
                    'trigger' => $trigger['value'],
                    'actions' => $this->getActionConfigurations($target->value, $trigger['value'])
                ];
            })->values()->all();
            return $triggerConfigs;
        })->flatten(1)->all();

        return $targets;
    }

    /**
     * Get trigger types based on target.
     *
     * @param string $target
     * @return array
     */
    protected function getTriggerTypes(string $target): array
    {
        $triggers = match ($target) {
            'order' => OrderStatus::values(),
            'payment' => OrderPaymentStatus::values(),
            'product' => ['no stock', 'low stock'],
            default => [],
        };

        return collect($triggers)->map(function ($trigger) {
            return [
                'label' => ucfirst($trigger),
                'value' => $trigger
            ];
        })->values()->all();
    }

    /**
     * Get action configurations for a specific target and trigger.
     *
     * @param string $target
     * @param string $trigger
     * @return array
     */
    protected function getActionConfigurations(string $target, string $trigger): array
    {
        $actions = $this->getActionTypes($target, $trigger);

        return collect($actions)->map(function ($action) use ($target, $trigger) {

            $templates = $this->getTemplateTypes($trigger, $action['value']);

            $templateFields = collect($templates)->map(function ($template) use ($action) {
                $fields = $this->getTemplateConfig($template['value'], $action['value']);

                return [
                    'name' => $template['value'],
                    'fields' => $fields
                ];
            })->values()->all();

            return [
                'name' => $action['value'],
                'templates' => $templateFields
            ];

        })->values()->all();
    }

    /**
     * Get action types based on target and trigger.
     *
     * @param string $target
     * @param string $trigger
     * @return array
     */
    protected function getActionTypes(string $target, string $trigger): array
    {
        $actions = WorkflowAction::cases();

        if ($target === 'product') {
            $actions = collect($actions)->filter(fn($actionType) => in_array($actionType, [
                WorkflowAction::WHATSAPP_TEAM,
                WorkflowAction::EMAIL_TEAM
            ]))->all();
        }

        return collect($actions)->map(function ($actionType) {
            return [
                'label' => ucfirst($actionType->value),
                'value' => $actionType->value
            ];
        })->values()->all();
    }

    /**
     * Get template types based on action.
     *
     * @param string $trigger
     * @param string $action
     * @return array
     */
    protected function getTemplateTypes(string $trigger, string $action): array
    {
        if(in_array($trigger, [OrderStatus::COMPLETED->value, OrderPaymentStatus::PAID->value]) && in_array($action, [WorkflowAction::WHATSAPP_CUSTOMER->value, WorkflowAction::EMAIL_CUSTOMER->value])) {
            $templates = [WorkflowTemplate::ORDER_DETAILS, WorkflowTemplate::REQUEST_REVIEW];
        }else if($trigger === OrderPaymentStatus::UNPAID->value && in_array($action, [WorkflowAction::WHATSAPP_CUSTOMER->value, WorkflowAction::EMAIL_CUSTOMER->value])) {
            $templates = [WorkflowTemplate::ORDER_DETAILS, WorkflowTemplate::PAYMENT_REMINDER];
        }else{
            $templates = [WorkflowTemplate::ORDER_DETAILS];
        }

        return collect($templates)->map(function ($templateType) {
            return [
                'label' => ucfirst($templateType->value),
                'value' => $templateType->value
            ];
        })->values()->all();
    }

    /**
     * Get template configuration.
     *
     * @param string $template
     * @param string $action
     * @return array
     */
    protected function getTemplateConfig(string $template, string $action): array
    {
        $template = match ($template) {
            'order details' => [

            ],
            'request review' => [
                'review_link' => ''
            ],
            'payment reminder' => [
                'add_delay' => false,
                'delay_time_value' => '1',
                'delay_time_unit' => 'hour',
                'auto_cancel' => false,
                'cancel_time_value' => '24',
                'cancel_time_unit' => 'hour',
            ],
            default => [],
        };

        if ($action === WorkflowAction::WHATSAPP_TEAM->value) {
            $template['mobile_numbers'] = [];
        } elseif ($action === WorkflowAction::EMAIL_TEAM->value) {
            $template['email'] = '';
        }else if ($action === WorkflowAction::WHATSAPP_CUSTOMER->value) {
            $template['notes'] = '';
        }

        return $template;
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

        return [
            'deleted' => $deleted,
            'message' => $deleted ? 'Workflow deleted' : 'Workflow delete unsuccessful'
        ];
    }
}
?>
