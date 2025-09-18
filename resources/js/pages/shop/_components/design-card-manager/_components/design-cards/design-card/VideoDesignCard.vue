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
        v-if="designCard.metadata.title || designCard.metadata.link">
        <div class="space-y-2">
            <div
                v-if="designCard.metadata.title"
                class="flex items-center space-x-4">
                <Video size="20" :style="{ color: designCard.metadata.design.icon_color }"></Video>
                <h3 class="text-lg font-semibold">{{ designCard.metadata.title }}</h3>
            </div>
            <div
                v-if="designCard.metadata.link"
                class="relative w-full" style="padding-bottom: 56.25%; /* 16:9 aspect ratio */">
                <iframe
                    frameborder="0"
                    allowfullscreen
                    :src="getYouTubeEmbedUrl(designCard.metadata.link)"
                    class="absolute top-0 left-0 w-full h-full rounded-sm"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                </iframe>
            </div>
        </div>
    </div>

</template>

<script>

    import { Video } from 'lucide-vue-next';

    export default {
        components: { Video },
        props: {
            designCard: {
                type: Object
            }
        },
        methods: {
            getYouTubeEmbedUrl(url) {
                // Extract YouTube video ID from various URL formats
                const regex = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/;
                const match = url.match(regex);
                const videoId = match ? match[1] : '';
                return videoId ? `https://www.youtube.com/embed/${videoId}` : '';
            }
        }
    }
</script>
