<template>

    <div class="grid grid-cols-12 gap-8 mb-4">

        <div class="col-span-8">

            <!-- Order Items -->
            <OrderItems></OrderItems>

            <!-- Order Transactions -->
            <OrderTransactions></OrderTransactions>

            <!-- Order Fulfilment -->
            <OrderFulfilment></OrderFulfilment>

        </div>

        <div class="col-span-4">

            <!-- Team Member -->
            <TeamMember></TeamMember>

            <!-- Order Comments -->
            <OrderComments></OrderComments>

        </div>

    </div>

</template>

<script>

    import TeamMember from '@Pages/orders/order/viewable/components/team-member/TeamMember.vue';
    import OrderItems from '@Pages/orders/order/viewable/components/order-items/OrderItems.vue';
    import OrderComments from '@Pages/orders/order/viewable/components/order-comments/OrderComments.vue';
    import OrderFulfilment from '@Pages/orders/order/viewable/components/order-fulfilment/OrderFulfilment.vue';
    import OrderTransactions from '@Pages/orders/order/viewable/components/order-transactions/OrderTransactions.vue';

    export default {
        inject: ['orderState', 'storeState'],
        components: {
            TeamMember, OrderItems, OrderComments, OrderFulfilment, OrderTransactions
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            order() {
                return this.orderState.order;
            },
            hasOrder() {
                return this.orderState.hasOrder;
            },
            isLoadingOrder() {
                return this.orderState.isLoadingOrder;
            }
        }
    };

</script>
