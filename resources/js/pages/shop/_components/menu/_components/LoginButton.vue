<template>

    <Button
        size="sm"
        type="primary"
        :leftIcon="User"
        leftIconSize="18"
        :action="navigateToStoreLogin">
        <span class="ml-1">Staff Login</span>
    </Button>

</template>

<script>

    import { User } from 'lucide-vue-next';
    import Button from '@Partials/Button.vue';

    export default {
        inject: ['storeState', 'notificationState'],
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
            isDesigning() {
                return ['edit-storefront', 'edit-checkout', 'edit-payment', 'edit-menu'].includes(this.$route.name);
            }
        },
        methods: {
            async navigateToStoreLogin() {
                if(this.isDesigning) {
                    this.notificationState.showSuccessNotification(`Only opens on the actual store`);
                    return;
                }
                await this.$router.push({
                    name: 'show-login'
                });
            },
        }
    };

</script>
