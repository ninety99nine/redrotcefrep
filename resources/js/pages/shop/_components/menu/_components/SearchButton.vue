<template>

    <Button
        size="xs"
        type="bare"
        leftIconSize="18"
        :leftIcon="Search"
        :action="navigateToSearch">
    </Button>

</template>

<script>

    import { Search } from 'lucide-vue-next';
    import Button from '@Partials/Button.vue';

    export default {
        inject: ['storeState', 'notificationState'],
        components: { Button },
        data() {
            return {
                Search
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
            async navigateToSearch() {
                if(this.isDesigning) {
                    this.notificationState.showSuccessNotification(`Only opens on the actual store`);
                    return;
                }
                await this.$router.push({
                    name: 'show-search',
                    params: {
                        alias: this.store.alias
                    }
                });
            },
        }
    };

</script>
