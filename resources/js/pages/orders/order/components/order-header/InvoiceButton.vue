<template>

    <Button
        size="xs"
        type="light"
        leftIconSize="12"
        :leftIcon="ReceiptText"
        :action="navigateToShowShopOrder"
        :skeleton="isLoadingStore || isLoadingOrder">
        <span>Invoice</span>
    </Button>

</template>

<script>

    import Button from '@Partials/Button.vue';
    import { ReceiptText } from 'lucide-vue-next';

    export default {
        inject: ['storeState', 'orderState'],
        components: { ReceiptText, Button },
        data() {
            return {
                ReceiptText
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
            async navigateToShowShopOrder() {
                await this.$router.push({
                    name: 'show-shop-order',
                    params: {
                        alias: this.store.alias,
                        order_id: this.order.id,
                    }
                });
            },
        }
    };

</script>
