<template>

    <div
        class="bg-white rounded-2xl p-4 space-y-4"
        v-if="designCard.metadata.type == 'order summary'">

        <h1 v-if="designCard.metadata.title" class="text-base text-gray-700 font-semibold mb-4">{{ designCard.metadata.title }}</h1>

        <OrderTotals></OrderTotals>

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
