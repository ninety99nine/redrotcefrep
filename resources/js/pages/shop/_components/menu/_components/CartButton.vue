<template>

    <Button
        size="xs"
        type="bare"
        leftIconSize="18"
        :leftIcon="ShoppingCart"
        :action="navigateToStorefront">
    </Button>

</template>

<script>

    import { ShoppingCart } from 'lucide-vue-next';
    import Button from '@Partials/Button.vue';

    export default {
        inject: ['storeState'],
        components: { Button },
        data() {
            return {
                ShoppingCart
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            }
        },
        methods: {
            async navigateToStorefront() {
                await this.$router.push({
                    name: 'show-storefront',
                    params: {
                        alias: this.store.alias
                    }
                });
            },
        }
    };

</script>
