<template>

    <div
        class="bg-white rounded-2xl p-4"
        v-if="designCard.metadata.type == 'socials' && designCard.metadata.platforms?.some(platform => platform.link)">

        <div class="space-y-2">

            <div
                v-if="designCard.metadata.title"
                class="flex items-center space-x-4">
                <AtSign size="20" class="text-gray-500"></AtSign>
                <h3 class="text-lg font-semibold text-gray-900">{{ designCard.metadata.title }}</h3>
            </div>

            <div class="flex flex-wrap justify-center gap-4">

                <template
                    :key="index2"
                    v-for="(platform, index2) in designCard.metadata.platforms">

                    <a
                        target="_blank"
                        v-if="platform.link"
                        :href="platform.link"
                        :aria-label="`Visit ${platform.name}`"
                        class="w-12 h-12 flex items-center justify-center hover:scale-90 active:scale-80 transition-all duration-300 cursor-pointer">

                        <img
                            :alt="platform.name"
                            class="w-8 h-8 object-contain"
                            :src="`/images/social-media-icons/${platform.name.toLowerCase()}.png`"/>

                    </a>

                </template>

            </div>

        </div>

    </div>

</template>

<script>

    import { AtSign } from 'lucide-vue-next';

    export default {
        components: { AtSign },
        props: {
            designCard: {
                type: Object
            }
        }
    }
</script>
