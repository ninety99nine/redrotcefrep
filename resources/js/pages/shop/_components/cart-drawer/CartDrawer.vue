<template>

    <Drawer
        position="right"
        ref="myCartDrawer"
        :showFooter="false"
        :scrollOnContent="false">

        <template #content="props">

            <div class="pt-4">

                <h3 class="font-semibold text-gray-900 border-b border-gray-200 pb-2 mb-4">My Cart</h3>

                <CartProducts></CartProducts>

                <div
                    v-if="shoppingCart"
                    class="border-t border-gray-100 pt-4 mt-4 space-y-8">

                    <Button
                        size="xs"
                        type="light"
                        v-if="shoppingCart"
                        buttonClass="w-full"
                        class="max-w-96 mx-auto"
                        :action="() => emptyCart(props.hideDrawer)">
                        <span>Empty cart</span>
                    </Button>

                    <Button
                        size="lg"
                        type="primary"
                        rightIconSize="20"
                        buttonClass="w-full"
                        :action="() => checkout(props.hideDrawer)">
                        <div class="flex items-center space-x-4">
                            <span class="text-base md:text-lg">Checkout</span>
                            <ShoppingCart size="24"></ShoppingCart>
                            <span class="text-base md:text-lg">{{ grandTotal }}</span>
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
    import CartProducts from '@Pages/shop/_components/cart-drawer/_components/CartProducts.vue';

    export default {
        inject: ['shopState', 'orderState', 'storeState'],
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
            }
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
        },
        unmounted() {
            this.shopState.showDrawer = null;
            this.shopState.hideDrawer = null;
        },
        mounted() {
            this.shopState.showDrawer = this.$refs.myCartDrawer.showDrawer;
            this.shopState.hideDrawer = this.$refs.myCartDrawer.hideDrawer;
        }
    }
</script>
