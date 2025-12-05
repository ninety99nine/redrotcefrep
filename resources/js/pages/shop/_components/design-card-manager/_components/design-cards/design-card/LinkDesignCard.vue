<template>

    <a
        class="block"
        target="_blank"
        rel="noopener noreferrer"
        :href="designCard.metadata.link"
        :aria-label="designCard.metadata.title">

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
        v-if="designCard.metadata.title && designCard.metadata.link"
        class="hover:scale-105 transition-all duration-300 cursor-pointer">

            <div
                v-if="designCard.metadata.title"
                class="flex items-center justify-center space-x-4">
                <Link size="20" :style="{ color: designCard.metadata.design.icon_color }"></Link>
                <h3 class="text-sm md:text-base font-semibold">{{ designCard.metadata.title }}</h3>
            </div>

        </div>

    </a>

</template>

<script>

    import { Link } from 'lucide-vue-next';

    export default {
        components: { Link },
        props: {
            designCard: {
                type: Object
            }
        }
    }
</script>
