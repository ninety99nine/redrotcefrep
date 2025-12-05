<template>

    <div
        v-if="store"
        class="z-20 fixed left-0 right-0 bottom-0 p-4">

        <Button
            size="lg"
            type="primary"
            :action="checkout"
            buttonClass="w-full"
            class="max-w-72 md:max-w-96 mx-auto">
            <div class="flex items-center space-x-4">
                <span class="text-base md:text-lg">Checkout</span>
                <ShoppingCart size="24"></ShoppingCart>
                <span class="text-base md:text-lg">{{ grandTotal }}</span>
            </div>
        </Button>

    </div>

</template>

<script>

    import Button from '@Partials/Button.vue';
    import { ShoppingCart } from 'lucide-vue-next';

    export default {
        inject: ['shopState', 'orderState', 'storeState'],
        components: {
            ShoppingCart, Button
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            shoppingCart() {
                return this.orderState.shoppingCart;
            },
            grandTotal() {
                return this.shoppingCart ? this.shoppingCart.totals.grand_total.amount_with_currency : null;
            }
        },
        methods: {
            async checkout() {
                await this.$router.push({
                    name: 'show-checkout',
                    params: {
                        alias: this.store.alias
                    }
                });
            },
        }
    }
</script>
