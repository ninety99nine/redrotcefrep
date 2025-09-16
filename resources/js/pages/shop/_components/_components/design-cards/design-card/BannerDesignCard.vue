<template>

    <a
        class="block"
        target="_blank"
        :href="designCard.metadata.link">
        <div
            v-if="designCard.metadata.title"
            :style="{
                color: designCard.metadata.design.text_color,
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
            class="flex justify-center hover:py-6 hover:scale-105 transition-all duration-300 cursor-pointer">
            <span>{{ designCard.metadata.title }}</span>
        </div>
    </a>

</template>

<script>
    export default {
        props: {
            designCard: {
                type: Object
            }
        }
    }
</script>
