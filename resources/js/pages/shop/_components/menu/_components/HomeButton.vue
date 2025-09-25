<template>

    <Button
        size="sm"
        type="outline"
        :leftIcon="House"
        leftIconSize="18"
        :action="navigateToStorefront">
        <span class="ml-1">Shop</span>
    </Button>

</template>

<script>

    import { House } from 'lucide-vue-next';
    import Button from '@Partials/Button.vue';

    export default {
        inject: ['storeState', 'notificationState'],
        components: { Button },
        data() {
            return {
                House
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            isDesigning() {
                return ['edit-storefront', 'edit-checkout', 'edit-payment', 'edit-menu'].includes(this.$route.name);
            }
        },
        methods: {
            async navigateToStorefront() {
                if(this.isDesigning) {
                    this.notificationState.showSuccessNotification(`Only opens on the actual store`);
                    return;
                }
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
