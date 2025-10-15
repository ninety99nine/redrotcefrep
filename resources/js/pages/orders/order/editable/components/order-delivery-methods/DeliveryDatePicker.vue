<template>

    <div>

        <ShineEffect v-if="isLoading">

            <div class="flex items-center space-x-2">

                <!-- Calendar Icon -->
                <svg class="w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>

                <Skeleton width="w-1/2"></Skeleton>

            </div>

        </ShineEffect>

        <!-- Datepicker -->
        <Datepicker
            v-else
            :key="key"
            :enableTimePicker="false"
            v-model="localModelValue"
            placeholder="Select delivery date"
            :minDate="showAllDates ? null : minDate"
            :maxDate="showAllDates ? null : maxDate"
            @change="(value) => $emit('change', value)"
            :disabledDates="showAllDates ? [] : disabledDates"
            :disabledWeekDays="showAllDates ? [] : disabledWeekDays">
        </Datepicker>

    </div>

</template>

<script>

    import debounce from 'lodash/debounce';
    import Skeleton from '@Partials/Skeleton.vue';
    import Datepicker from '@Partials/Datepicker.vue';
    import ShineEffect from '@Partials/ShineEffect.vue';
    import { formattedDate } from '@Utils/dateUtils.js';

    export default {
        inject: ['formState', 'storeState', 'deliveryMethodState', 'notificationState'],
        components: {
            Skeleton, Datepicker, ShineEffect
        },
        props: {
            modelValue: {
                type: String,
            },
            deliveryMethod: {
                type: Object,
            },
            showAllDates: {
                type: Boolean,
                default: false
            }
        },
        emits: ['update:modelValue', 'isLoading', 'change', 'scheduleOptions'],
        data() {
            return {
                minDate: null,
                maxDate: null,
                isLoading: false,
                disabledDates: [],
                apiRequestCounter: 0,
                disabledWeekDays: [],
                debounceInstance: null,
                localModelValue: this.modelValue
            };
        },
        watch: {
            modelValue(newValue) {
                this.localModelValue = newValue;
            },
            localModelValue(newValue) {
                this.$emit('update:modelValue', newValue);
            },
            ...[
                'schedule_type',
                'daily_order_limit',
                'same_day_delivery',
                'set_daily_order_limit',
                'time_slot_interval_unit',
                'auto_generate_time_slots',
                'time_slot_interval_value',
                'latest_delivery_time_value',
                'earliest_delivery_time_unit',
                'earliest_delivery_time_value',
                'require_minimum_notice_for_orders',
                'restrict_maximum_notice_for_orders',
            ].reduce((watchers, field) => {
                watchers[`deliveryMethodForm.${field}`] = function () {
                    this.debouncedShowDeliveryMethodScheduleOptions();
                };
                return watchers;
            }, {}),
            'deliveryMethodForm.operational_hours': {
                handler() {
                    this.debouncedShowDeliveryMethodScheduleOptions();
                },
                deep: true,
            },
        },
        computed: {
            key() {
                return (
                    this.minDate +
                    this.maxDate +
                    this.showAllDates +
                    this.disabledDates.length +
                    this.disabledWeekDays.length
                );
            },
            store() {
                return this.storeState.store;
            },
            deliveryMethodForm() {
                return this.deliveryMethodState.deliveryMethodForm;
            },
        },
        methods: {
            formattedDate,
            setIsLoading(status) {
                this.isLoading = status;
                this.$emit('isLoading', status);
            },
            parseForm() {
                return {
                    store_id: this.store.id,
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
                    restrict_maximum_notice_for_orders: this.deliveryMethod ? this.deliveryMethod.restrict_maximum_notice_for_orders : this.deliveryMethodForm.restrict_maximum_notice_for_orders,
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
                    this.minDate = this.formattedDate(scheduleOptions.min_date);
                    this.disabledWeekDays = scheduleOptions.days_of_week_disabled;
                    this.disabledDates = scheduleOptions.dates_disabled.map((date) => this.formattedDate(date));
                    this.maxDate = scheduleOptions.max_date ? this.formattedDate(scheduleOptions.max_date) : null;

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
