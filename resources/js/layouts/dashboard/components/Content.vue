<template>

    <div :class="['relative', { 'sm:ml-64' : storeMode }]">

            <!-- Page Loader -->
            <transition v-if="storeMode" name="fade-loader">
                <div v-if="uiState.isLoading" class="absolute top-0 bottom-0 left-0 right-0 inset-0 bg-white bg-opacity-90 z-20">
                    <div class="absolute w-full flex justify-center items-center h-screen">
                        <Loader size="custom" customSize="w-8 h-8 border-4"></Loader>
                    </div>
                </div>
            </transition>

            <!-- Dashboard Content -->
            <router-view></router-view>

        </div>

</template>

<script>

    import Loader from '@Partials/Loader.vue';

    export default {
        inject: ['uiState'],
        components: { Loader },
        props: {
            storeMode: {
                type: Boolean
            }
        }
    }
</script>
