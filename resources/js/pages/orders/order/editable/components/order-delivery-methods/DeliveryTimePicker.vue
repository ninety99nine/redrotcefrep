<template>

    <div>

        <ShineEffect v-if="isLoading">

            <div class="flex items-center space-x-2">

                <!-- Clock Icon -->
                <svg class="w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>

                <Skeleton width="w-1/2"></Skeleton>

            </div>

        </ShineEffect>

        <!-- Timeslot Select -->
        <Select
            v-else
            class="w-full"
            :search="false"
            v-model="localModelValue"
            noResultsText="No timeslots found"
            placeholder="Select delivery time"
            :options="availableTimeSlotOptions">
        </Select>

    </div>

</template>

<script>

    import dayjs from 'dayjs';
    import debounce from 'lodash/debounce';
    import Select from '@Partials/Select.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import ShineEffect from '@Partials/ShineEffect.vue';

    export default {
        inject: ['formState', 'storeState', 'deliveryMethodState', 'notificationState'],
        components: {
            Select, Skeleton, ShineEffect
        },
        props: {
            modelValue: {
                type: String,
            },
            form: {
                type: Object,
            },
            deliveryMethod: {
                type: Object,
            },
            deliveryDate: {
                type: String,
            },
            showAllDates: {
                type: Boolean,
                default: false
            },
            showAllTimeslots: {
                type: Boolean,
                default: false
            },
            autoSelectFirstTimeslot: {
                type: Boolean,
                value: true
            }
        },
        emits: ['update:modelValue', 'isLoading', 'change', 'scheduleOptions'],
        data() {
            return {
                isLoading: false,
                apiRequestCounter: 0,
                debounceInstance: null,
                availableTimeSlotOptions: [],
                localModelValue: this.modelValue,
            };
        },
        watch: {
            modelValue(newValue) {
                this.localModelValue = newValue;
            },
            localModelValue(newValue) {
                this.$emit('update:modelValue', newValue);
                this.$emit('change', newValue);
            },
            deliveryDate(newValue) {
                if(newValue) this.debouncedShowDeliveryMethodScheduleOptions();
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            deliveryMethodForm() {
                return this.deliveryMethodState.deliveryMethodForm;
            },
        },
        methods: {
            setIsLoading(status) {
                this.isLoading = status;
                this.$emit('isLoading', status);
            },
            parseForm() {
                return {
                    store_id: this.store.id,
                    show_all_dates: this.showAllDates,
                    show_all_timeslots: this.showAllTimeslots,
                    delivery_date: dayjs(this.deliveryDate).format('YYYY-MM-DD'),
                    schedule_type: this.deliveryMethod ? this.deliveryMethod.schedule_type : this.deliveryMethodForm.schedule_type,
                    daily_order_limit: this.deliveryMethod ? this.deliveryMethod.daily_order_limit : this.deliveryMethodForm.daily_order_limit,
                    same_day_delivery: this.deliveryMethod ? this.deliveryMethod.same_day_delivery : this.deliveryMethodForm.same_day_delivery,
                    operational_hours: this.deliveryMethod ? this.deliveryMethod.operational_hours : this.deliveryMethodForm.operational_hours,
                    set_daily_order_limit: this.deliveryMethod ? this.deliveryMethod.set_daily_order_limit : this.deliveryMethodForm.set_daily_order_limit,
                    time_slot_interval_unit: this.deliveryMethod ? this.deliveryMethod.time_slot_interval_unit : this.deliveryMethodForm.time_slot_interval_unit,
                    auto_generate_time_slots: this.deliveryMethod ? this.deliveryMethod.auto_generate_time_slots : this.deliveryMethodForm.auto_generate_time_slots,
                    time_slot_interval_value: this.deliveryMethod ? this.deliveryMethod.time_slot_interval_value : this.deliveryMethodForm.time_slot_interval_value,
                    latest_delivery_time_value: this.deliveryMethod ? this.deliveryMethod.latest_delivery_time_value : this.deliveryMethodForm.latest_delivery_time_value,
                    earliest_delivery_time_unit: this.deliveryMethod ? this.deliveryMethod.earliest_delivery_time_unit : this.deliveryMethodForm.earliest_delivery_time_unit,
                    earliest_delivery_time_value: this.deliveryMethod ? this.deliveryMethod.earliest_delivery_time_value : this.deliveryMethodForm.earliest_delivery_time_value,
                    require_minimum_notice_for_orders: this.deliveryMethod ? this.deliveryMethod.require_minimum_notice_for_orders : this.deliveryMethodForm.require_minimum_notice_for_orders,
                    restrict_maximum_notice_for_orders: this.deliveryMethod ? this.deliveryMethod.restrict_maximum_notice_for_orders : this.deliveryMethodForm.restrict_maximum_notice_for_orders
                };
            },
            async showDeliveryMethodScheduleOptions() {

                try {

                    const data = this.parseForm();

                    const currentRequest = ++this.apiRequestCounter;

                    const response = await axios.post(`/api/delivery-methods/schedule-options`, data);

                    if (currentRequest !== this.apiRequestCounter) {
                        return;
                    }

                    const scheduleOptions = response.data;

                    const availableTimeSlots = scheduleOptions.available_time_slots;

                    if (this.autoSelectFirstTimeslot && availableTimeSlots.length && !availableTimeSlots.includes(this.localModelValue)) {
                        this.localModelValue = availableTimeSlots[0];
                    }

                    this.availableTimeSlotOptions = availableTimeSlots.map(function(availableTimeSlot) {
                        return {
                            label: availableTimeSlot,
                            value: availableTimeSlot
                        }
                    });

                    this.$emit('scheduleOptions', scheduleOptions);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching schedule options';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch schedule options:', error);
                } finally {
                    this.setIsLoading(false);
                }

            },
            debouncedShowDeliveryMethodScheduleOptions() {
                // If debounce instance already exists, cancel it
                if (this.debounceInstance) {
                    this.debounceInstance.cancel();
                }

                this.setIsLoading(true);
                this.availableTimeSlotOptions = [];

                // Create a new debounce instance
                this.debounceInstance = debounce(() => {
                    this.showDeliveryMethodScheduleOptions(); // Execute request
                }, 1000);

                // Trigger the debounce function
                this.debounceInstance();
            },
        },
        created() {
            this.setIsLoading(true);
            this.showDeliveryMethodScheduleOptions();
        },
    };
</script>
