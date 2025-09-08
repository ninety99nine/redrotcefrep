<template>

    <div
        class="bg-white rounded-2xl p-4 space-y-4"
        v-if="designCard.metadata.type == 'delivery' && designCard.metadata.show_delivery_methods">

        <h1 v-if="designCard.metadata.title" class="text-base text-gray-700 font-semibold mb-4">{{ designCard.metadata.title }}</h1>

        <div class="space-y-2">

            <OrderDeliveryMethods></OrderDeliveryMethods>

        </div>

    </div>

</template>

<script>

    import Input from '@Partials/Input.vue';
    import OrderDeliveryMethods from '@Pages/shop/_components/_components/design-cards/design-card/delivery-design-card/order-delivery-methods/OrderDeliveryMethods.vue';

    export default {
        components: { Input, OrderDeliveryMethods },
        props: {
            designCard: {
                type: Object
            }
        }
    }
</script>
