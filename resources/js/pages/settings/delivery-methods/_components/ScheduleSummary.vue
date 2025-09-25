<template>

    <div class="bg-white border border-gray-300 rounded-lg space-y-4">

        <!-- Schedule Summary -->
        <div
            @click="toggleScheduleSummary"
            :class="['space-y-2 p-4 cursor-pointer rounded-t-lg rounded-x-lg hover:bg-gray-50', showScheduleSummary ? 'border-b border-gray-300 border-dashed pb-4' : 'rounded-b-lg']">

            <div :class="['flex justify-between', { 'mb-4' : showScheduleSummary }]">
                <p class="font-bold text-md">Schedule Summary</p>

                <div class="hover:text-gray-500 active:text-gray-400">
                    <svg v-if="showScheduleSummary" class="w-6 h-6 cursor-pointer" @click="showScheduleSummary = false" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                    </svg>
                    <svg v-else @click="showScheduleSummary = true" class="w-6 h-6 cursor-pointer" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                </div>

            </div>

            <template v-if="showScheduleSummary">

                <template v-if="isLoadingDateOptions || isLoadingTimeOptions">
                    <div
                        :key="index"
                        class="flex space-x-2"
                        v-for="(_, index) in [1,2,3]">
                        <Skeleton width="w-2"></Skeleton>
                        <Skeleton width="w-1/2"></Skeleton>
                    </div>
                </template>

                <template v-else>
                    <div v-for="(scheduleKeyPoint, index) in scheduleKeyPoints" :key="index" class="px-2 border-l-4 border-green-300">
                        <p class="text-xs">{{ scheduleKeyPoint }}</p>
                    </div>
                </template>

            </template>

        </div>

        <div v-if="showScheduleSummary" class="space-y-4 px-4 pb-4">

            <p class="font-bold text-md mb-2">Test Schedule</p>
            <div>
                <p class="text-sm text-gray-500">This is how the delivery days<template v-if="deliveryMethodForm.schedule_type == 'date and time'"> and times</template> will appear for customers shopping today.</p>
                <p class="text-sm text-gray-500">Go ahead and try it! 🫣</p>
            </div>

            <div class="grid grid-cols-2 gap-4 space-x-2">

                <!-- Test Date Datepicker -->
                <div class="col-span-2 lg:col-span-1 space-y-2">
                    <p class="font-bold text-sm">Delivery date</p>
                    <DeliveryDatePicker
                        :form="form"
                        v-model="testDeliveryDate"
                        @isLoading="onIsLoadingDateOptions"
                        @scheduleOptions="(_scheduleOptions) => scheduleOptions = _scheduleOptions">
                    </DeliveryDatePicker>
                </div>

                <!-- Test Time Datepicker -->
                <div v-if="testDeliveryDate && !isLoadingDateOptions && deliveryMethodForm.schedule_type == 'date and time'" class="col-span-2 lg:col-span-1 space-y-2">
                    <p class="font-bold text-sm">Delivery time</p>
                    <DeliveryTimePicker
                        :form="form"
                        v-model="testDeliveryTimeslot"
                        :deliveryDate="testDeliveryDate"
                        @isLoading="onIsLoadingTimeOptions"
                        @scheduleOptions="(_scheduleOptions) => scheduleOptions = _scheduleOptions">
                    </DeliveryTimePicker>
                </div>

            </div>

            <p v-if="testDeliveryDate" class="text-sm text-green-500">Your order will be delivered on <span class="font-bold">{{ testDeliveryDate }} ({{ formattedShortWeekday(testDeliveryDate) }})</span>, just {{ formattedRelativeDate(testDeliveryDate, true) }} from now.</p>

        </div>

    </div>

</template>

<script>

    import Skeleton from '@Partials/Skeleton.vue';
    import { formattedShortWeekday, formattedRelativeDate } from '@Utils/dateUtils.js';
    import DeliveryDatePicker from '@Pages/orders/order/editable/components/order-delivery-methods/DeliveryDatePicker.vue';
    import DeliveryTimePicker from '@Pages/orders/order/editable/components/order-delivery-methods/DeliveryTimePicker.vue';

    export default {
        inject: ['deliveryMethodState'],
        components: {
            Skeleton, DeliveryDatePicker, DeliveryTimePicker
        },
        props: {
            form: {
                type: Object
            }
        },
        data() {
            return {
                testDeliveryDate: null,
                testDeliveryTimeslot: null,
                scheduleOptions: {},
                showScheduleSummary: true,
                isLoadingDateOptions: false,
                isLoadingTimeOptions: false,
            }
        },
        computed: {
            deliveryMethodForm() {
                return this.deliveryMethodState.deliveryMethodForm;
            },
            scheduleKeyPoints() {
                return this.scheduleOptions.schedule_key_points || [];
            }
        },
        methods: {
            formattedRelativeDate: formattedRelativeDate,
            formattedShortWeekday: formattedShortWeekday,
            onIsLoadingDateOptions(isLoading) {
                this.testDeliveryDate = null;
                this.testDeliveryTimeslot = null;
                this.isLoadingDateOptions = isLoading;
            },
            onIsLoadingTimeOptions(isLoading) {
                this.testDeliveryTimeslot = null;
                this.isLoadingTimeOptions = isLoading;
            },
            toggleScheduleSummary() {
                this.showScheduleSummary = !this.showScheduleSummary;
                this.testDeliveryDate = null;
                this.testDeliveryTimeslot = null;
            }
        }
    };

</script>
