<template>

    <a
        class="block"
        target="_blank"
        @click.stop="handleLink"
        rel="noopener noreferrer"
        :href="designCard.metadata.link"
        :aria-label="designCard.metadata.title || 'Link to content'"
        :class="[hasLink ? 'cursor-pointer hover:scale-105 transition-all duration-300' : 'cursor-default']">

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
            v-if="designCard.photos?.[0]?.path || designCard.metadata.upper_text || designCard.metadata.lower_text">

            <div class="space-y-4">

                <Markdown
                    :text="designCard.metadata.upper_text"
                    v-if="isNotEmpty(designCard.metadata.upper_text)">
                </Markdown>

                <div
                    v-if="designCard.photos?.[0]?.path"
                    class="w-full aspect-square relative flex items-center justify-center rounded-lg overflow-hidden">

                    <img
                        :src="designCard.photos[0].path"
                        class="w-full h-full object-cover"
                        :alt="designCard.metadata.title || 'Image'" />

                </div>

                <Markdown
                    :text="designCard.metadata.lower_text"
                    v-if="isNotEmpty(designCard.metadata.lower_text)">
                </Markdown>

            </div>

        </div>

    </a>

</template>

<script>

    import Markdown from '@Partials/Markdown.vue';
    import { isNotEmpty } from '@Utils/stringUtils';

    export default {
        components: { Markdown },
        props: {
            designCard: {
                type: Object
            }
        },
        computed: {
            hasLink() {
                return this.isNotEmpty(this.designCard.metadata.link);
            }
        },
        methods: {
            isNotEmpty,
            handleLink(e) {
                if(!this.hasLink) {
                    e.preventDefault();
                }
            }
        }
    }
</script>
