<template>

    <Button
        size="sm"
        type="primary"
        :action="updateOrder"
        :disabled="!orderState.canSaveChanges"
        :skeleton="isLoadingStore || isLoadingOrder">
        <span>Save Changes</span>
    </Button>

</template>

<script>

    import Button from '@Partials/Button.vue';

    export default {
        inject: ['storeState', 'orderState'],
        components: { Button },
        computed: {
            isLoadingOrder() {
                return this.orderState.isLoadingOrder;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
        },
        methods: {
            updateOrder() {
                this.orderState.updateOrder({
                    ...this.orderState.orderForm,
                    'association': 'team member'
                })
            }
        }
    };

</script>
