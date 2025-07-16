<template>

    <div class="grid grid-cols-12 gap-4 mb-4">

        <div class="col-span-8">

            <!-- Order Basics -->
            <OrderBasics></OrderBasics>

            <!-- Order Details -->
            <OrderDetails></OrderDetails>

            <!-- Payments -->
            <Payments></Payments>

            <!-- Order Comments -->
            <OrderComments></OrderComments>

        </div>

        <div class="col-span-4">

            <!-- Team Member -->
            <TeamMember></TeamMember>

            <!-- Customer Summary -->
            <CustomerSummary></CustomerSummary>

            <!-- Payment Link -->
            <PaymentLink></PaymentLink>

            <!-- Delivery Address -->
            <DeliveryAddress></DeliveryAddress>

            <!-- Order History -->
            <OrderHistory></OrderHistory>

        </div>

    </div>

</template>

<script>

    import Payments from '@Pages/orders/order/viewable/components/payments/Payments.vue';
    import TeamMember from '@Pages/orders/order/viewable/components/team-member/TeamMember.vue';
    import OrderBasics from '@Pages/orders/order/viewable/components/order-basics/OrderBasics.vue';
    import PaymentLink from '@Pages/orders/order/viewable/components/payment-link/PaymentLink.vue';
    import OrderHistory from '@Pages/orders/order/viewable/components/order-history/OrderHistory.vue';
    import OrderDetails from '@Pages/orders/order/viewable/components/order-details/OrderDetails.vue';
    import OrderComments from '@Pages/orders/order/viewable/components/order-comments/OrderComments.vue';
    import CustomerSummary from '@Pages/orders/order/viewable/components/customer-summary/CustomerSummary.vue';
    import DeliveryAddress from '@Pages/orders/order/viewable/components/delivery-address/DeliveryAddress.vue';

    export default {
        inject: ['orderState', 'storeState'],
        components: {
            Payments, TeamMember, OrderBasics, PaymentLink, OrderHistory, OrderDetails, OrderComments, CustomerSummary, DeliveryAddress
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
