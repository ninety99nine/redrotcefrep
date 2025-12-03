<template>

    <div class="w-full bg-linear-to-b from-blue-100 to-blue-50 min-h-screen overflow-x-hidden">

        <Notifications></Notifications>

        <Header
            :stores="stores"
            :storeMode="storeMode"
            :isOnboarding="isOnboarding"
            :isLoadingStore="isLoadingStore"
            v-model:selectedStoreId="selectedStoreId">
        </Header>

        <Sidebar
            :storeMode="storeMode"
            :isLoadingStore="isLoadingStore">
        </Sidebar>

        <Content :storeMode="storeMode"></Content>

    </div>

</template>

<script>

    import Header from '@Layouts/dashboard/components/Header.vue';
    import Sidebar from '@Layouts/dashboard/components/Sidebar.vue';
    import Content from '@Layouts/dashboard/components/Content.vue';
    import Notifications from '@Layouts/dashboard/components/Notifications.vue';

    export default {
        inject: ['formState', 'storeState', 'notificationState'],
        components: {
            Header, Sidebar, Content, Notifications
        },
        data() {
            return {
                stores: [],
                selectedStoreId: null,
                isLoadingStores: false
            }
        },
        watch: {
            storeId(newValue, oldValue) {
                this.showStore();
                if(!oldValue && newValue) this.showStores();
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            storeId() {
                return this.$route.params.store_id || this.$route.query.store_id;
            },
            storeMode() {
                return !this.isOnboarding && this.storeId != null;
            },
            isOnboarding() {
                return this.$route.meta.onboarding === true;
            }
        },
        methods: {
            async showStore(silentUpdate = false) {
                try {

                    if(!this.storeId) return;

                    this.selectedStoreId = this.storeId;

                    if(!silentUpdate) this.storeState.isLoadingStore = true;

                    let config = {
                        params: {
                            _relationships: [
                                'logo', 'backgroundPhoto', 'seoImage', 'productTags', 'customerTags', 'categories',
                                'myMembership', 'activeSubscription.pricingPlan', 'address'
                            ].join(',')
                        }
                    };

                    const response = await axios.get(`/api/stores/${this.storeId}`, config);
                    this.storeState.setStore(response.data);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching store';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch store:', error);

                    if (error.response?.status === 404) {
                        await this.$router.replace({ name: 'show-stores' });
                    }

                } finally {
                    if(!silentUpdate) this.storeState.isLoadingStore = false;
                }
            },
            async showStores() {
                try {

                    this.isLoadingStores = true;

                    let config = {
                        params: {
                            association: 'team member'
                        }
                    };

                    const response = await axios.get('/api/stores', config);

                    const pagination = response.data;
                    this.stores = pagination.data;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching stores';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch stores:', error);
                } finally {
                    this.isLoadingStores = false;
                }
            },
        },
        created() {
            this.showStores();
            if(this.storeId) this.showStore();
            this.storeState.silentUpdate = () => this.showStore(true);
        }
    };

</script>
