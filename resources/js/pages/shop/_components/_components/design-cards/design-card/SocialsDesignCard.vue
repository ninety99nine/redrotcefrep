<template>

    <div :style="{
            color: designCard.metadata.design.title_color,
            backgroundColor: designCard.metadata.design.bg_color,

            marginTop: `${designCard.metadata.design.t_margin ?? 0}px`,
            marginLeft: `${designCard.metadata.design.l_margin ?? 0}px`,
            marginRight: `${designCard.metadata.design.r_margin ?? 0}px`,
            marginBottom: `${designCard.metadata.design.b_margin ?? 0}px`,

            paddingTop: `${designCard.metadata.design.t_padding ?? 0}px`,
            paddingLeft: `${designCard.metadata.design.l_padding ?? 0}px`,
            paddingRight: `${designCard.metadata.design.r_padding ?? 0}px`,
            paddingBottom: `${designCard.metadata.design.b_padding ?? 0}px`,

            borderTopLeftRadius: `${designCard.metadata.design.tl_border_radius ?? 0}px`,
            borderTopRightRadius: `${designCard.metadata.design.tr_border_radius ?? 0}px`,
            borderBottomLeftRadius: `${designCard.metadata.design.bl_border_radius ?? 0}px`,
            borderBottomRightRadius: `${designCard.metadata.design.br_border_radius ?? 0}px`,

            borderTop: `${designCard.metadata.design.t_border ?? 0}px solid ${designCard.metadata.design.border_color ?? '#000000'}`,
            borderLeft: `${designCard.metadata.design.l_border ?? 0}px solid ${designCard.metadata.design.border_color ?? '#000000'}`,
            borderRight: `${designCard.metadata.design.r_border ?? 0}px solid ${designCard.metadata.design.border_color ?? '#000000'}`,
            borderBottom: `${designCard.metadata.design.b_border ?? 0}px solid ${designCard.metadata.design.border_color ?? '#000000'}`,
        }"
        v-if="designCard.metadata.platforms?.some(platform => platform.link)">

        <div class="space-y-2">

            <div
                v-if="designCard.metadata.title"
                class="flex items-center space-x-4">
                <AtSign size="20" :style="{ color: designCard.metadata.design.icon_color }"></AtSign>
                <h3 class="text-lg font-semibold">{{ designCard.metadata.title }}</h3>
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
