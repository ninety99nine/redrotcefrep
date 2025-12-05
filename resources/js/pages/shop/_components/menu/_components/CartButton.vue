<template>

    <div
        @click.stop="showCartDrawer"
        :class="['relative cursor-pointer', { 'mr-6' : shoppingCart }]">
        <div
            v-if="shoppingCart"
            class="absolute -top-2 -right-5 z-10 flex items-center justify-center min-w-6 w-fit h-5 p-0.5 bg-red-500 text-xs font-bold text-white rounded-full hover:opacity-80">
            <span class="text-xs">{{totalUncancelledProductQuantities}}</span>
        </div>
        <Button
            size="xs"
            type="bare"
            :leftIcon="ShoppingCart"
            :action="showCartDrawer"
            :leftIconSize="shoppingCart ? '24' : '18'">
        </Button>

    </div>

</template>

<script>

    import { ShoppingCart } from 'lucide-vue-next';
    import Button from '@Partials/Button.vue';

    export default {
        inject: ['shopState', 'orderState', 'storeState'],
        components: { Button },
        data() {
            return {
                ShoppingCart
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            shoppingCart() {
                return this.orderState.shoppingCart;
            },
            totalUncancelledProductQuantities() {
                return this.shoppingCart ? this.shoppingCart.totals_summary.order_products.total_uncancelled_product_quantities : null;
            }
        },
        methods: {
            async showCartDrawer() {
                if(this.shopState.showDrawer) {
                    this.shopState.showDrawer();
                }
            },
        }
    };

</script>
