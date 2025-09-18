<template>

    <Drawer
        position="right"
        ref="myCartDrawer"
        maxWidth="max-w-lg"
        :showFooter="false"
        :scrollOnContent="false">

        <template #trigger="props">

            <div class="z-20 fixed left-0 right-0 bottom-0 p-4">

                <div class="relative">

                    <Button
                        size="lg"
                        type="primary"
                        buttonClass="w-full"
                        class="max-w-96 mx-auto"
                        :action="props.showDrawer">
                        <div class="flex items-center space-x-4">
                            <span class="text-lg">My Cart</span>
                            <ShoppingCart size="24"></ShoppingCart>
                            <span class="text-lg">{{ grandTotal }}</span>
                        </div>
                    </Button>

                    <div class="absolute top-1/2 -translate-y-1/2 right-2 flex items-center justify-center rounded-full min-w-6 h-6 bg-red-500 text-white text-xs font-bold">
                        <span class="p-2">{{ totalUncancelledProductQuantities }} {{ totalUncancelledProductQuantities == 1 ? 'item' : 'items'}}</span>
                    </div>

                </div>

            </div>

        </template>

        <template #content="props">

            <div class="p-4">

                <h3 class="font-semibold text-gray-900 border-b border-gray-200 pb-2 mb-4">My Cart</h3>

                <CartProducts></CartProducts>

                <div class="border-t border-gray-100 pt-4 mt-4 space-y-8">

                    <Button
                        size="xs"
                        type="light"
                        buttonClass="w-full"
                        class="max-w-96 mx-auto"
                        v-if="orderForm.cart_products.length"
                        :action="() => emptyCart(props.hideDrawer)">
                        <span>Empty cart</span>
                    </Button>

                    <Button
                        size="lg"
                        type="primary"
                        rightIconSize="20"
                        :action="() => checkout(props.hideDrawer)"
                        buttonClass="w-full"
                        class="max-w-96 mx-auto">
                        <div class="flex items-center space-x-4">
                            <span class="text-lg">Checkout</span>
                            <ShoppingCart size="24"></ShoppingCart>
                            <span class="text-lg">{{ grandTotal }}</span>
                        </div>
                    </Button>

                </div>

            </div>

        </template>

    </Drawer>

</template>

<script>

    import Button from '@Partials/Button.vue';
    import Drawer from '@Partials/Drawer.vue';
    import { ShoppingCart } from 'lucide-vue-next';
    import CartProducts from '@Pages/shop/_components/design-card-manager/_components/my-cart/_components/CartProducts.vue';

    export default {
        inject: ['orderState', 'storeState'],
        components: {
            ShoppingCart, Button, Drawer, CartProducts
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            orderForm() {
                return this.orderState.orderForm;
            },
            shoppingCart() {
                return this.orderState.shoppingCart;
            },
            grandTotal() {
                return this.shoppingCart ? this.shoppingCart.totals.grand_total.amount_with_currency : null;
            },
            totalUncancelledProductQuantities() {
                return this.shoppingCart ? this.shoppingCart.totals_summary.order_products.total_uncancelled_product_quantities : null;
            },
        },
        methods: {
            async checkout(hideDrawer) {

                hideDrawer();

                //  Wait for the drawer to close
                await new Promise(resolve => setTimeout(resolve, 500));

                await this.$router.push({
                    name: 'show-checkout',
                    params: {
                        alias: this.store.alias
                    }
                });
            },
            async emptyCart(hideDrawer) {

                hideDrawer();

                //  Wait for the drawer to close
                await new Promise(resolve => setTimeout(resolve, 500));

                this.orderState.resetOrderForm();

            },
        }
    }
</script>
