<template>

    <div class="pt-16 min-h-screen">

        <!-- Loader -->
        <div v-if="isLoadingStore" class="flex items-center justify-center">
            <Loader size="custom" customSize="w-8 h-8 border-4"></Loader>
        </div>

        <!-- Dashboard Content -->
        <router-view v-else></router-view>

    </div>

</template>

<script>

    import Loader from '@Partials/Loader.vue';

    export default {
        inject: ['storeState'],
        components: { Loader },
        computed: {
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            }
        },
    };

</script>
