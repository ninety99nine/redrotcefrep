import { defineStore } from 'pinia';
import { useChangeHistoryStore as changeHistoryState } from '@Stores/change-history-store.js';

export const useWorkflowStore = defineStore('workflow', {
    state: () => ({
        workflow: null,
        workflowForm: null,
        isLoadingWorkflow: false,
        isCreatingWorkflow: false,
        isUpdatingWorkflow: false,
        isDeletingWorkflow: false,
    }),
    actions: {
        reset() {
            this.workflow = null;
            this.workflowForm = null;
            this.isLoadingWorkflow = false;
            this.isCreatingWorkflow = false;
            this.isUpdatingWorkflow = false;
            this.isDeletingWorkflow = false;
            changeHistoryState().reset();
        },
        saveState(actionName) {
            changeHistoryState().saveState(actionName, this.workflowForm);
        },
        saveStateDebounced(actionName) {
            changeHistoryState().saveStateDebounced(actionName, this.workflowForm);
        },
        saveOriginalState(actionName) {
            changeHistoryState().saveOriginalState(actionName, this.workflowForm);
        },
        setWorkflow(workflow) {
            this.workflow = workflow;
        },
        setWorkflowForm(workflow = null, saveState = true) {

            this.workflowForm = {
                name: workflow?.name ?? '',
                active: workflow?.active ?? true,
                actions: workflow?.actions ?? [],
                target: workflow?.target ?? 'order',
                trigger: workflow?.trigger ?? 'waiting',
            };

            if(saveState) {
                this.saveOriginalState('Original workflow');
            }

        }
    },
});
