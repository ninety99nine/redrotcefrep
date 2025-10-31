<template>

    <div class="max-w-2xl mx-auto pt-32 pb-40">

        <div
            class="flex justify-end"
            v-if="!isLoadingStore && !isLoadingWorkflows && hasWorkflows">

            <Button
                size="md"
                class="mb-4"
                type="primary"
                :leftIcon="Plus"
                :action="navigateToAddWorkflow">
                <span>Add Workflow</span>
            </Button>

        </div>

        <!-- Loading Placeholder -->
        <div v-if="isLoadingStore || isLoadingWorkflows" class="space-y-3 mt-4">

            <div
                :key="index"
                v-for="(_, index) in [1, 2, 3]"
                class="bg-white p-4 shadow-sm rounded-xl transition-all duration-300">

                <div class="flex justify-between items-center">

                    <Skeleton width="w-40" :shine="true"></Skeleton>

                    <div class="flex items-center space-x-4">

                        <Skeleton width="w-16" :shine="true"></Skeleton>

                        <Skeleton width="w-4" height="h-4" rounded="rounded-md" :shine="true"></Skeleton>

                        <Skeleton width="w-4" height="h-4" rounded="rounded-md" :shine="true"></Skeleton>

                    </div>

                </div>

            </div>

        </div>

        <!-- Workflows -->
        <draggable
            v-model="workflows"
            class="space-y-3 mt-4"
            v-else-if="hasWorkflows"
            handle=".draggable-handle"
            ghost-class="bg-yellow-50"
            @change="changeWorkflowArrangement">

            <div
                :key="workflow.id"
                v-for="workflow in workflows"
                @click.stop="() => navigateToEditWorkflow(workflow)"
                class="bg-white p-4 shadow-sm rounded-xl transition-all duration-300 border border-transparent hover:border-gray-300 hover:shadow-lg cursor-pointer">

                <div class="flex justify-between items-center">

                    <!-- Name -->
                    <span class="text-sm">{{ workflow.name }}</span>

                    <div class="flex items-center space-x-4">

                        <!-- Active Status -->
                        <Pill :type="workflow.active ? 'success' : 'warning'" size="xs">{{ workflow.active ? 'active' : 'inactive'}}</Pill>

                        <!-- Delete Button -->
                        <Button
                            size="xs"
                            type="bareDanger"
                            :leftIcon="Trash2"
                            :action="() => showDeleteWorkflowModal(workflow)">
                        </Button>

                        <!-- Drag & Drop Handle -->
                        <Move @click.stop size="16" class="draggable-handle cursor-grab active:cursor-grabbing text-gray-500 hover:text-yellow-500"></Move>

                    </div>

                </div>

            </div>

        </draggable>

        <div
            v-else
            class="flex flex-col items-center justify-center bg-linear-to-br from-indigo-50 to-purple-50 border border-gray-300 shadow-lg rounded-2xl py-16 px-8 space-y-6">

            <div class="relative">
                <div class="bg-linear-to-br from-white-50 to-indigo-50 text-indigo-500 rounded-full p-2">
                    <Workflow size="60"></Workflow>
                </div>
                <div class="absolute inset-0 bg-indigo-300 opacity-20 rounded-full animate-ping"></div>
            </div>

            <!-- Engaging headline and description -->
            <div class="text-center">
                <h3 class="text-xl font-semibold text-gray-800">Set Up Your Workflows!</h3>
                <span class="text-sm text-gray-600 mt-2 block max-w-sm">
                    Add a workflow to automate your communication.
                </span>
            </div>

            <!-- Interactive button with gradient and hover effect -->
            <button
                size="lg"
                type="bare"
                @click.stop="navigateToAddWorkflow"
                class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-medium py-3 px-6 rounded-lg hover:from-indigo-600 hover:to-purple-700 transition-all duration-300 hover:scale-105 cursor-pointer">
                <span>Add Workflow Now</span>
            </button>

        </div>

        <Modal
            approveType="danger"
            :approveLeftIcon="Trash2"
            ref="deleteWorkflowModal"
            approveText="Delete Workflow"
            :approveAction="deleteWorkflow"
            :triggerLoading="isDeletingWorkflow"
            :approveLoading="isDeletingWorkflow">

            <template #content
                v-if="deletableWorkflow">
                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">Confirm Delete</p>
                <p class="mb-8">Are you sure you want to delete <span class="font-bold text-black">{{ deletableWorkflow.name }}</span>?</p>
            </template>

        </Modal>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Modal from '@Partials/Modal.vue';
    import Button from '@Partials/Button.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { VueDraggableNext } from 'vue-draggable-next';
    import { Plus, Move, Trash2, Workflow } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'storeState', 'notificationState'],
        components: { Workflow, Pill, Modal, Button, Skeleton, Move, draggable: VueDraggableNext },
        data() {
            return {
                Plus,
                Trash2,
                pagination: null,
                workflows: [],
                deletableWorkflow: null,
                isDeletingWorkflow: false,
                isLoadingWorkflows: false,
                isChangingWorkflowArrangement: false
            }
        },
        watch: {
            store(newValue, oldValue) {
                if(!oldValue && newValue) {
                    this.setup();
                }
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            hasWorkflows() {
                return this.workflows.length > 0;
            }
        },
        methods: {
            setup() {
                if(this.store) {
                    this.showWorkflows();
                }
            },
            showDeleteWorkflowModal(workflow) {
                this.deletableWorkflow = workflow;
                this.$refs.deleteWorkflowModal.showModal();
            },
            async navigateToAddWorkflow() {
                await this.$router.push({
                    name: 'add-workflow',
                    query: {
                        store_id: this.store.id
                    }
                });
            },
            async navigateToEditWorkflow(workflow) {
                await this.$router.push({
                    name: 'edit-workflow',
                    query: {
                        store_id: this.store.id,
                    },
                    params: {
                        workflow_id: workflow.id
                    }
                });
            },
            async showWorkflows() {
                try {

                    this.isLoadingWorkflows = true;

                    let config = {
                        params: {
                            per_page: 100,
                            store_id: this.store.id,
                            association: 'team member'
                        }
                    };

                    const response = await axios.get('/api/workflows', config);

                    this.pagination = response.data;
                    this.workflows = this.pagination.data;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching workflows';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch workflows:', error);
                } finally {
                    this.isLoadingWorkflows = false;
                }
            },
            async changeWorkflowArrangement() {

                try {

                    if(this.isChangingWorkflowArrangement) return;

                    const workflowIds = this.workflows.map((workflow) => workflow.id);

                    if(workflowIds.length == 0) return;

                    this.isChangingWorkflowArrangement = true;

                    const data = {
                        store_id: this.store.id,
                        workflow_ids: workflowIds
                    };

                    await axios.post(`/api/workflows/arrangement`, data);
                    this.notificationState.showSuccessNotification('Arrangement changed');

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating workflow arrangement';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update workflow arrangement:', error);
                } finally {
                    this.isChangingWorkflowArrangement = false;
                }

            },
            async deleteWorkflow() {

                try {

                    if(this.isDeletingWorkflow) return;

                    this.isDeletingWorkflow = true;

                    const config = {
                        data: {
                            store_id: this.store.id
                        }
                    }

                    await axios.delete(`/api/workflows/${this.deletableWorkflow.id}`, config);

                    this.showWorkflows();
                    this.$refs.deleteWorkflowModal.hideModal();
                    this.notificationState.showSuccessNotification('Workflow deleted');

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting workflow';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete workflow:', error);
                    hideModal();
                } finally {
                    this.isDeletingWorkflow = false;
                }

            },
        },
        created() {
            this.setup();
        }
    };

</script>
