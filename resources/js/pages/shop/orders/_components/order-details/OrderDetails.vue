<template>

    <div class="select-none bg-white space-y-4 pt-8 pb-4 px-4 shadow-sm border border-gray-100 rounded-xl mb-4">

        <OrderHeader></OrderHeader>

        <OrderContent></OrderContent>

    </div>

</template>

<script>

    import OrderHeader from '@Pages/shop/orders/_components/order-details/order-header/OrderHeader.vue';
    import OrderContent from '@Pages/shop/orders/_components/order-details/order-content/OrderContent.vue';

    export default {
        components: { OrderHeader, OrderContent }
    };

</script>
