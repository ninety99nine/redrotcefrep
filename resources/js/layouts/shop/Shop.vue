<template>

    <div>
        <Notifications></Notifications>
        <router-view></router-view>
    </div>

</template>

<script>

    import Notifications from '@Layouts/shop/components/Notifications.vue';

    export default {
        inject: ['formState', 'orderState', 'storeState', 'notificationState'],
        components: { Notifications },
        watch: {
            alias() {
                this.showStoreByAlias();
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            alias() {
                return this.$route.params.alias;
            }
        },
        methods: {
            async showStoreByAlias() {
                try {

                    this.storeState.isLoadingStore = true;

                    let config = {
                        params: {
                            _relationships: ['logo', 'categories'].join(',')
                        }
                    };

                    const response = await axios.get(`/api/stores/alias/${this.alias}`, config);

                    this.storeState.setStore(response.data);

                    this.orderState.setOrderForm(null);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching store';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch store:', error);
                } finally {
                    this.storeState.isLoadingStore = false;
                }
            },
        },
        created() {
            this.showStoreByAlias();

        }
    };

</script>
