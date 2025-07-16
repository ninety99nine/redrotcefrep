<template>

    <div class="animated-border-pink bg-white space-y-4 py-4 px-4 shadow-sm rounded-xl flex flex-col items-center">

        <h1 class="space-x-2 text-sm text-gray-700 font-bold">
            Share Your Store
        </h1>

        <!-- Share On Social Platforms -->
        <div class="w-full">

            <a
                :key="index"
                target="_blank"
                :href="socialPlatform.link"
                v-for="(socialPlatform, index) in socialPlatforms">

                <div class="flex justify-between items-center gap-2 bg-white py-3 px-4 rounded-xl transition-all duration-300 border border-transparent hover:shadow-sm hover:border-gray-300 cursor-pointer">

                    <div class="w-full flex items-center space-x-4">

                        <!-- Social Platform Logo -->
                        <Skeleton v-if="isLoadingStore" width="w-8" height="h-8" :shine="true"></Skeleton>
                        <img v-else :src="`/images/social-media-icons/${socialPlatform.name.toLowerCase()}.png`" :alt="`${socialPlatform.name} Logo`" class="w-8 h-8" />

                        <!-- Social Platform Name -->
                        <div class="w-full space-y-1 text-sm">

                            <Skeleton v-if="isLoadingStore" width="w-1/3" :shine="true"></Skeleton>
                            <p v-else class="font-bold">{{ socialPlatform.name }}</p>

                            <Skeleton v-if="isLoadingStore" width="w-4/5" :shine="true"></Skeleton>
                            <p v-else class="text-xs">{{ socialPlatform.description }}</p>

                        </div>

                    </div>

                    <Skeleton v-if="isLoadingStore" width="w-5" height="h-5" :shine="true"></Skeleton>
                    <div v-else class="rounded-md border p-1 border-transparent hover:border-gray-300 hover:bg-gray-50">
                        <ExternalLink size="20" class="text-gray-500"></ExternalLink>
                    </div>

                </div>
            </a>

        </div>

    </div>

</template>

<script>

    import Skeleton from '@Partials/Skeleton.vue';
    import { ExternalLink } from 'lucide-vue-next';

    export default {
        inject: ['storeState'],
        components: {
            Skeleton, ExternalLink
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            socialPlatforms() {

                return [
                    {
                        name: 'Facebook',
                        description: 'Reach your customers by posting your store link',
                        link: this.isLoadingStore ? null : `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(this.store.web_link)}`
                    },
                    {
                        name: 'Whatsapp',
                        description: 'Share your store with friends and family instantly',
                        link: this.isLoadingStore ? null : `https://wa.me/?text=${encodeURIComponent(
                            `${this.store.name}\n\nVisit our store: ${this.store.web_link}`
                        )}`
                    },
                    {
                        name: 'Instagram',
                        description: 'Add your store link to your bio and attract visitors',
                        link: this.isLoadingStore ? null : 'https://www.instagram.com/accounts/edit'
                    },
                    {
                        name: 'LinkedIn',
                        description: 'Share your store link with professional connections',
                        link: this.isLoadingStore ? null : `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(this.store.web_link)}`
                    },
                ];

            }
        }
    };

</script>
