<template>

    <Button
        size="xs"
        type="light"
        :action="navigateToEditOrder"
        :skeleton="isLoadingStore || isLoadingOrder">
        <span>Edit</span>
    </Button>

</template>

<script>

    import Button from '@Partials/Button.vue';

    export default {
        inject: ['storeState', 'orderState'],
        components: { Button },
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
            navigateToEditOrder() {
                this.$router.push({
                    name: 'edit-order',
                    params: {
                        'order_id': this.order.id
                    },
                    query: {
                        'store_id': this.store.id
                    }
                });
            }
        }
    };

</script>
