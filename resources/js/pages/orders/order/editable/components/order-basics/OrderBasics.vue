<template>

    <div class="bg-white rounded-lg p-4 shadow-sm">

        <!-- Order Basics (Loading Placeholder) -->
        <div v-if="isLoadingStore || isLoadingOrder || (isEditting && !hasOrder)" class="space-y-2">

            <div v-for="(_, index) in [1, 2, 3]" :key="index" class="border-b border-gray-300 shadow-sm rounded-lg py-6 px-4 bg-gray-50">
                <Skeleton width="w-full" :shine="true"></Skeleton>
            </div>

        </div>

        <div v-else class="space-y-2">

            <OrderStatusSelect
                class="w-full"
                :showLabel="true"
                @change="orderState.saveStateDebounced(`Status changed to ${ orderForm.status }`)">
            </OrderStatusSelect>

            <OrderPaymentStatusSelect
                class="w-full"
                :showLabel="true"
                @change="orderState.saveStateDebounced(`Payment status changed to ${ orderForm.payment_status }`)">
            </OrderPaymentStatusSelect>

            <Select
                :search="false"
                label="Couriers"
                :options="couriers"
                v-model="orderForm.courier_id"
                :errorText="formState.getFormError('courier_id')"
                @change="orderState.saveStateDebounced('Courier changed')">
            </Select>

            <Input
                type="text"
                label="Tracking Number"
                v-model="orderForm.tracking_number"
                :errorText="formState.getFormError('tracking_number')"
                @input="orderState.saveStateDebounced('Tracking number changed')">
            </Input>

            <Input
                rows="2"
                type="textarea"
                label="Internal Note"
                v-model="orderForm.internal_note"
                :errorText="formState.getFormError('internal_note')"
                @change="orderState.saveStateDebounced('Internal note changed')"
                tooltipContent="Internal information about this order only visible to you and other team members">
            </Input>

        </div>

    </div>

</template>

<script>

    import axios from 'axios';
    import Input from '@Partials/Input.vue';
    import Select from '@Partials/Select.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { capitalize } from '@Utils/stringUtils.js';
    import OrderStatusSelect from '@Pages/orders/order/editable/components/order-basics/components/OrderStatusSelect.vue';
    import OrderPaymentStatusSelect from '@Pages/orders/order/editable/components/order-basics/components/OrderPaymentStatusSelect.vue';

    export default {
        inject: ['formState', 'storeState', 'orderState', 'notificationState'],
        components: { Input, Select, Skeleton, OrderStatusSelect, OrderPaymentStatusSelect },
        data() {
            return {
                couriers: [],
                isLoadingCouriers: false
            }
        },
        computed: {
            hasOrder() {
                return this.orderState.hasOrder;
            },
            orderForm() {
                return this.orderState.orderForm;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            isLoadingOrder() {
                return this.orderState.isLoadingOrder;
            },
            isEditting() {
                return this.$route.name === 'edit-order';
            },
            statuses() {
                const options = ['waiting','cancelled','completed','on its way','ready for pickup'];

                return options.map((option) => {
                    return {
                        'label': capitalize(option),
                        'value': option
                    }
                });
            },
            paymentStatuses() {
                const options = ['paid','unpaid','partially paid','waiting confirmation'];

                return options.map((option) => {
                    return {
                        'label': capitalize(option),
                        'value': option
                    }
                });
            }
        },
        methods: {
            async showCouriers() {
                try {

                    if(this.couriers.length) return;

                    this.isLoadingCouriers = true;

                    let config = {
                        params: {
                            'per_page': 100
                        }
                    };

                    const response = await axios.get('/api/couriers', config);

                    this.couriers = response.data.data.map((courier) => {
                        return {
                            'label': capitalize(courier.name),
                            'value': courier.id
                        }
                    });

                    this.couriers.unshift({
                        'label': 'None',
                        'value': null
                    });

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching stores';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch stores:', error);
                } finally {
                    this.isLoadingCouriers = false;
                }
            },
        },
        created() {
            this.showCouriers();
        }
    };

</script>
