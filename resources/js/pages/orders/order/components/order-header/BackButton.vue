<template>

    <Button
        size="xs"
        type="light"
        :action="goBack"
        :leftIcon="MoveLeft">
        <span>Back</span>
    </Button>

</template>

<script>

    import Button from '@Partials/Button.vue';
    import { MoveLeft } from 'lucide-vue-next';

    export default {
        inject: ['storeState', 'orderState'],
        components: { Button },
        data() {
            return {
                MoveLeft,
                searchTerm: null,
                filterExpressions: [],
                sortingExpressions: []
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            order() {
                return this.orderState.order;
            },
            isEditing() {
                return this.$route.name === 'edit-order';
            },
        },
        methods: {
            goBack() {
                if(this.isEditing) {
                    this.navigateToOrder();
                }else{
                    this.navigateToOrders();
                }
            },
            navigateToOrder() {
                this.$router.push({
                    name: 'show-order',
                    params: {
                        'order_id': this.order.id
                    },
                    query: {
                        store_id: this.store.id
                    }
                });
            },
            navigateToOrders() {
                this.$router.replace({
                    name: 'show-orders',
                    query: {
                        store_id: this.store.id,
                        searchTerm: this.$route.query.searchTerm,
                        filterExpressions: this.$route.query.filterExpressions,
                        sortingExpressions: this.$route.query.sortingExpressions
                    }
                });
            }
        }
    };

</script>
