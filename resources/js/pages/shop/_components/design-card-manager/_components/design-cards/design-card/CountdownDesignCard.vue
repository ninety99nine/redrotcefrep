<template>

    <div :style="{
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
        v-if="designCard.metadata.body || designCard.metadata.date || designCard.photos?.[0]?.path">

            <Markdown
                v-if="isNotEmpty(designCard.metadata.upper_text)"
                :text="designCard.metadata.upper_text">
            </Markdown>

            <div
                v-if="designCard.photos?.[0]?.path"
                class="w-full aspect-square relative flex items-center justify-center rounded-lg overflow-hidden">

                <img
                    alt="Countdown Image"
                    :src="designCard.photos[0].path"
                    class="w-full h-full object-cover" />

            </div>

            <Countdown
                type="design2"
                class="w-full"
                    :style="{
                    color: designCard.metadata.design.countdown_text_color
                }"
                :time="designCard.metadata.date">
                <template #prefix="{ hasExpired }">
                    <span v-if="hasExpired" class="text-lg font-semibold text-red-600">Countdown Ended</span>
                </template>
            </Countdown>

            <Markdown
                v-if="isNotEmpty(designCard.metadata.lower_text)"
                :text="designCard.metadata.lower_text">
            </Markdown>

    </div>

</template>

<script>

    import Markdown from '@Partials/Markdown.vue';
    import Countdown from '@Partials/Countdown.vue';
    import { isNotEmpty } from '@Utils/stringUtils';

    export default {
        components: { Markdown, Countdown },
        props: {
            designCard: {
                type: Object
            }
        },
        methods: {
            isNotEmpty: isNotEmpty
        }
    }
</script>
