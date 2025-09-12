<template>

    <Button
        size="sm"
        type="light"
        class="w-full"
        :action="orderAgain"
        buttonClass="w-full"
        :leftIcon="RotateCcw"
        :skeleton="isLoadingStore || isLoadingOrder">
        <span>Order Again</span>
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
                this.orderState.setOrderForm(this.order, false);
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
