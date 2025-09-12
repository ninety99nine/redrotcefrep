<template>

    <div class="select-none max-w-xl mx-auto pt-16 pb-40">

        <Advert></Advert>

        <OrderDetails></OrderDetails>

        <Actions></Actions>

    </div>

</template>

<script>

    import Advert from '@Pages/shop/order/_components/advert/Advert.vue';
    import Actions from '@Pages/shop/order/_components/actions/Actions.vue';
    import OrderDetails from '@Pages/shop/order/_components/order-details/OrderDetails.vue';

    export default {
        components: { Advert, Actions, OrderDetails }
    };

</script>
