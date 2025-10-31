<template>

    <div class="select-none grid grid-cols-12 gap-4 mb-40">

        <div class="col-span-8 space-y-4">

            <!-- Order Products -->
            <OrderProducts></OrderProducts>

            <!-- Order Promotions -->
            <OrderPromotions></OrderPromotions>

            <!-- Order Fees -->
            <OrderFees></OrderFees>

            <!-- Order Delivery Methods -->
            <OrderDeliveryMethods></OrderDeliveryMethods>

            <!-- Order Miscellaneous -->
            <OrderMiscellaneous></OrderMiscellaneous>

            <!-- Order Totals -->
            <OrderTotals></OrderTotals>

        </div>

        <div class="col-span-4 space-y-4">

            <!-- Order Customer -->
            <OrderCustomer></OrderCustomer>

            <!-- Order Basics -->
            <OrderBasics></OrderBasics>

        </div>

    </div>

</template>

<script>

    import debounce from 'lodash.debounce';
    import OrderFees from '@Pages/orders/order/editable/components/order-fees/OrderFees.vue';
    import OrderBasics from '@Pages/orders/order/editable/components/order-basics/OrderBasics.vue';
    import OrderTotals from '@Pages/orders/order/editable/components/order-totals/OrderTotals.vue';
    import OrderCustomer from '@Pages/orders/order/editable/components/order-customer/OrderCustomer.vue';
    import OrderProducts from '@Pages/orders/order/editable/components/order-products/OrderProducts.vue';
    import OrderPromotions from '@Pages/orders/order/editable/components/order-promotions/OrderPromotions.vue';
    import OrderMiscellaneous from '@Pages/orders/order/editable/components/order-miscellaneous/OrderMiscellaneous.vue';
    import OrderDeliveryMethods from '@Pages/orders/order/editable/components/order-delivery-methods/OrderDeliveryMethods.vue';

    export default {
        inject: ['formState', 'orderState', 'storeState', 'changeHistoryState', 'notificationState'],
        components: {
            OrderFees, OrderBasics, OrderTotals, OrderCustomer, OrderProducts, OrderPromotions,
            OrderMiscellaneous, OrderDeliveryMethods
        },
        data() {
            return {
                shoppingCartReady: false
            }
        },
        watch: {
            orderForm: {
                handler(newVal) {
                    if(newVal) {
                        if(this.shoppingCartReady) {
                            this.orderState.setIsInspectingShoppingCart(true);
                            this.inspectShoppingCartDelayed();
                        }else{
                            this.shoppingCartReady = true;
                        }
                    }
                },
                deep: true
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            orderForm() {
                return this.orderState.orderForm;
            },
            isEditing() {
                return this.$route.name === 'edit-order';
            },
            isCreating() {
                return this.$route.name === 'create-order';
            },
        },
        methods: {
            inspectShoppingCartDelayed: debounce(function () {

                if(this.changeHistoryState.hasChanges) {
                    this.inspectShoppingCart();
                }else{
                    this.orderState.setShoppingCart(null);
                    this.orderState.setIsInspectingShoppingCart(false);
                }

            }, 1000),
            async inspectShoppingCart() {

                try {

                    this.orderState.setIsInspectingShoppingCart(true);

                    const data = {
                        inspect: true,
                        ...this.orderForm,
                        store_id: this.store.id,
                        association: 'team member'
                    };

                    const response = await axios.post(`/api/orders`, data);
                    this.orderState.setShoppingCart(response.data);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while inspecting shopping cart';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to inspect shopping cart:', error);
                } finally {
                    this.orderState.setIsInspectingShoppingCart(false);
                }

            }
        }
    };

</script>
