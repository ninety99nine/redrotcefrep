<template>

    <Button
        size="sm"
        type="light"
        class="w-full"
        :action="null"
        :leftIcon="Mail"
        buttonClass="w-full"
        :skeleton="isLoadingOrder">
        <span>Email Invoice</span>
    </Button>

</template>

<script>

    import { Mail } from 'lucide-vue-next';
    import Button from '@Partials/Button.vue';

    export default {
        inject: ['formState', 'orderState', 'storeState'],
        components: { Button },
        data() {
            return {
                Mail
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

        }
    };

</script>
