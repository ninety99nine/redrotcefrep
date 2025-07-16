<template>

    <div v-if="deliveryMethodRequiresSchedule" class="space-y-4 pt-4 px-4">

        <div class="space-y-2">
            <p class="font-bold text-md">Schedule</p>
            <div>
                <p class="text-sm text-gray-500">
                    <template v-if="deliveryMethod.schedule_type == 'date'">
                        <template v-if="isCreating">Specify the delivery date for this order</template>
                        <template v-else>The delivery date for this order</template>
                    </template>
                    <template v-else-if="deliveryMethod.schedule_type == 'date and time'">
                        <template v-if="isCreating">Specify the delivery date and time for this order</template>
                        <template v-else>The delivery date and time for this order</template>
                    </template>
                </p>
            </div>
        </div>

        <div v-if="originalDeliveryDate" class="space-y-2">

            <div class="flex items-center space-x-4">

                <Switch
                    size="xs"
                    suffixText="Change delivery schedule"
                    v-model="shoppingCartForm.schedule.change_delivery_schedule"
                    @change="orderState.saveStateDebounced(`Change delivery schedule (${ shoppingCartForm.schedule.change_delivery_schedule ? 'On' : 'Off' })`)">
                </Switch>

                <Input
                    type="checkbox"
                    v-model="shoppingCartForm.schedule.show_all_dates"
                    v-if="shoppingCartForm.schedule.change_delivery_schedule"
                    :inputLabel="deliveryMethod.schedule_type == 'date' ? 'Show all dates' : 'Show all dates and timeslots'"
                    @change="orderState.saveStateDebounced(`Show all ${ deliveryMethod.schedule_type == 'date' ? 'dates' : 'dates and timeslots' } (${ shoppingCartForm.schedule.show_all_dates ? 'On' : 'Off' })`)">
                </Input>

            </div>

            <Alert
                type="warning"
                :dismissable="true"
                title="Delivery date passed"
                v-if="shoppingCartForm.schedule.change_delivery_schedule && deliveryDateIsPast"
                description="Update the delivery date to a future date unless the order has already been delivered"
            />

        </div>

        <template v-if="!originalDeliveryDate || (originalDeliveryDate && shoppingCartForm.schedule.change_delivery_schedule)">

            <!-- Date Picker -->
            <div>
                <DeliveryDatePicker
                    :deliveryMethod="deliveryMethod"
                    @isLoading="onIsLoadingDateOptions"
                    v-model="shoppingCartForm.delivery_date"
                    :showAllDates="shoppingCartForm.schedule.show_all_dates"
                    @change="this.orderState.saveStateDebounced('Delivery date changed')">
                </DeliveryDatePicker>
            </div>

            <!-- Time Picker -->
            <div v-if="shoppingCartForm.delivery_date && !isLoadingDateOptions && deliveryMethod.schedule_type == 'date and time'">
                <DeliveryTimePicker
                    class="w-full"
                    :deliveryMethod="deliveryMethod"
                    :autoSelectFirstTimeslot="isCreating"
                    v-model="shoppingCartForm.delivery_timeslot"
                    :deliveryDate="shoppingCartForm.delivery_date"
                    :showAllDates="shoppingCartForm.schedule.show_all_dates"
                    :showAllTimeslots="shoppingCartForm.schedule.show_all_timeslots"
                    @change="this.orderState.saveStateDebounced('Delivery time changed')">
                </DeliveryTimePicker>
            </div>

            <!-- Error Message -->
            <div
                :key="index"
                v-for="(shoppingCartDeliveryMethodScheduleIncompleteReason, index) in shoppingCartDeliveryMethodScheduleIncompleteReasons">

                <Skeleton v-if="isInspectingShoppingCart" width="w-1/3" :shine="true"></Skeleton>
                <div v-else class="flex items-center space-x-1 md:space-x-2">
                    <svg class="w-4 h-4 md:w-6 md:h-6 flex-shrink-0 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    <span class="font-medium text-red-500 text-xs md:text-sm">{{ shoppingCartDeliveryMethodScheduleIncompleteReason }}</span>
                </div>

            </div>

        </template>

        <div v-if="shoppingCartForm.delivery_date || shoppingCartForm.delivery_timeslot" class="space-y-2 border-t border-b border-dashed py-4">
            <div v-if="shoppingCartForm.delivery_date" class="text-sm">
                <span>Delivery date: </span>
                <span class="font-bold">
                    {{ shoppingCartForm.delivery_date }} ({{ formattedShortWeekday(shoppingCartForm.delivery_date) }})
                </span>
            </div>

            <div v-if="shoppingCartForm.delivery_timeslot" class="text-sm">
                <span>Delivery time: </span>
                <span class="font-bold">
                    {{ shoppingCartForm.delivery_timeslot }}
                </span>
            </div>
        </div>

    </div>

</template>

<script>

    import Alert from '@Partials/Alert.vue';
    import Input from '@Partials/Input.vue';
    import Switch from '@Partials/Switch.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { isPastDate } from '@Utils/dateUtils.js';
    import { formattedShortWeekday } from '@Utils/dateUtils.js';
    import DeliveryDatePicker from '@Pages/orders/order/editable/components/delivery-methods/DeliveryDatePicker.vue';
    import DeliveryTimePicker from '@Pages/orders/order/editable/components/delivery-methods/DeliveryTimePicker.vue';

    export default {
        inject: ['orderState', 'shoppingCartState'],
        components: {
            Alert, Input, Switch, Skeleton, DeliveryDatePicker, DeliveryTimePicker
        },
        props: {
            index: {
                type: Number
            },
            deliveryMethod: {
                type: Object
            }
        },
        data() {
            return {
                originalDeliveryDate: null,
                isLoadingDateOptions: false,
                originalDeliveryTimeslot: null,
                originalDeliveryDateIsPast: false
            }
        },
        watch: {
            'shoppingCartForm.schedule.change_delivery_schedule'(newVal) {
                if(newVal) {
                    if(this.originalDeliveryDateIsPast && !this.shoppingCartForm.schedule.show_all_dates) {
                        this.shoppingCartForm.delivery_date = null;
                        this.shoppingCartForm.delivery_timeslot = null;
                    }
                }else{
                    this.shoppingCartForm.delivery_date = this.originalDeliveryDate;
                    this.shoppingCartForm.delivery_timeslot = this.originalDeliveryTimeslot;
                }
            },
            'shoppingCartForm.schedule.show_all_dates'(newVal) {
                if(newVal) {
                    this.shoppingCartForm.delivery_date = this.originalDeliveryDate;
                    this.shoppingCartForm.delivery_timeslot = this.originalDeliveryTimeslot;
                }else if(this.originalDeliveryDateIsPast) {
                    this.shoppingCartForm.delivery_date = null;
                    this.shoppingCartForm.delivery_timeslot = null;
                }
                this.shoppingCartForm.schedule.show_all_timeslots = newVal;
            }
        },
        computed: {
            isEditting() {
                return this.$route.name === 'edit-order';
            },
            isCreating() {
                return this.$route.name === 'create-order';
            },
            shoppingCart() {
                return this.shoppingCartState.shoppingCart;
            },
            shoppingCartForm() {
                return this.shoppingCartState.shoppingCartForm;
            },
            deliveryDateIsPast() {
                return this.shoppingCartForm.delivery_date && isPastDate(this.shoppingCartForm.delivery_date);
            },
            isInspectingShoppingCart() {
                return this.orderState.isInspectingShoppingCart;
            },
            deliveryMethodRequiresSchedule() {
                return this.deliveryMethod.set_schedule;
            },
            shoppingCartDeliveryMethodOption() {
                return ((this.shoppingCart || {}).delivery_method_options || []).find((shoppingCartDeliveryMethodOption) => shoppingCartDeliveryMethodOption.id == this.deliveryMethod.id);
            },
            shoppingCartDeliveryMethodScheduleIncompleteReasons() {
                return ((this.shoppingCartDeliveryMethodOption || {}).schedule_incomplete_reasons || []);
            },
        },
        methods: {
            formattedShortWeekday: formattedShortWeekday,
            onIsLoadingDateOptions(isLoading) {
                this.isLoadingDateOptions = isLoading;
            }
        },
        created() {
            this.originalDeliveryDate = this.shoppingCartForm.delivery_date;
            this.originalDeliveryTimeslot = this.shoppingCartForm.delivery_timeslot;
            this.originalDeliveryDateIsPast = this.originalDeliveryDate && isPastDate(this.originalDeliveryDate);
        }
    };

</script>
