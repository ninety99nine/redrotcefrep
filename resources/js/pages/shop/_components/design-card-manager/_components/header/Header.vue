<template>

    <div v-if="store">

        <div class="flex flex-col items-center mb-8 space-y-4">

            <div
                v-if="store.show_background && store.background_photo"
                :style="{ backgroundImage: `url(${store.background_photo.path})` }"
                class="relative w-full flex justify-center min-h-48 mb-16 bg-no-repeat bg-cover bg-center">

                <div class="absolute -bottom-10 border-4 border-white rounded-full">
                    <StoreLogo size="w-24 h-24" :editable="false" :showButton="false"></StoreLogo>
                </div>
            </div>

            <div
                v-else-if="store.show_background"
                :style="{ background: `#${store.bg_color}` }"
                class="relative w-full flex justify-center min-h-48 mb-16">

                <div class="absolute -bottom-10 border-4 border-white rounded-full">
                    <StoreLogo size="w-24 h-24" :editable="false" :showButton="false"></StoreLogo>
                </div>

            </div>

            <StoreLogo v-else size="w-24 h-24" class="mt-8" :editable="false" :showButton="false"></StoreLogo>

            <h1 class="text-lg md:text-xl font-bold">{{ store.name }}</h1>

            <p v-if="store.description" :class="['text-center text-sm md:text-base max-w-xl', isDesigning ? 'px-4' : 'px-4 lg:px-0']">{{ store.description }}</p>

        </div>

        <div class="select-none flex justify-center mb-4">
            <div
                @click="navigateToStorefront"
                :class="['flex items-center space-x-2 py-2 px-4 border-b-4 hover:border-gray-300 transition-all duration-300 cursor-pointer', homeMenuActive ? 'border-gray-300' : 'border-transparent']">
                <Home size="20"></Home>
                <span class="text-sm md:text-base">Home</span>
            </div>
            <div
                @click="navigateToSearch"
                :class="['flex items-center space-x-2 py-2 px-4 border-b-4 hover:border-gray-300 transition-all duration-300 cursor-pointer', searchMenuActive ? 'border-gray-300' : 'border-transparent']">
                <Search size="20"></Search>
                <span class="text-sm md:text-base">Search</span>
            </div>
            <div
                @click="navigateToReviews"
                :class="['flex items-center space-x-2 py-2 px-4 border-b-4 hover:border-gray-300 transition-all duration-300 cursor-pointer', reviewMenuActive ? 'border-gray-300' : 'border-transparent']">
                <Star size="20"></Star>
                <span class="text-sm md:text-base">Reviews{{ store.reviews_count ? ` (${store.reviews_count})` : '' }}</span>
            </div>
        </div>

    </div>

</template>

<script>

    import { Home, Star, Search } from 'lucide-vue-next';
    import StoreLogo from '@Components/StoreLogo.vue';

    export default {
        inject: ['storeState'],
        components: {
            Home, Star, Search, StoreLogo
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            isDesigning() {
                return ['edit-storefront', 'edit-checkout', 'edit-payment', 'edit-menu'].includes(this.$route.name);
            },
            homeMenuActive() {
                return this.$route.name == 'show-storefront';
            },
            searchMenuActive() {
                return this.$route.name == 'show-search';
            },
            reviewMenuActive() {
                return this.$route.name == 'show-shop-reviews' || this.$route.name == 'create-shop-review';
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
            async navigateToReviews() {
                if(this.isDesigning) {
                    this.notificationState.showSuccessNotification(`Only opens on the actual store`);
                    return;
                }
                await this.$router.push({
                    name: 'show-shop-reviews',
                    params: {
                        alias: this.store.alias
                    }
                });
            }
        }
    }
</script>
