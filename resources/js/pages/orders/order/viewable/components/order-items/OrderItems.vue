<template>

    <div class="bg-white overflow-hidden rounded-lg space-y-4 p-4 mb-4">

        <div class="flex justify-between items-center">

            <h1 class="text-gray-700 font-semibold mb-4">Items <template v-if="!isLoadingStore && !isLoadingOrder && hasOrder">({{ order.total_uncancelled_product_quantities }})</template></h1>

            <div class="flex space-x-4">

                <div class="flex items-center justify-end space-x-1"
                    v-if="!isLoadingStore && !isLoadingOrder && hasOrder">
                    <span class="text-gray-500 text-xs">{{ formattedDatetime(order.created_at) }}</span>
                    <Popover :content="`Created ${formattedRelativeDate(order.created_at)}`"></Popover>
                </div>

                <Skeleton v-if="isLoadingStore || isLoadingOrder || !hasOrder" width="w-full" height="h-8" rounded="rounded-md" :shine="true"></Skeleton>

                <Select
                    v-else
                    class="w-40"
                    :search="false"
                    :options="statuses"
                    v-model="orderForm.status"
                    @change="(status) => updateOrder({ status: status })">
                </Select>

            </div>

        </div>

        <!-- Order Items -->
        <OrderProducts></OrderProducts>

        <!-- Order Totals -->
        <OrderTotals></OrderTotals>

    </div>

</template>

<script>

    import Select from '@Partials/Select.vue';
    import Popover from '@Partials/Popover.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { capitalize } from '@Utils/stringUtils.js';
    import { formattedDatetime, formattedRelativeDate } from '@Utils/dateUtils.js';
    import OrderTotals from '@Pages/orders/order/viewable/components/order-items/order-totals/OrderTotals.vue';
    import OrderProducts from '@Pages/orders/order/viewable/components/order-items/order-products/OrderProducts.vue';

    export default {
        inject: ['formState', 'orderState', 'storeState', 'notificationState'],
        components: {
            Select, Popover, Skeleton, OrderTotals, OrderProducts
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            order() {
                return this.orderState.order;
            },
            orderId() {
                return this.$route.params.order_id;
            },
            orderForm() {
                return this.orderState.orderForm;
            },
            hasOrder() {
                return this.orderState.hasOrder;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            isLoadingOrder() {
                return this.orderState.isLoadingOrder;
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
        },
        methods: {
            formattedDatetime,
            formattedRelativeDate,
            async updateOrder(data) {

                try {

                    data = {
                        ...data,
                        store_id: this.store.id
                    };

                    await axios.put(`/api/orders/${this.orderId}`, data);

                    this.notificationState.showSuccessNotification(`Order ${this.orderForm.status}`);
                    this.orderState.order.status = this.orderForm.status;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating order';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update order changes:', error);
                }

            }
        }
    };

</script>
