<template>

    <div
        class="bg-white rounded-2xl p-4"
        v-if="designCard.type == 'delivery' && designCard.metadata.show_delivery_methods">

        <h1
            v-if="designCard.metadata.title"
            class="text-base text-gray-700 font-semibold">
            {{ designCard.metadata.title }}
        </h1>

        <p
            v-if="designCard.metadata.description"
            class="text-sm text-gray-700 mt-2">
            {{ designCard.metadata.description }}
        </p>

        <div class="space-y-2 mt-4">

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
