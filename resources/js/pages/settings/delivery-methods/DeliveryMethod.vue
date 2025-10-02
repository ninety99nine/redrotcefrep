<template>

    <div class="max-w-2xl mx-auto pt-24 pb-40">

        <!-- Back Button -->
        <Button
            size="xs"
            type="light"
            class="mb-4"
            :leftIcon="MoveLeft"
            :action="navigateToShowDeliveryMethods">
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

            <div :class="[{ 'border border-gray-300 border-dashed rounded-lg': deliveryMethodForm.set_daily_order_limit }]">

                <div :class="['space-y-4 transition-all duration-500', { 'bg-blue-50 p-4 rounded-lg': deliveryMethodForm.set_daily_order_limit }]">

                    <!-- Set Daily Order Limit Checkbox -->
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

                        <!-- Daily Order Limit Input -->
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

        </div>

        <div class="bg-gray-50 border border-gray-300 rounded-lg p-4 space-y-4 mb-4">

            <h1 class="text-md font-bold mb-4">Fee</h1>

            <!-- Charge Fee -->
            <Switch
                size="xs"
                suffixText="Charge fee"
                v-model="deliveryMethodForm.charge_fee"
                :errorText="formState.getFormError('charge_fee')"
                @change="deliveryMethodState.saveStateDebounced('Charge fee status changed')"
            />

            <template v-if="deliveryMethodForm.charge_fee">

                <div class="border-b border-gray-300 border-dashed space-y-4 pb-4">

                    <div class="grid grid-cols-2 gap-4">

                        <!-- Fee Type -->
                        <Select
                            class="w-full"
                            :search="false"
                            label="Fee type"
                            :options="feeTypes"
                            v-model="deliveryMethodForm.fee_type"
                            :errorText="formState.getFormError('fee_type')">
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

            </template>

        </div>

        <div class="bg-gray-50 border border-gray-300 rounded-lg p-4 space-y-4 mb-4">

            <h1 class="text-md font-bold mb-4">Address</h1>

            <!-- Capture Address Information Checkbox -->
            <Input
                type="checkbox"
                inputLabel="Capture address information"
                v-model="deliveryMethodForm.ask_for_an_address"
                :skeleton="isLoadingStore || isLoadingDeliveryMethod"
                :errorText="formState.getFormError('ask_for_an_address')"
                @change="deliveryMethodState.saveStateDebounced('Capture address information changed')"
                :disabled="['fee by distance', 'fee by postal code'].includes(deliveryMethodForm.fee_type)">
                <template #inputDescription>
                    <p
                        class="text-xs text-green-600 mt-1"
                        v-if="['fee by distance', 'fee by postal code'].includes(deliveryMethodForm.fee_type)">
                        The address information will be captured to support delivery fee by
                        <template v-if="deliveryMethodForm.fee_type == 'fee by distance'">distance</template>
                        <template v-else-if="deliveryMethodForm.fee_type == 'fee by postal code'">postal code</template>
                    </p>
                    <p v-else class="text-xs text-gray-500 mt-1">Ask customers to provide their address information for delivery</p>
                </template>
            </Input>

            <!-- Capture Address Location On Map Checkbox -->
            <Input
                type="checkbox"
                inputLabel="Capture address location on map"
                v-model="deliveryMethodForm.pin_location_on_map"
                :skeleton="isLoadingStore || isLoadingDeliveryMethod"
                :errorText="formState.getFormError('pin_location_on_map')"
                @change="deliveryMethodState.saveStateDebounced('Capture address location on map changed')"
                :disabled="['fee by distance', 'fee by postal code'].includes(deliveryMethodForm.fee_type)">
                <template #inputDescription>
                    <p
                        class="text-xs text-green-600 mt-1"
                        v-if="['fee by distance', 'fee by postal code'].includes(deliveryMethodForm.fee_type)">
                        The address location will be captured to support delivery fee by
                        <template v-if="deliveryMethodForm.fee_type == 'fee by distance'">distance</template>
                        <template v-else-if="deliveryMethodForm.fee_type == 'fee by postal code'">postal code</template>
                    </p>
                    <p v-else class="text-xs text-gray-500 mt-1">Ask customers to show their address on the map for better accuracy</p>
                </template>
            </Input>

        </div>

        <div class="bg-gray-50 border border-gray-300 rounded-lg p-4 space-y-4 mb-4">

            <h1 class="text-md font-bold mb-4">Schedule</h1>

            <!-- Set schedule -->
            <Switch
                size="xs"
                suffixText="Set schedule"
                v-model="deliveryMethodForm.set_schedule"
                :errorText="formState.getFormError('set_schedule')"
                @change="deliveryMethodState.saveStateDebounced('Set schedule fee status changed')"
            />

            <template v-if="deliveryMethodForm.set_schedule">

                <!-- Schedule Type -->
                <Select
                    class="w-full"
                    :search="false"
                    label="Schedule type"
                    :options="scheduleTypes"
                    v-model="deliveryMethodForm.schedule_type"
                    :errorText="formState.getFormError('schedule_type')"
                    tooltipContent="Select the schedule type e.g Whether customers should specify a delivery date or both delivery date and time">
                </Select>

                <div v-if="deliveryMethodForm.schedule_type == 'date and time'" class="space-y-4">

                    <!-- Auto generate time slots Checkbox -->
                    <Input
                        type="checkbox"
                        inputLabel="Auto generate time slots"
                        v-model="deliveryMethodForm.auto_generate_time_slots"
                        :skeleton="isLoadingStore || isLoadingDeliveryMethod"
                        :errorText="formState.getFormError('auto_generate_time_slots')"
                        inputDescription="Automatically generates time options for customers to choose from"
                        @change="deliveryMethodState.saveStateDebounced('Auto generate time slots changed')">
                    </Input>

                    <div
                        class="grid grid-cols-2 gap-4"
                        v-if="deliveryMethodForm.auto_generate_time_slots">

                        <!-- Time Slot Interval Value Input -->
                        <Input
                            min="1"
                            type="number"
                            placeholder="1"
                            label="Interval"
                            v-model="deliveryMethodForm.time_slot_interval_value"
                            :skeleton="isLoadingStore || isLoadingDeliveryMethod"
                            :errorText="formState.getFormError('time_slot_interval_value')"
                            @input="deliveryMethodState.saveStateDebounced('Time slot interval value changed')"
                            tooltipContent="Set the interval that should be used to auto generate the time slots">
                        </Input>

                        <!-- Time Slot Interval Unit Input -->
                        <Select
                            class="w-full"
                            :search="false"
                            label="Interval Unit"
                            :options="timeSlotIntervalUnits"
                            v-model="deliveryMethodForm.time_slot_interval_unit"
                            :errorText="formState.getFormError('time_slot_interval_unit')"
                            tooltipContent="Set the interval unit that should be used to auto generate the time slots">
                        </Select>

                    </div>

                </div>

                <OperationalHours></OperationalHours>

                <div class="space-y-4">

                    <!-- Same Day Delivery Checkbox -->
                    <Input
                        type="checkbox"
                        inputLabel="Same day delivery"
                        v-model="deliveryMethodForm.same_day_delivery"
                        :skeleton="isLoadingStore || isLoadingDeliveryMethod"
                        :errorText="formState.getFormError('same_day_delivery')"
                        inputDescription="Customers must place orders for delivery on the same day"
                        @change="deliveryMethodState.saveStateDebounced('Same day delivery changed')">
                    </Input>

                    <template v-if="!deliveryMethodForm.same_day_delivery">

                        <!-- Require Minimum Notice For Orders Checkbox -->
                        <Input
                            type="checkbox"
                            inputLabel="Require orders in advance"
                            :skeleton="isLoadingStore || isLoadingDeliveryMethod"
                            v-model="deliveryMethodForm.require_minimum_notice_for_orders"
                            :errorText="formState.getFormError('require_minimum_notice_for_orders')"
                            @change="deliveryMethodState.saveStateDebounced('Require minimum notice for orders changed')">
                            <template #inputDescription>
                                <p class="text-xs text-gray-500 mt-1">Customers can only place orders a few hours or days before the delivery date to allow time for processing</p>
                            </template>
                            <template #inputOuterDescription>

                                <div :class="[deliveryMethodForm.require_minimum_notice_for_orders ? 'h-12 mt-2' : 'h-0 mt-0 overflow-hidden', 'flex items-center text-gray-500 space-x-2 transition-all duration-500']">

                                    <span class="text-xs text-gray-500">
                                        Allow delivery at least
                                    </span>

                                    <!-- Earliest Delivery Time Value Input -->
                                    <Input
                                        min="1"
                                        @click.stop
                                        class="w-28"
                                        type="number"
                                        placeholder="1"
                                        :skeleton="isLoadingStore || isLoadingDeliveryMethod"
                                        v-model="deliveryMethodForm.earliest_delivery_time_value"
                                        :errorText="formState.getFormError('earliest_delivery_time_value')"
                                        @input="deliveryMethodState.saveStateDebounced('Earliest delivery time value changed')">
                                        <template v-if="deliveryMethodForm.schedule_type == 'date'" #suffix>
                                            <span class="w-20 ml-2">{{ deliveryMethodForm.earliest_delivery_time_value == 1 ? 'Day' : 'Days' }}</span>
                                        </template>
                                    </Input>

                                    <!-- Earliest Delivery Time Unit Select -->
                                    <Select
                                        class="w-28"
                                        :search="false"
                                        :options="earliestDeliveryTimeUnits"
                                        v-model="deliveryMethodForm.earliest_delivery_time_unit"
                                        v-if="deliveryMethodForm.schedule_type == 'date and time'"
                                        :errorText="formState.getFormError('earliest_delivery_time_unit')">
                                    </Select>

                                    <span class="text-xs text-gray-500">
                                        after order date
                                        <template v-if="deliveryMethodForm.schedule_type == 'date and time' && deliveryMethodForm.earliest_delivery_time_unit == 'hour'">({{ deliveryMethodForm.earliest_delivery_time_value }} {{ deliveryMethodForm.earliest_delivery_time_value == '1' ? 'hour' : 'hours'}} notice)</template>
                                        <template v-else>({{ deliveryMethodForm.earliest_delivery_time_value }} {{ deliveryMethodForm.earliest_delivery_time_value == '1' ? 'day' : 'days'}} notice)</template>
                                    </span>

                                </div>
                            </template>
                        </Input>

                        <!-- Restrict Maximum Notice For Orders Checkbox -->
                        <Input
                            type="checkbox"
                            :skeleton="isLoadingStore || isLoadingDeliveryMethod"
                            inputLabel="Limit how far in advance orders can be placed"
                            v-model="deliveryMethodForm.restrict_maximum_notice_for_orders"
                            :errorText="formState.getFormError('restrict_maximum_notice_for_orders')"
                            @change="deliveryMethodState.saveStateDebounced('Restrict maximum notice for orders changed')">
                            <template #inputDescription>
                                <p class="text-xs text-gray-500 mt-1">Customers can only place orders if the delivery date is within a set number of days to avoid delivery days that are too far</p>
                            </template>
                            <template #inputOuterDescription>

                                <div :class="[deliveryMethodForm.restrict_maximum_notice_for_orders ? 'h-12 mt-2' : 'h-0 mt-0 overflow-hidden', 'flex items-center text-gray-500 space-x-2 transition-all duration-500']">

                                    <span class="text-xs text-gray-500">
                                        Restrict orders more than
                                    </span>

                                    <!-- Latest Delivery Time Value Input -->
                                    <Input
                                        min="1"
                                        @click.stop
                                        class="w-28"
                                        type="number"
                                        placeholder="1"
                                        :skeleton="isLoadingStore || isLoadingDeliveryMethod"
                                        v-model="deliveryMethodForm.latest_delivery_time_value"
                                        :errorText="formState.getFormError('latest_delivery_time_value')"
                                        @input="deliveryMethodState.saveStateDebounced('Latest delivery time value changed')">
                                        <template #suffix>
                                            <span class="w-20 ml-2">{{ deliveryMethodForm.latest_delivery_time_value == 1 ? 'Day' : 'Days' }}</span>
                                        </template>
                                    </Input>

                                    <span class="text-xs text-gray-500">
                                        from the delivery date
                                    </span>

                                </div>
                            </template>
                        </Input>

                    </template>

                    <ScheduleSummary></ScheduleSummary>

                </div>

            </template>

        </div>

        <div class="bg-gray-50 border border-gray-300 rounded-lg p-4 space-y-4 mb-4">

            <h1 class="text-md font-bold mb-4">Questions</h1>

            <DataCollectionFields></DataCollectionFields>

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
    import ScheduleSummary from '@Pages/settings/delivery-methods/_components/ScheduleSummary.vue';
    import FeeByPostalCode from '@Pages/settings/delivery-methods/_components/FeeByPostalCode.vue';
    import OperationalHours from '@Pages/settings/delivery-methods/_components/OperationalHours.vue';
    import DataCollectionFields from '@Pages/settings/delivery-methods/_components/DataCollectionFields.vue';

    export default {
        inject: ['formState', 'storeState', 'deliveryMethodState', 'changeHistoryState', 'notificationState'],
        components: {
            Info, Modal, Input, Switch, Select, Button, Skeleton, FeeByWeight, FeeByDistance,
            ScheduleSummary, FeeByPostalCode, OperationalHours, DataCollectionFields
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
                ],
                scheduleTypes: [
                    { label: 'Date', value: 'date' },
                    { label: 'Date And Time', value: 'date and time' },
                ],
                lastAskForAnAddressValue: null,
                lastPinLocationOnMapValue: null
            }
        },
        watch: {
            store(newValue, oldValue) {
                if(!oldValue && newValue) {
                    this.setup();
                }
            },
            'deliveryMethodForm.fee_type'(newValue) {
                if(['fee by distance', 'fee by postal code'].includes(newValue)) {

                    this.lastAskForAnAddressValue = this.deliveryMethodForm.ask_for_an_address;
                    this.deliveryMethodForm.ask_for_an_address = true;

                    this.lastPinLocationOnMapValue = this.deliveryMethodForm.pin_location_on_map;
                    this.deliveryMethodForm.pin_location_on_map = true;

                }else{

                    this.deliveryMethodForm.ask_for_an_address = this.lastAskForAnAddressValue;
                    this.deliveryMethodForm.pin_location_on_map = this.lastPinLocationOnMapValue;

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
            },
            timeSlotIntervalUnits() {
                return [
                    { label: (this.deliveryMethodForm.time_slot_interval_value == '1' ? 'Minute' : 'Minutes'), value: 'minute' },
                    { label: (this.deliveryMethodForm.time_slot_interval_value == '1' ? 'Hour' : 'Hours'), value: 'hour' },
                ];
            },
            earliestDeliveryTimeUnits() {
                return [
                    { label: (this.deliveryMethodForm.earliest_delivery_time_unit == '1' ? 'Hour' : 'Hours'), value: 'hour' },
                    { label: (this.deliveryMethodForm.earliest_delivery_time_unit == '1' ? 'Day' : 'Days'), value: 'day' },
                ];
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
            async navigateToShowDeliveryMethods() {
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

                    this.navigateToShowDeliveryMethods();

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

                    await this.navigateToShowDeliveryMethods();

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
