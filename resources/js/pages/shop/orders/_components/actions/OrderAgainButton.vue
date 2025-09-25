<template>

    <Button
        size="md"
        type="light"
        class="w-full"
        :action="orderAgain"
        buttonClass="w-full"
        :leftIcon="RotateCcw"
        :skeleton="isLoadingStore || isLoadingOrder">
        <span class="ml-1">Order Again</span>
    </Button>

</template>

<script>

    import Button from '@Partials/Button.vue';
    import { RotateCcw } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'orderState', 'storeState'],
        components: { Button },
        data() {
            return {
                RotateCcw
            }
        },
        computed: {
            order() {
                return this.orderState.order;
            },
            store() {
                return this.storeState.store;
            },
            isLoadingOrder() {
                return this.orderState.isLoadingOrder;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            }
        },
        methods: {
            async orderAgain() {
                //  Refer to resources/js/layouts/shop/Shop.vue to see what happens
                //  on the orderId wacher after order_id is unset on the route and
                //  then how we set the orderForm to allow for reorder.
                await this.$router.push({
                    name: 'show-checkout',
                    params: {
                        alias: this.store.alias
                    }
                });
            }
        }
    };

</script>
