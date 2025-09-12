<template>

    <div v-if="scheduleIsRequired" class="space-y-4 pt-4 px-4">

        <div class="space-y-2">
            <p class="font-bold text-md">Schedule</p>
            <div>
                <p class="text-sm text-gray-500">
                    <template v-if="deliveryMethod.schedule_type == 'date'">The delivery date for this order</template>
                    <template v-else-if="deliveryMethod.schedule_type == 'date and time'">Specify the delivery date and time for this order</template>
                </p>
            </div>
        </div>

        <span
            v-if="errorText"
            class="scroll-to-error font-medium text-red-500 text-xs mt-1 ml-1">
            {{ errorText }}
        </span>

        <!-- Date Picker -->
        <DeliveryDatePicker
            :deliveryMethod="deliveryMethod"
            v-model="orderForm.delivery_date"
            @isLoading="onIsLoadingDateOptions">
        </DeliveryDatePicker>

        <!-- Time Picker -->
        <DeliveryTimePicker
            class="w-full"
            :autoSelectFirstTimeslot="true"
            :deliveryMethod="deliveryMethod"
            v-model="orderForm.delivery_timeslot"
            :deliveryDate="orderForm.delivery_date"
            v-if="orderForm.delivery_date && !isLoadingDateOptions && deliveryMethod.schedule_type == 'date and time'">
        </DeliveryTimePicker>

        <!-- Schedule Incomplete Reasons -->
        <div
            :key="index"
            v-for="(shoppingCartDeliveryMethodScheduleIncompleteReason, index) in scheduleIncompleteReasons">

            <Skeleton v-if="isInspectingShoppingCart" width="w-1/3" :shine="true"></Skeleton>
            <div v-else class="flex items-center space-x-1 md:space-x-2">
                <svg class="w-4 h-4 md:w-6 md:h-6 flex-shrink-0 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span class="font-medium text-red-500 text-xs md:text-sm">{{ shoppingCartDeliveryMethodScheduleIncompleteReason }}</span>
            </div>

        </div>

        <div v-if="orderForm.delivery_date" class="space-y-2 border-t border-b border-dashed border-gray-300 py-4">

            <div class="text-sm">
                <span>Delivery date: </span>
                <span class="font-bold">
                    {{ orderForm.delivery_date }} ({{ formattedShortWeekday(orderForm.delivery_date) }})
                </span>
            </div>

            <div v-if="orderForm.delivery_timeslot" class="text-sm">
                <span>Delivery time: </span>
                <span class="font-bold">
                    {{ orderForm.delivery_timeslot }}
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
    import { formattedShortWeekday } from '@Utils/dateUtils.js';
    import DeliveryDatePicker from '@Pages/orders/order/editable/components/order-delivery-methods/DeliveryDatePicker.vue';
    import DeliveryTimePicker from '@Pages/orders/order/editable/components/order-delivery-methods/DeliveryTimePicker.vue';

    export default {
        inject: ['formState', 'orderState'],
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
                isLoadingDateOptions: false
            }
        },
        computed: {
            orderForm() {
                return this.orderState.orderForm;
            },
            shoppingCart() {
                return this.orderState.shoppingCart;
            },
            isInspectingShoppingCart() {
                return this.orderState.isInspectingShoppingCart;
            },
            scheduleIsRequired() {
                return ((this.shoppingCartDeliveryMethodOption || {}).schedule_is_required || false);
            },
            scheduleIncompleteReasons() {
                return ((this.shoppingCartDeliveryMethodOption || {}).schedule_incomplete_reasons || []);
            },
            shoppingCartDeliveryMethodOption() {
                return this.orderState.getShoppingCartDeliveryMethodOption(this.deliveryMethod);
            },
            errorText() {
                return this.formState.getFormError(`delivery_methods.${this.index}.schedule`);
            }
        },
        methods: {
            formattedShortWeekday: formattedShortWeekday,
            onIsLoadingDateOptions(isLoading) {
                this.isLoadingDateOptions = isLoading;
            }
        }
    };

</script>
