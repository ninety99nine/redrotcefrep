<template>

    <div class="pt-24 px-20 min-h-screen">

        <div class="flex flex-col items-center my-16">

            <h1 class="space-x-2 text-xl text-gray-700 font-semibold mb-2">
                <template v-if="isLoadingStores">Searching Your Stores</template>
                <template v-else-if="hasStores">Let’s Get Selling!</template>
                <template v-else>Start Selling Today!</template>
            </h1>

            <p class="text-sm text-gray-500">
                <template v-if="isLoadingStores">Hang tight! We're getting everything ready for you.</template>
                <template v-else-if="hasStores">Select a store to start managing your business.</template>
                <template v-else>You haven’t created a store yet—let’s get your first one up and running! 🚀</template>
            </p>

        </div>

        <!-- Loader -->
        <div
            v-if="isLoadingStores"
            class="flex justify-center">
            <Loader></Loader>
        </div>

        <div v-else class="flex justify-center">

            <div class="w-full max-w-5xl flex flex-wrap justify-center gap-4">

                <!-- Store Cards -->
                <StoreCard
                    class="w-80"
                    :key="index"
                    :store="store"
                    v-for="(store, index) in stores">
                </StoreCard>

                <!-- Add Store Card -->
                <AddStoreCard :hasStores="hasStores" class="w-80"></AddStoreCard>

            </div>

        </div>

    </div>

</template>

<script>

    import Loader from '@Partials/Loader.vue';
    import StoreCard from '@Pages/stores/components/StoreCard.vue';
    import AddStoreCard from '@Pages/stores/components/AddStoreCard.vue';

    export default {
        inject: ['authState', 'formState', 'notificationState'],
        components: {
            StoreCard, Loader, AddStoreCard
        },
        data() {
            return {
                stores: [],
                pagination: null,
                isLoadingStores: false
            };
        },
        computed: {
            authUser() {
                return this.authState.user;
            },
            hasStores() {
                return this.stores.length > 0;
            }
        },
        methods: {
            async showStores() {
                try {

                    this.isLoadingStores = true;

                    let config = {
                        params: {
                            'association': 'team member',
                            '_relationships': ['logo', 'activeSubscription'].join(',')
                        }
                    };

                    const response = await axios.get('/api/stores', config);

                    this.pagination = response.data;
                    this.stores = this.pagination.data;

                    if(this.stores.length == 0) {
                        this.navigateToCreateStore();
                    }

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching stores';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch stores:', error);
                } finally {
                    this.isLoadingStores = false;
                }
            },
            navigateToCreateStore() {
                this.$router.push({
                    name: 'create-store'
                });
            }
        },
        created() {
            this.showStores();
        }
    };

</script>
