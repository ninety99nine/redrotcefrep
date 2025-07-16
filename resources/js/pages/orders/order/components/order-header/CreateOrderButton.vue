<template>

    <Button
        size="sm"
        type="primary"
        :action="saveChanges"
        :skeleton="isLoadingStore || isLoadingOrder">
        <span>Create Order</span>
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
            }
        },
        methods: {
            saveChanges() {

            }
        }
    };

</script>
