<?php

namespace App\Http\Controllers;

use App\Models\Workflow;
use App\Services\WorkflowService;
use App\Http\Resources\WorkflowResource;
use App\Http\Resources\WorkflowResources;
use App\Http\Requests\Workflow\ShowWorkflowRequest;
use App\Http\Requests\Workflow\ShowWorkflowsRequest;
use App\Http\Requests\Workflow\CreateWorkflowRequest;
use App\Http\Requests\Workflow\UpdateWorkflowRequest;
use App\Http\Requests\Workflow\DeleteWorkflowRequest;
use App\Http\Requests\Workflow\DeleteWorkflowsRequest;
use App\Http\Requests\Workflow\UpdateWorkflowArrangementRequest;

class WorkflowController extends Controller
{
    /**
     * @var WorkflowService
     */
    protected $service;

    /**
     * WorkflowController constructor.
     *
     * @param WorkflowService $service
     */
    public function __construct(WorkflowService $service)
    {
        $this->service = $service;
    }

    /**
     * Show workflows.
     *
     * @param ShowWorkflowsRequest $request
     * @return WorkflowResources|array
     */
    public function showWorkflows(ShowWorkflowsRequest $request): WorkflowResources|array
    {
        return $this->service->showWorkflows($request->validated());
    }

    /**
     * Create workflow.
     *
     * @param CreateWorkflowRequest $request
     * @return array
     */
    public function createWorkflow(CreateWorkflowRequest $request): array
    {
        return $this->service->createWorkflow($request->validated());
    }

    /**
     * Delete multiple workflows.
     *
     * @param DeleteWorkflowsRequest $request
     * @return array
     */
    public function deleteWorkflows(DeleteWorkflowsRequest $request): array
    {
        $workflowIds = request()->input('workflow_ids', []);
        return $this->service->deleteWorkflows($workflowIds);
    }

    /**
     * Update workflow arrangement.
     *
     * @param UpdateWorkflowArrangementRequest $request
     * @return array
     */
    public function updateWorkflowArrangement(UpdateWorkflowArrangementRequest $request): array
    {
        return $this->service->updateWorkflowArrangement($request->validated());
    }

    /**
     * Show workflow.
     *
     * @param ShowWorkflowRequest $request
     * @param Workflow $workflow
     * @return WorkflowResource
     */
    public function showWorkflow(ShowWorkflowRequest $request, Workflow $workflow): WorkflowResource
    {
        return $this->service->showWorkflow($workflow);
    }

    /**
     * Update workflow.
     *
     * @param UpdateWorkflowRequest $request
     * @param Workflow $workflow
     * @return array
     */
    public function updateWorkflow(UpdateWorkflowRequest $request, Workflow $workflow): array
    {
        return $this->service->updateWorkflow($workflow, $request->validated());
    }

    /**
     * Delete workflow.
     *
     * @param DeleteWorkflowRequest $request
     * @param Workflow $workflow
     * @return array
     */
    public function deleteWorkflow(DeleteWorkflowRequest $request, Workflow $workflow): array
    {
        return $this->service->deleteWorkflow($workflow);
    }
}
