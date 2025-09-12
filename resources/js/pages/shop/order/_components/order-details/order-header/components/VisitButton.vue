<template>

    <Button
        size="sm"
        type="bare"
        :leftIcon="Store"
        leftIconSize="18"
        :action="navigateToStorefront">
    </Button>

</template>

<script>

    import { Store } from 'lucide-vue-next';
    import Button from '@Partials/Button.vue';

    export default {
        inject: ['storeState'],
        components: { Button },
        data() {
            return {
                Store
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
