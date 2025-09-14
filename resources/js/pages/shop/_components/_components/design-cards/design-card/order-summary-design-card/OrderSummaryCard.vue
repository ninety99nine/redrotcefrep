<template>

    <div
        class="bg-white rounded-2xl p-4"
        v-if="designCard.type == 'order summary'">

        <h1
            v-if="designCard.metadata.title"
            class="text-base text-gray-700 font-semibold">
            {{ designCard.metadata.title }}
        </h1>

        <p
            class="text-sm text-gray-700 mt-2"
            v-if="designCard.metadata.description">
            {{ designCard.metadata.description }}
        </p>

        <OrderTotals class="mt-4"></OrderTotals>

    </div>

</template>

<script>

    import OrderTotals from '@Pages/shop/_components/_components/design-cards/design-card/order-summary-design-card/order-totals/OrderTotals.vue';

    export default {
        inject: ['formState', 'orderState'],
        components: { OrderTotals },
        props: {
            designCard: {
                type: Object
            }
        }
    }
</script>
