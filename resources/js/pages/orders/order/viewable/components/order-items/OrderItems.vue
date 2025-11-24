<template>

    <div class="bg-white rounded-lg space-y-4 p-4 mb-4">

        <div class="flex justify-between items-center">

            <h1 class="text-gray-700 font-semibold mb-4">Items <template v-if="!isLoadingStore && !isLoadingOrder && hasOrder">({{ order.total_uncancelled_product_quantities }})</template></h1>

            <div class="flex space-x-4">

                <div class="flex items-center justify-end space-x-1"
                    v-if="!isLoadingStore && !isLoadingOrder && hasOrder">
                    <span class="text-gray-500 text-xs">{{ formattedDatetime(order.created_at) }}</span>
                    <Popover :content="`Created ${formattedRelativeDate(order.created_at)}`"></Popover>
                </div>

                <Skeleton v-if="isLoadingStore || isLoadingOrder || !hasOrder" width="w-full" height="h-8" rounded="rounded-md" :shine="true"></Skeleton>

                <OrderStatusSelect
                    v-else
                    class="w-48"
                    :showLabel="false"
                    @change="(status) => updateOrder({ status: status })">
                </OrderStatusSelect>

            </div>

        </div>

        <!-- Order Items -->
        <OrderProducts></OrderProducts>

        <!-- Order Totals -->
        <OrderTotals></OrderTotals>

    </div>

</template>

<script>

    import Popover from '@Partials/Popover.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { formattedDatetime, formattedRelativeDate } from '@Utils/dateUtils.js';
    import OrderTotals from '@Pages/orders/order/viewable/components/order-items/order-totals/OrderTotals.vue';
    import OrderProducts from '@Pages/orders/order/viewable/components/order-items/order-products/OrderProducts.vue';
    import OrderStatusSelect from '@Pages/orders/order/editable/components/order-basics/components/OrderStatusSelect.vue';

    export default {
        inject: ['formState', 'orderState', 'storeState', 'notificationState'],
        components: {
            Popover, Skeleton, OrderTotals, OrderProducts, OrderStatusSelect
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
            }
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
