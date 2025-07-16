<template>

    <div class="bg-white overflow-hidden rounded-lg space-y-4 p-4 mb-4">

        <h1 class="text-lg text-gray-700 font-semibold mb-4">Items</h1>

        <!-- Order Items -->
        <OrderProducts></OrderProducts>

        <!-- Order Totals -->
        <OrderTotals></OrderTotals>

    </div>

</template>

<script>

    import OrderTotals from '@Pages/orders/order/viewable/components/order-details/order-totals/OrderTotals.vue';
    import OrderProducts from '@Pages/orders/order/viewable/components/order-details/order-products/OrderProducts.vue';

    export default {
        inject: ['orderState'],
        components: {
            OrderTotals, OrderProducts
        }
    };

</script>
