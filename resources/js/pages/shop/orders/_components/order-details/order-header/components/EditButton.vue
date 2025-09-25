<template>

    <Button
        size="sm"
        type="bare"
        leftIconSize="18"
        :leftIcon="SquarePen"
        :action="navigateToEditOrder">
    </Button>

</template>

<script>

    import Button from '@Partials/Button.vue';
    import { SquarePen } from 'lucide-vue-next';

    export default {
        inject: ['orderState', 'storeState'],
        components: { Button },
        data() {
            return {
                SquarePen
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            order() {
                return this.orderState.order;
            }
        },
        methods: {
            navigateToEditOrder() {
                this.$router.push({
                    name: 'edit-order',
                    params: {
                        order_id: this.order.id
                    },
                    query: {
                        store_id: this.store.id
                    }
                });
            }
        }
    };

</script>
