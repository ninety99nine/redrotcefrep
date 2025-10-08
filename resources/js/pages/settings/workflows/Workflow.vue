<template>

    <div class="max-w-2xl mx-auto pt-24 pb-40">

        <!-- Back Button -->
        <Button
            size="xs"
            type="light"
            class="mb-4"
            :leftIcon="MoveLeft"
            :action="navigateToShowWorkflows">
            <span>Back</span>
        </Button>

        <div class="bg-gray-50 border border-gray-300 rounded-lg p-4 space-y-4 mb-4">

            <!-- Active -->
            <Switch
                size="xs"
                suffixText="Active"
                v-model="workflowForm.active"
                :errorText="formState.getFormError('active')"
                @change="workflowState.saveStateDebounced('Active status changed')"
            />

            <!-- Name -->
            <Input
                max="40"
                type="text"
                label="Name"
                v-model="workflowForm.name"
                placeholder="New order notification"
                :errorText="formState.getFormError('name')"
                :skeleton="isLoadingStore || isLoadingWorkflow"
                @input="workflowState.saveStateDebounced('Name changed')"
                tooltipContent="The workflow name to help you know what this workflow does e.g New order notification"
            />

        </div>

        <div class="bg-gray-50 border border-gray-300 rounded-lg p-4 space-y-4 mb-4">

            <!-- Heading -->
            <div class="flex items-center space-x-2 mb-4">
                <Star size="20" class="shrink-0"></Star>
                <h1 class="font-bold text-lg">Trigger</h1>
            </div>

            <div class="w-full flex items-center space-x-4">

                <span>when</span>

                <!-- Target Select -->
                <Select
                    class="w-full"
                    :search="false"
                    :options="targetTypes"
                    v-model="workflowForm.target"
                    :errorText="formState.getFormError('target')"
                    @change="workflowState.saveStateDebounced('Target changed')"
                />

                <span>is</span>

                <!-- Trigger Select -->
                <Select
                    class="w-full"
                    :search="false"
                    :options="triggerTypes"
                    v-model="workflowForm.trigger"
                    :errorText="formState.getFormError('trigger')"
                    @change="workflowState.saveStateDebounced('Trigger changed')"
                />

            </div>

        </div>

        <div v-if="!isLoadingStore && !isLoadingWorkflow && !isLoadingWorkflowConfigurations">

            <!-- Down Pointing Arrow Separator -->
            <div class="w-40 text-blue-500 border border-blue-500 mx-auto py-1 px-2 rounded-xl">
                <h1 class="text-xs text-center">{{ totalWorkflowActions }} {{ totalWorkflowActions == 1 ? 'Action' : 'Actions' }}</h1>
            </div>

            <div class="flex justify-center">
                <svg class="w-6 h-6 text-blue-500 -mt-0.5 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25 12 21m0 0-3.75-3.75M12 21V3" />
                </svg>
            </div>

            <!-- Draggable Workflow Steps -->
            <draggable
                class="space-y-4 mb-8"
                v-if="hasWorkflowActions"
                handle=".draggable-handle"
                ghost-class="bg-yellow-50"
                v-model="workflowForm.actions"
                @change="workflowState.saveStateDebounced('Action arrangement changed')">

                <div
                    :key="index"
                    class="relative"
                    v-for="(action, index) in workflowForm.actions">

                    <!-- Dotted Separator Line -->
                    <div v-if="index != 0" class="w-0 h-4 mx-auto border-l border-r border-blue-200 border-dashed absolute -top-4 left-1/2 translate-x-1/2"></div>

                    <div class="bg-gray-50 border border-gray-300 rounded-lg p-4 space-y-4">

                        <div class="flex items-center space-x-8">

                            <div class="w-full flex items-center space-x-4">

                                <!-- Icon -->
                                <WhatsappIcon
                                    class="shrink-0 mr-2"
                                    v-if="['whatsapp customer', 'whatsapp team'].includes(action.action)" />

                                <Mail
                                    size="28"
                                    class="shrink-0 text-indigo-500"
                                    v-else-if="['email customer', 'email team'].includes(action.action)" />

                                <!-- Action Select -->
                                <Select
                                    class="w-full"
                                    :search="false"
                                    :options="actionTypes"
                                    v-model="action.action"
                                    @change="updateActionFields(index)"
                                    :errorText="formState.getFormError(`actions.${index}.action`)" />

                                <template v-if="templateTypes(action.action).length >= 2">

                                    <span>using</span>

                                    <!-- Template Select -->
                                    <Select
                                        class="w-full"
                                        :search="false"
                                        v-model="action.template"
                                        :options="templateTypes(action.action)"
                                        @change="updateTemplateFields(index)"
                                        :errorText="formState.getFormError(`actions.${index}.template`)"
                                    />

                                </template>

                            </div>

                            <div class="flex items-center space-x-2">

                                <!-- Delete Button -->
                                <Button
                                    size="xs"
                                    type="bareDanger"
                                    :leftIcon="Trash2"
                                    :action="() => removeWorkflowAction(index)"
                                />
                                <!-- Drag & Drop Handle -->
                                <Move @click.stop size="16" class="draggable-handle cursor-grab active:cursor-grabbing text-gray-500 hover:text-yellow-500"></Move>

                            </div>

                        </div>

                        <!-- Dynamic Fields Rendering -->
                        <div v-for="(field, fieldName) in getActionFields(action.action, action.template, index)" :key="fieldName">

                            <!-- Mobile Numbers for whatsapp team -->
                            <div
                                v-if="fieldName === 'mobile_numbers' && action.action === 'whatsapp team'"
                                :class="{'space-y-4 p-4 bg-slate-100 border border-gray-200 rounded-lg': !action[fieldName]?.length}">

                                <div v-if="!action[fieldName]?.length" class="space-y-1 mb-4">

                                    <h1 class="flex items-center font-lg font-bold">
                                        <span>Send WhatsApp</span>
                                    </h1>

                                    <p class="text-sm text-gray-500">
                                        Add one or more mobile numbers to send the Whatsapp message
                                    </p>

                                </div>

                                <div class="flex justify-center">

                                    <InputMobileNumbers
                                        :isSubmitting="false"
                                        v-model="action[fieldName]"
                                        @change="workflowState.saveStateDebounced('Mobile numbers changed')" />

                                </div>

                            </div>

                            <!-- Email for email team -->
                            <div v-if="fieldName === 'email' && action.action === 'email team'">

                                <Input
                                    type="email"
                                    label="Email"
                                    v-model="action[fieldName]"
                                    placeholder="company@example.com"
                                    @input="workflowState.saveStateDebounced('Email changed')"
                                    :errorText="formState.getFormError(`actions.${index}.email`)" />

                            </div>

                            <!-- Payment Reminder Fields -->
                            <div v-if="action.template === 'payment reminder' && fieldName === 'add_delay'" class="space-y-4">

                                <div class="space-y-2">

                                    <Input
                                        type="checkbox"
                                        inputLabel="Add delay"
                                        v-model="action[fieldName]"
                                        :errorText="formState.getFormError(`actions.${index}.add_delay`)"
                                        @change="workflowState.saveStateDebounced('Add delay status changed')"
                                    />

                                    <template v-if="action[fieldName]">

                                        <p class="text-xs text-gray-400 mb-2">Time to wait before sending payment reminder</p>

                                        <div class="flex items-center space-x-4">

                                            <Input
                                                min="1"
                                                type="number"
                                                class="w-40"
                                                v-model="action.delay_time_value"
                                                @input="workflowState.saveStateDebounced('Delay time changed')"
                                                :errorText="formState.getFormError(`actions.${index}.delay_time_value`)"
                                            />

                                            <Select
                                                class="w-40"
                                                :search="false"
                                                v-model="action.delay_time_unit"
                                                :options="delayTimeUnits(action.delay_time_value)"
                                                @change="workflowState.saveStateDebounced('Delay time changed')"
                                                :errorText="formState.getFormError(`actions.${index}.delay_time_unit`)"
                                            />

                                        </div>

                                    </template>

                                </div>

                            </div>

                            <div
                                v-if="action.template === 'payment reminder' && fieldName === 'auto_cancel'"
                                :class="['space-y-2', { 'border-t border-b border-gray-300 border-dashed py-4': action.add_delay && action[fieldName] }]">

                                <Input
                                    type="checkbox"
                                    v-model="action[fieldName]"
                                    inputLabel="Cancel order if payment is not received"
                                    :errorText="formState.getFormError(`actions.${index}.auto_cancel`)"
                                    @change="workflowState.saveStateDebounced('Auto cancel status changed')" />

                                <template v-if="action[fieldName]">

                                    <p class="text-xs text-gray-400 mb-2">Time to wait before cancelling the order after sending payment reminder</p>

                                    <div class="flex items-center space-x-4">

                                        <Input
                                            min="1"
                                            type="number"
                                            class="w-40"
                                            v-model="action.cancel_time_value"
                                            @input="workflowState.saveStateDebounced('Cancel time changed')"
                                            :errorText="formState.getFormError(`actions.${index}.cancel_time_value`)"
                                        />

                                        <Select
                                            class="w-40"
                                            :search="false"
                                            v-model="action.cancel_time_unit"
                                            :options="delayTimeUnits(action.cancel_time_value)"
                                            @change="workflowState.saveStateDebounced('Cancel time changed')"
                                            :errorText="formState.getFormError(`actions.${index}.cancel_time_unit`)"
                                        />

                                    </div>

                                </template>

                            </div>

                            <!-- Review Link for request review -->
                            <div v-if="fieldName === 'review_link' && action.template === 'request review'">

                                <Input
                                    type="text"
                                    label="Review Link"
                                    placeholder="https://"
                                    secondaryLabel="(optional)"
                                    v-model="action[fieldName]"
                                    @input="workflowState.saveStateDebounced('Review link changed')"
                                    :errorText="formState.getFormError(`actions.${index}.review_link`)"
                                    :description="`If empty a review link will be provided automatically`"
                                    tooltipContent="Link to the platform that allows customers to add a review e.g Google Reviews"
                                />

                            </div>

                            <!-- Notes for payment reminder or request review -->
                            <div v-if="fieldName === 'notes' && ['payment reminder', 'request review'].includes(action.template)">

                                <Input
                                    rows="1"
                                    max="200"
                                    :resize="true"
                                    type="textarea"
                                    label="Additional Notes"
                                    secondaryLabel="(optional)"
                                    description="Less than 200 characters"
                                    v-model="action[fieldName]"
                                    :errorText="formState.getFormError(`actions.${index}.notes`)"
                                    @input="workflowState.saveStateDebounced('Additional notes changed')"
                                />

                            </div>

                        </div>

                    </div>

                </div>

            </draggable>

            <div v-if="totalWorkflowActions < maxWorkflowActions" class="flex justify-center mb-16">

                <Button
                    size="md"
                    type="primary"
                    :leftIcon="Plus"
                    buttonClass="w-60"
                    :action="addWorkflowAction">
                    <span class="ml-1">Add Action</span>
                </Button>

            </div>

            <!-- General Error Info Alert -->
            <Alert
                type="light"
                class="flex justify-between items-center mb-4"
                v-if="totalWorkflowActions == maxWorkflowActions">

                <template #content>
                    <div class="flex items-center space-x-2">
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                        </svg>
                        <span>You can only add up to <span class="font-bold">{{ maxWorkflowActions }} actions</span> per workflow</span>
                    </div>
                </template>

            </Alert>

        </div>

        <div
            v-if="workflow"
            :class="['flex items-center justify-between space-x-4 overflow-hidden rounded-lg p-4 border mt-4', isLoadingStore || isLoadingWorkflow ? 'border-gray-300 bg-gray-50' : 'border-red-300 bg-red-50']">

            <div class="space-y-2">
                <p>Delete <span class="font-bold text-black">{{ workflow.name }}</span>?</p>
            </div>

            <div class="flex justify-end">
                <Modal
                    triggerType="danger"
                    approveType="danger"
                    :approveLeftIcon="Trash2"
                    triggerText="Delete Workflow"
                    approveText="Delete Workflow"
                    :approveAction="deleteWorkflow"
                    :triggerLoading="isDeletingWorkflow"
                    :approveLoading="isDeletingWorkflow">
                    <template #content>
                        <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">Confirm Delete</p>
                        <p class="mb-8">Are you sure you want to delete <span class="font-bold text-black">{{ workflow.name }}</span>?</p>
                    </template>
                </Modal>
            </div>

        </div>

    </div>

</template>

<script>

    import Alert from '@Partials/Alert.vue';
    import Modal from '@Partials/Modal.vue';
    import Input from '@Partials/Input.vue';
    import Switch from '@Partials/Switch.vue';
    import Select from '@Partials/Select.vue';
    import Button from '@Partials/Button.vue';
    import { isEmpty } from '@Utils/stringUtils';
    import Skeleton from '@Partials/Skeleton.vue';
    import { capitalize } from '@Utils/stringUtils';
    import WhatsappIcon from '@Partials/WhatsappIcon.vue';
    import { VueDraggableNext } from 'vue-draggable-next';
    import InputMobileNumbers from '@Partials/InputMobileNumbers.vue';
    import { Move, Mail, Plus, Star, Trash2, MoveLeft } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'storeState', 'workflowState', 'changeHistoryState', 'notificationState'],
        components: {
            Move, Mail, Star, Alert, Modal, Input, Switch, Select, Button, Skeleton, WhatsappIcon, InputMobileNumbers, draggable: VueDraggableNext
        },
        data() {
            return {
                Plus,
                Trash2,
                MoveLeft,
                maxWorkflowActions: 3,
                workflowConfigurations: [],
                appName: import.meta.env.VITE_APP_NAME,
                isLoadingWorkflowConfigurations: false,
            };
        },
        watch: {
            store(newValue, oldValue) {
                if (!oldValue && newValue) {
                    this.setup();
                }
            },
            'workflowForm.target': {
                handler() {
                    const trigger = this.workflowForm.trigger;
                    const availableTriggers = this.triggerTypes.map(t => t.value);
                    this.workflowForm.trigger = availableTriggers.includes(trigger) ? trigger : this.triggerTypes[0]?.value || null;
                }
            },
            'workflowForm.trigger': {
                handler() {
                    const availableActions = this.actionTypes.map(a => a.value);
                    this.workflowForm.actions = this.workflowForm.actions.map(action => {
                        if (!availableActions.includes(action.action)) {
                            const defaultAction = this.actionTypes[0]?.value;
                            const defaultTemplate = this.templateTypes(defaultAction)[0]?.value || null;
                            const fields = this.getActionFields(defaultAction, defaultTemplate, 0);
                            return {
                                action: defaultAction,
                                template: defaultTemplate,
                                ...fields
                            };
                        }
                        return action;
                    });
                }
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            isEditing() {
                return this.workflowId != null;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            workflowId() {
                return this.$route.params.workflow_id;
            },
            workflow() {
                return this.workflowState.workflow;
            },
            workflowForm() {
                return this.workflowState.workflowForm;
            },
            isLoadingWorkflow() {
                return this.workflowState.isLoadingWorkflow;
            },
            isDeletingWorkflow() {
                return this.workflowState.isDeletingWorkflow;
            },
            hasWorkflowActions() {
                return this.totalWorkflowActions > 0;
            },
            totalWorkflowActions() {
                return this.workflowForm.actions?.length ?? 0;
            },
            targetTypes() {
                // Deduplicate targets and map to options
                const uniqueTargets = [...new Set(this.workflowConfigurations.map(config => config.target))];
                return uniqueTargets.map(target => ({
                    label: capitalize(target),
                    value: target
                }));
            },
            triggerTypes() {
                if (!this.workflowForm.target) return [];
                // Filter workflow configurations by selected target and deduplicate triggers
                const triggers = this.workflowConfigurations
                    .filter(c => c.target === this.workflowForm.target)
                    .map(c => c.trigger);
                return [...new Set(triggers)].map(trigger => ({
                    label: capitalize(trigger),
                    value: trigger
                }));
            },
            actionTypes() {
                if (!this.workflowForm.target || !this.workflowForm.trigger) return [];
                const config = this.workflowConfigurations.find(c => c.target === this.workflowForm.target && c.trigger === this.workflowForm.trigger);
                if (!config) return [];
                return config.actions.map(action => ({
                    label: capitalize(action.name.replace(/([A-Z])/g, ' $1').trim()),
                    value: action.name
                }));
            }
        },
        methods: {
            isEmpty,
            capitalize,
            async setup() {
                this.workflowState.setWorkflowForm(null, false);
                if (this.store) await this.showWorkflowConfigurations();
                if (this.store && this.workflowId) await this.showWorkflow();
            },
            setActionButtons() {
                this.changeHistoryState.removeButtons();
                this.changeHistoryState.addDiscardButton();
                this.changeHistoryState.addActionButton(
                    this.isEditing ? 'Save Changes' : 'Add Workflow',
                    this.isEditing ? this.updateWorkflow : this.createWorkflow,
                    'primary',
                    null
                );
            },
            delayTimeUnits(value) {
                return [
                    { label: value == '1' ? 'Minute' : 'Minutes', value: 'minute' },
                    { label: value == '1' ? 'Hour' : 'Hours', value: 'hour' }
                ];
            },
            templateTypes(action) {
                if (!this.workflowForm.target || !this.workflowForm.trigger) return [];
                const config = this.workflowConfigurations.find(c => c.target === this.workflowForm.target && c.trigger === this.workflowForm.trigger);
                if (!config) return [];
                const actionConfig = config.actions.find(a => a.name === action);
                if (!actionConfig) return [];
                return actionConfig.templates.map(template => ({
                    label: capitalize(template.name.replace(/([A-Z])/g, ' $1').trim()),
                    value: template.name
                }));
            },
            updateTemplateFields(index) {
                const action = this.workflowForm.actions[index];
                const fields = this.getActionFields(action.action, action.template, index);
                // Preserve action and template, reset other fields
                Object.keys(action).forEach(key => {
                    if (key !== 'action' && key !== 'template') {
                        delete action[key];
                    }
                });
                Object.assign(action, fields);
                this.workflowState.saveStateDebounced('Template changed');
            },
            getActionFields(actionName, templateName, index) {
                if (!this.workflowForm.target || !this.workflowForm.trigger) return {};
                const config = this.workflowConfigurations.find(c => c.target === this.workflowForm.target && c.trigger === this.workflowForm.trigger);
                if (!config) return {};
                const actionConfig = config.actions.find(a => a.name === actionName);
                if (!actionConfig) return {};
                const templateConfig = actionConfig.templates.find(t => t.name === templateName);
                if (!templateConfig) return {};
                return templateConfig.fields;
            },
            updateActionFields(index) {
                const action = this.workflowForm.actions[index];
                const config = this.workflowConfigurations.find(c => c.target === this.workflowForm.target && c.trigger === this.workflowForm.trigger);
                if (!config) return;
                const actionConfig = config.actions.find(a => a.name === action.action);
                if (!actionConfig) return;
                const defaultTemplate = actionConfig.templates[0]?.name || 'order details';
                action.template = defaultTemplate;
                // Reset fields and apply defaults from API
                const fields = this.getActionFields(action.action, defaultTemplate, index);
                Object.keys(action).forEach(key => {
                    if (key !== 'action' && key !== 'template') {
                        delete action[key];
                    }
                });
                Object.assign(action, fields);
                this.workflowState.saveStateDebounced('Action changed');
            },
            addWorkflowAction() {
                if (!this.actionTypes.length) return;
                const defaultAction = this.actionTypes[0].value;
                const defaultTemplate = this.templateTypes(defaultAction)[0]?.value || 'order details';
                const fields = this.getActionFields(defaultAction, defaultTemplate, 0);
                this.workflowForm.actions.push({
                    action: defaultAction,
                    template: defaultTemplate,
                    ...fields
                });
                this.workflowState.saveStateDebounced('Action added');
            },
            removeWorkflowAction(index) {
                this.workflowForm.actions.splice(index, 1);
                this.workflowState.saveStateDebounced('Action removed');
            },
            async showWorkflowConfigurations() {
                try {

                    this.isLoadingWorkflowConfigurations = true;

                    const config = {
                        params: { store_id: this.store.id }
                    };

                    const response = await axios.get('/api/workflows/configurations', config);
                    this.workflowConfigurations = response.data;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Failed to fetch workflow configurations';
                    this.notificationState.showWarningNotification(message);
                    console.error('Failed to fetch workflow configurations:', error);
                } finally {
                    this.isLoadingWorkflowConfigurations = false;
                }
            },
            async showWorkflow() {
                try {
                    this.workflowState.isLoadingWorkflow = true;
                    const response = await axios.get(`/api/workflows/${this.workflowId}`, {
                        params: { store_id: this.store.id }
                    });
                    const workflow = response.data;
                    this.workflowState.setWorkflow(workflow);
                    this.workflowState.setWorkflowForm(workflow, true);
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching workflow';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch workflow:', error);
                } finally {
                    this.workflowState.isLoadingWorkflow = false;
                }
            },
            async createWorkflow() {
                try {
                    if (this.workflowState.isCreatingWorkflow) return;
                    this.formState.hideFormErrors();
                    if (this.isEmpty(this.workflowForm.name)) {
                        this.formState.setFormError('name', 'The name is required');
                    }
                    if (this.formState.hasErrors) return;
                    this.workflowState.isCreatingWorkflow = true;
                    this.changeHistoryState.actionButtons[1].loading = true;
                    const data = { ...this.workflowForm, store_id: this.store.id };
                    const response = await axios.post('/api/workflows', data);
                    const workflow = response.data;
                    this.workflowState.setWorkflow(workflow);
                    this.notificationState.showSuccessNotification('Workflow created');
                    this.workflowState.saveOriginalState('Original workflow');
                    await this.navigateToShowWorkflows();
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while creating workflow';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to create workflow:', error);
                } finally {
                    this.workflowState.isCreatingWorkflow = false;
                    this.changeHistoryState.actionButtons[1].loading = false;
                }
            },
            async updateWorkflow() {
                try {
                    if (this.workflowState.isUpdatingWorkflow) return;
                    this.formState.hideFormErrors();
                    if (this.isEmpty(this.workflowForm.name)) {
                        this.formState.setFormError('name', 'The name is required');
                    }
                    if (this.formState.hasErrors) return;
                    this.workflowState.isUpdatingWorkflow = true;
                    this.changeHistoryState.actionButtons[1].loading = true;
                    const data = { ...this.workflowForm, store_id: this.store.id };
                    const response = await axios.put(`/api/workflows/${this.workflowId}`, data);
                    const workflow = response.data;
                    this.workflowState.setWorkflow(workflow);
                    this.notificationState.showSuccessNotification('Workflow updated');
                    this.workflowState.saveOriginalState('Original workflow');
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating workflow';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update workflow:', error);
                } finally {
                    this.workflowState.isUpdatingWorkflow = false;
                    this.changeHistoryState.actionButtons[1].loading = false;
                }
            },
            async deleteWorkflow(hideModal) {
                try {
                    if (this.workflowState.isDeletingWorkflow) return;
                    this.workflowState.isDeletingWorkflow = true;
                    await axios.delete(`/api/workflows/${this.workflowId}`, {
                        data: { store_id: this.store.id }
                    });
                    hideModal();
                    await new Promise(resolve => setTimeout(resolve, 500));
                    this.notificationState.showSuccessNotification('Workflow deleted');
                    await this.navigateToShowWorkflows();
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting workflow';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete workflow:', error);
                    hideModal();
                } finally {
                    this.workflowState.isDeletingWorkflow = false;
                }
            },
            async navigateToShowWorkflows() {
                await this.$router.push({
                    name: 'show-workflows',
                    query: { store_id: this.store.id }
                });
            },
            setWorkflowForm(workflowForm) {
                this.workflowState.workflowForm = workflowForm;
            }
        },
        beforeUnmount() {
            this.workflowState.reset();
        },
        created() {
            this.setup();
            this.setActionButtons();
            const listeners = ['undo', 'redo', 'jumpToHistory', 'resetHistoryToCurrent', 'resetHistoryToOriginal'];
            for (let listener of listeners) {
                this.changeHistoryState.listeners[listener] = this.setWorkflowForm;
            }
        }
    };

</script>
