<template>

    <Button
        size="lg"
        type="primary"
        class="w-full"
        buttonClass="w-full"
        :action="navigateToStorePaymentMethods"
        :skeleton="isLoadingStore || isLoadingOrder"
        v-if="isLoadingStore || isLoadingOrder || !['paid', 'waiting confirmation'].includes(order.payment_status)">
        <span>Pay</span>
    </Button>

</template>

<script>

    import Button from '@Partials/Button.vue';

    export default {
        inject: ['formState', 'orderState', 'storeState'],
        components: { Button },
        data() {
            return {

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
            navigateToStorePaymentMethods() {
                this.$router.push({
                    name: 'show-shop-payment-methods',
                    params: {
                        'alias': this.store.alias,
                        'order_id': this.order.id
                     }
                });
            }
        }
    };

</script>
