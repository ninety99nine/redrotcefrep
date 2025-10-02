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
                tooltipContent="The workflow name to help you know what this workflow does e.g New order notification">
            </Input>

        </div>

        <div class="bg-gray-50 border border-gray-300 rounded-lg p-4 space-y-4 mb-4">

            <!-- Heading -->
            <div class="flex items-center space-x-2 mb-4">
                <Star size="20"></Star>
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
                    @change="workflowState.saveStateDebounced('Target changed')">
                </Select>

                <span>is</span>

                <!-- Trigger Select -->
                <Select
                    class="w-full"
                    :search="false"
                    :options="triggerTypes"
                    v-model="workflowForm.trigger"
                    :errorText="formState.getFormError('trigger')"
                    @change="workflowState.saveStateDebounced('Trigger changed')">
                </Select>

            </div>

        </div>

        <div v-if="!isLoadingStore && !isLoadingWorkflow">

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
                                    class="flex-shrink-0 mr-2"
                                    v-if="['whatsapp customer', 'whatsapp team'].includes(workflowForm.actions[index].action)">
                                </WhatsappIcon>
                                <Mail
                                    size="28"
                                    class="text-indigo-500"
                                    v-else-if="['email customer', 'email team'].includes(workflowForm.actions[index].action)">
                                </Mail>

                                <!-- Action Select -->
                                <Select
                                    class="w-full"
                                    :search="false"
                                    :options="actionTypes"
                                    v-model="workflowForm.actions[index].action"
                                    @change="workflowState.saveStateDebounced('Action changed')"
                                    :errorText="formState.getFormError(`actions.${index}.action`)">
                                </Select>

                                <template v-if="templateTypes(workflowForm.actions[index].action).length >= 2">

                                    <span>using</span>

                                    <!-- Template Select -->
                                    <Select
                                        class="w-full"
                                        :search="false"
                                        v-model="workflowForm.actions[index].template"
                                        :options="templateTypes(workflowForm.actions[index].action)"
                                        @change="workflowState.saveStateDebounced('Template changed')"
                                        :errorText="formState.getFormError(`actions.${index}.template`)">
                                    </Select>

                                </template>

                            </div>

                            <div class="flex items-center space-x-2">

                                <!-- Delete Button -->
                                <Button
                                    size="xs"
                                    type="bareDanger"
                                    :leftIcon="Trash2"
                                    :action="() => removeWorkflowAction(index)">
                                </Button>

                                <!-- Drag & Drop Handle -->
                                <Move @click.stop size="16" class="draggable-handle cursor-grab active:cursor-grabbing text-gray-500 hover:text-yellow-500"></Move>

                            </div>

                        </div>

                        <div v-if="workflowForm.actions[index].action == 'whatsapp team'"
                            :class="{'space-y-4 p-4 bg-slate-100 border border-gray-200 rounded-lg' : workflowForm.actions[index].mobile_numbers.length == 0 }">

                            <div v-if="workflowForm.actions[index].mobile_numbers.length == 0" class="space-y-1 mb-4">

                                <h1 class="flex items-center font-lg font-bold">
                                    <span>Send WhatsApp</span>
                                </h1>

                                <p class="text-sm text-gray-500">
                                    Add one or more mobile numbers to send the Whatsapp message
                                </p>

                            </div>

                            <div class="flex justify-center">

                                <!-- Mobile Numbers Input -->
                                <InputMobileNumbers
                                    :isSubmitting="false"
                                    v-model="workflowForm.actions[index].mobile_numbers"
                                    @change="workflowState.saveStateDebounced('Mobile numbers changed')">
                                </InputMobileNumbers>

                            </div>

                        </div>

                        <div v-if="workflowForm.actions[index].action == 'email team'">

                            <!-- Email Input -->
                            <Input
                                type="email"
                                label="Email"
                                placeholder="company@example.com"
                                v-model="workflowForm.actions[index].email"
                                :errorText="formState.getFormError('email')"
                                @input="workflowState.saveStateDebounced('Email changed')">
                            </Input>

                        </div>

                        <div v-if="workflowForm.actions[index].template == 'payment reminder'" class="space-y-4">

                            <div class="space-y-2">

                                <Input
                                    type="checkbox"
                                    inputLabel="Add delay"
                                    v-model="workflowForm.actions[index].add_delay"
                                    :errorText="formState.getFormError(`actions.${index}.add_delay`)"
                                    @change="workflowState.saveStateDebounced('Add delay status changed')">
                                </Input>

                                <template v-if="workflowForm.actions[index].add_delay">

                                    <p class="text-xs text-gray-400 mb-2">Time to wait before sending payment reminder</p>

                                    <div class="flex items-center space-x-4">

                                        <!-- Delay Time Value Input -->
                                        <Input
                                            min="1"
                                            type="number"
                                            class="w-40"
                                            v-model="workflowForm.actions[index].delay_time_value"
                                            @input="workflowState.saveStateDebounced('Delay time changed')"
                                            :errorText="formState.getFormError(`actions.${index}.delay_time_value`)">
                                        </Input>

                                        <!-- Delay Time Units Input -->
                                        <Select
                                            class="w-40"
                                            :search="false"
                                            :options="delayTimeUnits(index)"
                                            v-model="workflowForm.actions[index].delay_time_unit"
                                            @change="workflowState.saveStateDebounced('Delay time changed')"
                                            :errorText="formState.getFormError(`actions.${index}.delay_time_unit`)">
                                        </Select>

                                    </div>

                                </template>

                            </div>

                            <div :class="['space-y-2', { 'border-t border-b border-gray-300 border-dashed py-4' : workflowForm.actions[index].add_delay && workflowForm.actions[index].auto_cancel }]">

                                <Input
                                    type="checkbox"
                                    v-model="workflowForm.actions[index].auto_cancel"
                                    inputLabel="Cancel order if payment is not received"
                                    :errorText="formState.getFormError(`actions.${index}.auto_cancel`)"
                                    @change="workflowState.saveStateDebounced('Auto cancel status changed')">
                                </Input>

                                <template v-if="workflowForm.actions[index].auto_cancel">

                                    <p class="text-xs text-gray-400 mb-2">Time to wait before cancelling the order after sending payment reminder</p>

                                    <div class="flex items-center space-x-4">

                                        <!-- Cancel Time Value Input -->
                                        <Input
                                            min="1"
                                            type="number"
                                            class="w-40"
                                            v-model="workflowForm.actions[index].cancel_time_value"
                                            @input="workflowState.saveStateDebounced('Cancel time changed')"
                                            :errorText="formState.getFormError(`actions.${index}.cancel_time_value`)">
                                        </Input>

                                        <!-- Cancel Time Units Input -->
                                        <Select
                                            class="w-40"
                                            :search="false"
                                            :options="delayTimeUnits(index)"
                                            v-model="workflowForm.actions[index].delay_time_unit"
                                            @change="workflowState.saveStateDebounced('Cancel time changed')"
                                            :errorText="formState.getFormError(`actions.${index}.cancel_time_unit`)">
                                        </Select>

                                    </div>

                                </template>

                            </div>

                        </div>

                        <div v-if="workflowForm.actions[index].template == 'request review'">

                            <!-- Review Link Input -->
                            <Input
                                type="text"
                                label="Review Link"
                                placeholder="https://"
                                secondaryLabel="(optional)"
                                v-model="workflowForm.actions[index].review_link"
                                @input="workflowState.saveStateDebounced('Review link changed')"
                                :errorText="formState.getFormError(`actions.${index}.review_link`)"
                                :description="`If empty a review link will be provided automatically`"
                                tooltipContent="Link to the platform that allows customers to add a review e.g Google Reviews">
                            </Input>

                        </div>

                        <div v-if="['payment reminder', 'request review'].includes(workflowForm.actions[index].template)">

                            <!-- Additional Notes Input -->
                            <Input
                                rows="1"
                                max="200"
                                :resize="true"
                                type="textarea"
                                label="Additional Notes"
                                secondaryLabel="(optional)"
                                description="Less than 200 characters"
                                v-model="workflowForm.actions[index].notes"
                                :errorText="formState.getFormError('notes')"
                                @input="workflowState.saveStateDebounced('Additional notes changed')">
                            </Input>

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
    import Skeleton from '@Partials/Skeleton.vue';
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
                appName: import.meta.env.VITE_APP_NAME,
                targetTypes: [
                    { label: 'Order', value: 'order' },
                    { label: 'Payment', value: 'payment' },
                    { label: 'Product', value: 'product' },
                ]
            }
        },
        watch: {
            store(newValue, oldValue) {
                if(!oldValue && newValue) {
                    this.setup();
                }
            },
            workflowForm: {
                handler(newValue, oldValue) {

                    if(oldValue != null) {

                        const trigger = this.workflowForm.trigger;
                        const actionTypeValues = this.actionTypes.map(actionType => actionType.value);
                        const triggerTypeValues = this.triggerTypes.map(triggerType => triggerType.value);

                        if(!triggerTypeValues.includes(trigger)) {
                            this.workflowForm.trigger = triggerTypeValues[0];
                        }

                        for (let index = 0; index < this.workflowForm.actions.length; index++) {

                            const action = this.workflowForm.actions[index].action;
                            const template = this.workflowForm.actions[index].template;
                            const templateTypeValues = this.templateTypes(action).map(templateType => templateType.value);

                            if(!actionTypeValues.includes(action)) {
                                this.workflowForm.actions[index].action = this.actionTypes[0].value;
                            }

                            if(!templateTypeValues.includes(template)) {
                                this.workflowForm.actions[index].template = templateTypeValues[0];
                            }

                        }

                    }

                },
                deep: true
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
            triggerTypes() {
                if(this.workflowForm.target == 'order') {
                    return [
                        { label: 'Waiting', value: 'waiting' },
                        { label: 'Cancelled', value: 'cancelled' },
                        { label: 'Completed', value: 'completed' },
                        { label: 'On its way', value: 'on its way' },
                        { label: 'Ready for pickup', value: 'ready for pickup' },
                    ];
                }else if(this.workflowForm.target == 'payment') {
                    return [
                        { label: 'Paid', value: 'paid' },
                        { label: 'Unpaid', value: 'unpaid' },
                        { label: 'Partially paid', value: 'partially paid' },
                        { label: 'Waiting confirmation', value: 'waiting confirmation' },
                    ];
                }else if(this.workflowForm.target == 'product') {
                    return [
                        { label: 'No stock', value: 'no stock' },
                        { label: 'Low stock', value: 'low stock' },
                    ];
                }else{
                    return [];
                }

            },
            actionTypes() {
                const actions = {
                    whatsappTeam: { label: 'Whatsapp team', value: 'whatsapp team' },
                    whatsappCustomer: { label: 'Whatsapp customer', value: 'whatsapp customer' },
                    emailTeam: { label: 'Email team', value: 'email team' },
                    emailCustomer: { label: 'Email customer', value: 'email customer' }
                };

                if (this.workflowForm.trigger === 'product') {
                    return [actions.whatsappTeam, actions.emailTeam];
                } else {
                    return [actions.whatsappTeam, actions.whatsappCustomer, actions.emailTeam, actions.emailCustomer];
                }
            }
        },
        methods: {
            setup() {
                this.workflowState.setWorkflowForm(null, true);
                if(this.store && this.workflowId) this.showWorkflow();
            },
            setActionButtons() {
                this.changeHistoryState.removeButtons();
                this.changeHistoryState.addDiscardButton();
                this.changeHistoryState.addActionButton(
                    this.isEditing ? 'Save Changes' : 'Add Workflow',
                    this.isEditing ? this.updateWorkflow : this.createWorkflow,
                    'primary',
                    null,
                );
            },
            async navigateToShowWorkflows() {
                await this.$router.push({
                    name: 'show-workflows',
                    query: {
                        store_id: this.store.id
                    }
                });
            },
            templateTypes(action) {
                const templates = {
                    orderDetails: { label: 'Order details', value: 'order details' },
                    requestReview: { label: 'Request review', value: 'request review' },
                    paymentReminder: { label: 'Payment reminder', value: 'payment reminder' },
                };

                if (action === 'whatsapp customer') {
                    if (this.workflowForm.trigger === 'paid') {
                        return [templates.orderDetails, templates.requestReview];
                    } else if (this.workflowForm.trigger === 'unpaid') {
                        return [templates.orderDetails, templates.paymentReminder];
                    } else if (this.workflowForm.trigger === 'completed') {
                        return [templates.orderDetails, templates.requestReview];
                    } else {
                    return [templates.orderDetails];
                }
                } else if (this.workflowForm.action === 'whatsapp team') {
                    return [templates.orderDetails];
                }

                return [];
            },
            delayTimeUnits(index) {
                return [
                    { label: (this.workflowForm.actions[index].delay_time_value == '1' ? 'Minute' : 'Minutes'), value: 'minute' },
                    { label: (this.workflowForm.actions[index].delay_time_value == '1' ? 'Hour' : 'Hours'), value: 'hour' },
                ];
            },
            addWorkflowAction() {
                this.workflowForm.actions.push({
                    notes : '',
                    email : '',
                    mobile_numbers : [],
                    action: this.actionTypes[0].value,
                    template: this.templateTypes[0]?.value ?? null,

                    add_delay: false,
                    delay_time_value: '1',
                    delay_time_unit: 'hour',

                    auto_cancel: false,
                    cancel_time_value: '24',
                    cancel_time_unit: 'hour',

                    review_link : ''
                });

                this.workflowState.saveStateDebounced('Action added');
            },
            removeWorkflowAction(index) {
                this.workflowForm.actions.splice(index, 1);
                this.workflowState.saveStateDebounced('Action removed');
            },
            async showWorkflow() {
                try {

                    this.workflowState.isLoadingWorkflow = true;

                    let config = {
                        params: {
                            store_id: this.store.id
                        }
                    };

                    const response = await axios.get(`/api/workflows/${this.workflowId}`, config);

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

                    if(this.workflowState.isCreatingWorkflow) return;

                    this.formState.hideFormErrors();

                    if(this.workflowForm.name == null || this.workflowForm.name.trim() === '') {
                        this.formState.setFormError('name', 'The name is required');
                    }

                    if(this.formState.hasErrors) {
                        return;
                    }

                    this.workflowState.isCreatingWorkflow = true;
                    this.changeHistoryState.actionButtons[1].loading = true;

                    const data = {
                        ...this.workflowForm,
                        store_id: this.store.id,
                    }

                    const response = await axios.post(`/api/workflows`, data);
                    const workflow = response.data.delivery_method;

                    this.workflowState.setWorkflow(workflow);

                    this.notificationState.showSuccessNotification(`Workflow created`);
                    this.workflowState.saveOriginalState('Original workflow');

                    this.navigateToShowWorkflows();

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

                    if(this.workflowState.isUpdatingWorkflow) return;

                    this.formState.hideFormErrors();

                    if(this.workflowForm.name == null || this.workflowForm.name.trim() === '') {
                        this.formState.setFormError('name', 'The name is required');
                    }

                    if(this.formState.hasErrors) {
                        return;
                    }

                    this.workflowState.isUpdatingWorkflow = true;
                    this.changeHistoryState.actionButtons[1].loading = true;

                    const data = {
                        ...this.workflowForm,
                        store_id: this.store.id
                    }

                    const response = await axios.put(`/api/workflows/${this.workflowId}`, data);
                    const workflow = response.data.delivery_method;

                    this.workflowState.setWorkflow(workflow);

                    this.notificationState.showSuccessNotification(`Workflow updated`);
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

                    if(this.workflowState.isDeletingWorkflow) return;

                    this.workflowState.isDeletingWorkflow = true;

                    const config = {
                        data: {
                            store_id: this.store.id
                        }
                    }

                    await axios.delete(`/api/workflows/${this.workflowId}`, config);

                    hideModal();
                    await new Promise(resolve => setTimeout(resolve, 500));    //  Wait for modal to close

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

            for (let i = 0; i < listeners.length; i++) {
                let listener = listeners[i];
                this.changeHistoryState.listeners[listener] = this.setWorkflowForm;
            }

        }
    };

</script>
