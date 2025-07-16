<template>

    <div class="select-none grid grid-cols-12 gap-4 mb-40">

        <div class="col-span-8 space-y-4">

            <div class="bg-white rounded-lg p-4 shadow-sm">

                {{ shoppingCartState.shoppingCartForm }}

                <!-- Order Products -->
                <OrderProducts></OrderProducts>

            </div>

            <div class="bg-white rounded-lg p-4 shadow-sm">

                <!-- Order Promotions -->
                <OrderPromotions></OrderPromotions>

            </div>

            <div class="bg-white rounded-lg p-4 shadow-sm">

                <!-- Order Fees -->
                <OrderFees></OrderFees>

            </div>

            <div class="bg-white rounded-lg p-4 shadow-sm">

                <!-- Order Delivery Methods -->
                <OrderDeliveryMethods></OrderDeliveryMethods>

            </div>

        </div>

    </div>

</template>

<script>

    import { v4 as uuidv4 } from 'uuid';
    import debounce from 'lodash/debounce';
    import OrderFees from '@Pages/orders/order/editable/components/fees/OrderFees.vue';
    import OrderProducts from '@Pages/orders/order/editable/components/products/OrderProducts.vue';
    import OrderPromotions from '@Pages/orders/order/editable/components/promotions/OrderPromotions.vue';
    import OrderDeliveryMethods from '@Pages/orders/order/editable/components/delivery-methods/OrderDeliveryMethods.vue';

    export default {
        inject: ['formState', 'orderState', 'storeState', 'shoppingCartState', 'notificationState'],
        components: {
            OrderFees, OrderProducts, OrderPromotions, OrderDeliveryMethods
        },
        data() {
            return {
                guestId: uuidv4()
            }
        },
        watch: {
            shoppingCartForm: {
                handler(newValue, oldValue) {
                    if(oldValue != null) {
                        this.shoppingCartState.setIsInspectingShoppingCart(true);
                        this.inspectShoppingCartDelayed();
                    }
                },
                deep: true
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            shoppingCartForm() {
                return this.shoppingCartState.shoppingCartForm;
            },
        },
        methods: {
            inspectShoppingCartDelayed: debounce(function () {
                this.inspectShoppingCart();
            }, 1000),
            async inspectShoppingCart() {

                try {

                    const data = {
                        guest_id: this.guestId,
                        store_id: this.store.id,
                        ...this.shoppingCartForm
                    };

                    const response = await axios.post(`/api/shopping-carts`, data);
                    this.shoppingCartState.setShoppingCart(response.data);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while inspecting shopping cart';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to inspect shopping cart:', error);
                } finally {
                    this.shoppingCartState.setIsInspectingShoppingCart(false);
                }

            }
        }
    };

</script>
