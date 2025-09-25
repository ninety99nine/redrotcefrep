<template>

    <div class="max-w-2xl mx-auto pt-24 pb-40">

        <!-- Back Button -->
        <Button
            size="xs"
            type="light"
            class="mb-4"
            :leftIcon="MoveLeft"
            :action="navigateToshowDeliveryMethods">
            <span>Back</span>
        </Button>

        <div class="bg-gray-50 border border-gray-300 rounded-lg p-4 space-y-4 mb-4">

            <!-- Active -->
            <Switch
                size="xs"
                suffixText="Active"
                v-model="deliveryMethodForm.active"
                :errorText="formState.getFormError('active')"
                @change="deliveryMethodState.saveStateDebounced('Active status changed')"
            />

            <!-- Name -->
            <Input
                max="40"
                type="text"
                label="Name"
                placeholder="In-house Delivery"
                v-model="deliveryMethodForm.name"
                :errorText="formState.getFormError('name')"
                :skeleton="isLoadingStore || isLoadingDeliveryMethod"
                @input="deliveryMethodState.saveStateDebounced('Name changed')"
                tooltipContent="The delivery method name e.g In-house delivery, Self pickup or Dine in">
            </Input>

            <!-- Description -->
            <Input
                rows="2"
                type="textarea"
                label="Description"
                v-model="deliveryMethodForm.description"
                :errorText="formState.getFormError('description')"
                :skeleton="isLoadingStore || isLoadingDeliveryMethod"
                placeholder="Delivery using our in-house logistics service"
                @input="deliveryMethodState.saveStateDebounced('Instruction changed')"
                tooltipContent="A short and sweet description of how delivery/pickup will be accomplished">
            </Input>

            <div :class="[{ 'border border-gray-300 border-dashed rounded-lg': deliveryMethodForm.qualify_on_minimum_grand_total }]">

                <div :class="['space-y-4 transition-all duration-500', { 'bg-blue-50 p-4 rounded-lg': deliveryMethodForm.qualify_on_minimum_grand_total }]">

                    <!-- Qualify On Minimum Grand Total Checkbox -->
                    <Input
                        type="checkbox"
                        inputLabel="Qualify on minimum grand total"
                        :skeleton="isLoadingStore || isLoadingDeliveryMethod"
                        v-model="deliveryMethodForm.qualify_on_minimum_grand_total"
                        :errorText="formState.getFormError('qualify_on_minimum_grand_total')"
                        @change="deliveryMethodState.saveStateDebounced('Qualify on minimum grand total changed')"
                        inputDescription="Customer carts must qualify with a minimum grand total before placing an order">
                    </Input>

                    <!-- Minimum Grand Total Input -->
                    <Input
                        type="money"
                        placeholder="100"
                        label="Minimum Grand Total"
                        :currency="store?.currency"
                        v-model="deliveryMethodForm.minimum_grand_total"
                        :skeleton="isLoadingStore || isLoadingDeliveryMethod"
                        v-if="deliveryMethodForm.qualify_on_minimum_grand_total"
                        :errorText="formState.getFormError('minimum_grand_total')"
                        @input="deliveryMethodState.saveStateDebounced('Minimum grand total changed')">
                    </Input>

                </div>

            </div>

            <div :class="[{ 'border border-gray-300 border-dashed rounded-lg': deliveryMethodForm.offer_free_delivery_on_minimum_grand_total }]">

                <div :class="['space-y-4 transition-all duration-500', { 'bg-blue-50 p-4 rounded-lg': deliveryMethodForm.offer_free_delivery_on_minimum_grand_total }]">

                    <!-- Qualify On Minimum Grand Total Checkbox -->
                    <Input
                        type="checkbox"
                        :skeleton="isLoadingStore || isLoadingDeliveryMethod"
                        inputLabel="Offer free delivery on minimum grand total"
                        v-model="deliveryMethodForm.offer_free_delivery_on_minimum_grand_total"
                        :errorText="formState.getFormError('offer_free_delivery_on_minimum_grand_total')"
                        inputDescription="Customer carts must qualify with a minimum grand total to claim free delivery"
                        @change="deliveryMethodState.saveStateDebounced('Offer free delivery on minimum grand total changed')">
                    </Input>

                    <!-- Minimum Grand Total Input -->
                    <Input
                        type="money"
                        placeholder="100"
                        label="Minimum Grand Total"
                        :currency="store?.currency"
                        :skeleton="isLoadingStore || isLoadingDeliveryMethod"
                        v-model="deliveryMethodForm.free_delivery_minimum_grand_total"
                        v-if="deliveryMethodForm.offer_free_delivery_on_minimum_grand_total"
                        :errorText="formState.getFormError('free_delivery_minimum_grand_total')"
                        @input="deliveryMethodState.saveStateDebounced('Minimum grand total changed')">
                    </Input>

                </div>

            </div>

            <div :class="[{ 'border border-gray-300 border-dashed rounded-lg': deliveryMethodForm.set_daily_order_limit }]">

                <div :class="['space-y-4 transition-all duration-500', { 'bg-blue-50 p-4 rounded-lg': deliveryMethodForm.set_daily_order_limit }]">

                    <!-- Qualify On Minimum Grand Total Checkbox -->
                    <Input
                        type="checkbox"
                        inputLabel="Set daily order limit"
                        v-model="deliveryMethodForm.set_daily_order_limit"
                        :skeleton="isLoadingStore || isLoadingDeliveryMethod"
                        :errorText="formState.getFormError('set_daily_order_limit')"
                        inputDescription="Orders placed must not exceed the daily limit specified"
                        @change="deliveryMethodState.saveStateDebounced('Set daily order limit changed')">
                    </Input>

                    <div
                        class="flex items-center space-x-2"
                        v-if="deliveryMethodForm.set_daily_order_limit">

                        <span class="text-sm">Accept no more than</span>

                        <!-- Minimum Grand Total Input -->
                        <Input
                            min="1"
                            class="w-24"
                            type="number"
                            placeholder="10"
                            v-model="deliveryMethodForm.daily_order_limit"
                            :skeleton="isLoadingStore || isLoadingDeliveryMethod"
                            :errorText="formState.getFormError('daily_order_limit')"
                            @input="deliveryMethodState.saveStateDebounced('Daily order limit changed')">
                        </Input>

                        <span class="text-sm">orders per day</span>

                    </div>

                </div>

            </div>

        </div>

        <div class="bg-gray-50 border border-gray-300 rounded-lg p-4 space-y-4">

            <!-- Charge Fee -->
            <Switch
                size="xs"
                suffixText="Charge fee"
                v-model="deliveryMethodForm.charge_fee"
                :errorText="formState.getFormError('charge_fee')"
                @change="deliveryMethodState.saveStateDebounced('Charge fee status changed')"
            />

            <div class="grid grid-cols-2 gap-4">

                <!-- Fee Type -->
                <Select
                    width="w-full"
                    :search="false"
                    label="Fee type"
                    :options="feeTypes"
                    v-model="deliveryMethodForm.fee_type">
                </Select>

                <Input
                    type="money"
                    label="Flat fee"
                    placeholder="100.00"
                    :currency="store?.currency"
                    v-model="deliveryMethodForm.flat_fee_rate"
                    v-if="deliveryMethodForm.fee_type == 'flat fee'"
                    :errorText="formState.getFormError('flat_fee_rate')"
                    :skeleton="isLoadingStore || isLoadingDeliveryMethod"
                    @input="deliveryMethodState.saveStateDebounced('Flat fee rate changed')"
                    tooltipContent="Set the flat fee amount (This is the flat fee that will be applied as a charge)">
                </Input>

                <Input
                    placeholder="10"
                    type="percentage"
                    label="Percentage fee rate"
                    v-model="deliveryMethodForm.percentage_fee_rate"
                    :skeleton="isLoadingStore || isLoadingDeliveryMethod"
                    v-if="deliveryMethodForm.fee_type == 'percentage fee'"
                    :errorText="formState.getFormError('percentage_fee_rate')"
                    @input="deliveryMethodState.saveStateDebounced('Percentage fee rate changed')"
                    tooltipContent="Set the percentage fee amount (This is the percentage fee that will be applied as a charge)">
                </Input>

            </div>

            <FeeByWeight v-if="deliveryMethodForm.fee_type == 'fee by weight'"></FeeByWeight>
            <FeeByDistance v-if="deliveryMethodForm.fee_type == 'fee by distance'"></FeeByDistance>
            <FeeByPostalCode v-if="deliveryMethodForm.fee_type == 'fee by postal code'"></FeeByPostalCode>

        </div>

        <div
            v-if="deliveryMethod"
            :class="['flex items-center justify-between space-x-4 overflow-hidden rounded-lg p-4 border mt-4', isLoadingStore || isLoadingDeliveryMethod ? 'border-gray-300 bg-gray-50' : 'border-red-300 bg-red-50']">

            <div class="space-y-2">
                <p>Delete <span class="font-bold text-black">{{ deliveryMethod.name }}</span>?</p>
            </div>

            <div class="flex justify-end">

                <Modal
                    triggerType="danger"
                    approveType="danger"
                    :approveLeftIcon="Trash2"
                    triggerText="Delete Delivery Method"
                    approveText="Delete Delivery Method"
                    :approveAction="deleteDeliveryMethod"
                    :triggerLoading="isDeletingDeliveryMethod"
                    :approveLoading="isDeletingDeliveryMethod">

                    <template #content>
                        <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">Confirm Delete</p>
                        <p class="mb-8">Are you sure you want to delete <span class="font-bold text-black">{{ deliveryMethod.name }}</span>?</p>
                    </template>

                </Modal>

            </div>

        </div>

    </div>

</template>

<script>

    import Modal from '@Partials/Modal.vue';
    import Input from '@Partials/Input.vue';
    import Switch from '@Partials/Switch.vue';
    import Select from '@Partials/Select.vue';
    import Button from '@Partials/Button.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { Info, Trash2, MoveLeft } from 'lucide-vue-next';
    import FeeByWeight from '@Pages/settings/delivery-methods/_components/FeeByWeight.vue';
    import FeeByDistance from '@Pages/settings/delivery-methods/_components/FeeByDistance.vue';
    import FeeByPostalCode from '@Pages/settings/delivery-methods/_components/FeeByPostalCode.vue';

    export default {
        inject: ['formState', 'storeState', 'deliveryMethodState', 'changeHistoryState', 'notificationState'],
        components: {
            Info, Modal, Input, Switch, Select, Button, Skeleton, FeeByWeight, FeeByDistance, FeeByPostalCode
        },
        data() {
            return {
                Trash2,
                MoveLeft,
                feeTypes: [
                    { label: 'Flat fee', value: 'flat fee' },
                    { label: 'Percentage fee', value: 'percentage fee' },
                    { label: 'Fee by weight', value: 'fee by weight' },
                    { label: 'Fee by distance', value: 'fee by distance' },
                    { label: 'Fee by postal code', value: 'fee by postal code' },
                ]
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
            isEditing() {
                return this.deliveryMethodId != null;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            deliveryMethodId() {
                return this.$route.params.delivery_method_id;
            },
            deliveryMethod() {
                return this.deliveryMethodState.deliveryMethod;
            },
            deliveryMethodForm() {
                return this.deliveryMethodState.deliveryMethodForm;
            },
            isLoadingDeliveryMethod() {
                return this.deliveryMethodState.isLoadingDeliveryMethod;
            },
            isDeletingDeliveryMethod() {
                return this.deliveryMethodState.isDeletingDeliveryMethod;
            }
        },
        methods: {
            setup() {
                this.deliveryMethodState.setDeliveryMethodForm(null, true);
                if(this.store && this.deliveryMethodId) this.showDeliveryMethod();
            },
            setActionButtons() {
                this.changeHistoryState.removeButtons();
                this.changeHistoryState.addDiscardButton();
                this.changeHistoryState.addActionButton(
                    this.isEditing ? 'Save Changes' : 'Add Delivery Method',
                    this.isEditing ? this.updateDeliveryMethod : this.createDeliveryMethod,
                    'primary',
                    null,
                );
            },
            async navigateToshowDeliveryMethods() {
                await this.$router.push({
                    name: 'show-delivery-methods',
                    query: {
                        store_id: this.store.id
                    }
                });
            },
            async showDeliveryMethod() {
                try {

                    this.deliveryMethodState.isLoadingDeliveryMethod = true;

                    let config = {
                        params: {
                            store_id: this.store.id
                        }
                    };

                    const response = await axios.get(`/api/delivery-methods/${this.deliveryMethodId}`, config);

                    const deliveryMethod = response.data;
                    this.deliveryMethodState.setDeliveryMethod(deliveryMethod);
                    this.deliveryMethodState.setDeliveryMethodForm(deliveryMethod, true);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching delivery method';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch delivery method:', error);
                } finally {
                    this.deliveryMethodState.isLoadingDeliveryMethod = false;
                }
            },
            async createDeliveryMethod() {

                try {

                    if(this.deliveryMethodState.isCreatingDeliveryMethod) return;

                    this.formState.hideFormErrors();

                    if(this.deliveryMethodForm.name == null || this.deliveryMethodForm.name.trim() === '') {
                        this.formState.setFormError('name', 'The name is required');
                    }

                    if(this.formState.hasErrors) {
                        return;
                    }

                    this.deliveryMethodState.isCreatingDeliveryMethod = true;
                    this.changeHistoryState.actionButtons[1].loading = true;

                    const data = {
                        ...this.deliveryMethodForm,
                        store_id: this.store.id,
                    }

                    const response = await axios.post(`/api/delivery-methods`, data);
                    const deliveryMethod = response.data.delivery_method;

                    this.deliveryMethodState.setDeliveryMethod(deliveryMethod);

                    this.notificationState.showSuccessNotification(`Delivery method created`);
                    this.deliveryMethodState.saveOriginalState('Original delivery method');

                    this.navigateToshowDeliveryMethods();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while creating delivery method';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to create delivery method:', error);
                } finally {
                    this.deliveryMethodState.isCreatingDeliveryMethod = false;
                    this.changeHistoryState.actionButtons[1].loading = false;
                }

            },
            async updateDeliveryMethod() {

                try {

                    if(this.deliveryMethodState.isUpdatingDeliveryMethod) return;

                    this.formState.hideFormErrors();

                    if(this.deliveryMethodForm.name == null || this.deliveryMethodForm.name.trim() === '') {
                        this.formState.setFormError('name', 'The name is required');
                    }

                    if(this.formState.hasErrors) {
                        return;
                    }

                    this.deliveryMethodState.isUpdatingDeliveryMethod = true;
                    this.changeHistoryState.actionButtons[1].loading = true;

                    const data = {
                        ...this.deliveryMethodForm,
                        store_id: this.store.id
                    }

                    const response = await axios.put(`/api/delivery-methods/${this.deliveryMethodId}`, data);
                    const deliveryMethod = response.data.delivery_method;

                    this.deliveryMethodState.setDeliveryMethod(deliveryMethod);
                    await this.uploadImages();

                    this.notificationState.showSuccessNotification(`Delivery method updated`);
                    this.deliveryMethodState.saveOriginalState('Original delivery method');

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating delivery method';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update delivery method:', error);
                } finally {
                    this.deliveryMethodState.isUpdatingDeliveryMethod = false;
                    this.changeHistoryState.actionButtons[1].loading = false;
                }

            },
            async deleteDeliveryMethod(hideModal) {

                try {

                    if(this.deliveryMethodState.isDeletingDeliveryMethod) return;

                    this.deliveryMethodState.isDeletingDeliveryMethod = true;

                    const config = {
                        data: {
                            store_id: this.store.id
                        }
                    }

                    await axios.delete(`/api/delivery-methods/${this.deliveryMethodId}`, config);

                    hideModal();
                    await new Promise(resolve => setTimeout(resolve, 500));    //  Wait for modal to close

                    this.notificationState.showSuccessNotification('Delivery method deleted');

                    await this.navigateToshowDeliveryMethods();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting delivery method';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete delivery method:', error);
                    hideModal();
                } finally {
                    this.deliveryMethodState.isDeletingDeliveryMethod = false;
                }

            },
            setDeliveryMethodForm(deliveryMethodForm) {
                this.deliveryMethodState.deliveryMethodForm = deliveryMethodForm;
            }
        },
        beforeUnmount() {
            this.deliveryMethodState.reset();
        },
        created() {

            this.setup();
            this.setActionButtons();

            const listeners = ['undo', 'redo', 'jumpToHistory', 'resetHistoryToCurrent', 'resetHistoryToOriginal'];

            for (let i = 0; i < listeners.length; i++) {
                let listener = listeners[i];
                this.changeHistoryState.listeners[listener] = this.setDeliveryMethodForm;
            }

        }
    };

</script>
