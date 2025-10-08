<template>

    <div class="select-none max-w-xl mx-auto pt-16 pb-40">

        <Advert></Advert>

        <OrderDetails></OrderDetails>

        <Actions></Actions>

        <DeliveryAddress></DeliveryAddress>

        <OrderComments></OrderComments>

    </div>

</template>

<script>

    import Advert from '@Pages/shop/orders/_components/advert/Advert.vue';
    import Actions from '@Pages/shop/orders/_components/actions/Actions.vue';
    import OrderDetails from '@Pages/shop/orders/_components/order-details/OrderDetails.vue';
    import OrderComments from '@Pages/shop/orders/_components/order-comments/OrderComments.vue';
    import DeliveryAddress from '@Pages/shop/orders/_components/delivery-address/DeliveryAddress.vue';

    export default {
        components: { Advert, Actions, OrderDetails, OrderComments, DeliveryAddress }
    };

</script>
