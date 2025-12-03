<template>

    <Button
        size="sm"
        type="primary"
        :leftIcon="User"
        leftIconSize="18"
        :action="authUser ? navigateToStoreHome : navigateToStoreLogin">
        <span class="ml-1">{{ authUser ? 'Dashboard' : 'Login' }}</span>
    </Button>

</template>

<script>

    import { User } from 'lucide-vue-next';
    import Button from '@Partials/Button.vue';

    export default {
        inject: ['authState', 'storeState', 'notificationState'],
        components: { Button },
        data() {
            return {
                User
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            authUser() {
                return this.authState.user;
            },
            isDesigning() {
                return ['edit-storefront', 'edit-checkout', 'edit-payment', 'edit-menu'].includes(this.$route.name);
            }
        },
        methods: {
            navigateToStoreHome() {
                this.$router.push({
                    name: 'show-store-home',
                    params: { store_id: this.store.id }
                });
            },
            async navigateToStoreLogin() {
                if(this.isDesigning) {
                    this.notificationState.showSuccessNotification(`Only opens on the actual store`);
                    return;
                }
                await this.$router.push({
                    name: 'show-login'
                });
            }
        }
    };

</script>
