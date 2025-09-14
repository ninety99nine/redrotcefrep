<template>

    <div class="bg-white rounded-2xl p-4"
        v-if="designCard.type == 'video' && (designCard.metadata.title || designCard.metadata.link)">
        <div class="space-y-2">
            <div
                v-if="designCard.metadata.title"
                class="flex items-center space-x-4">
                <Video size="20" class="text-gray-500"></Video>
                <h3 class="text-lg font-semibold text-gray-900">{{ designCard.metadata.title }}</h3>
            </div>
            <div
                v-if="designCard.metadata.link"
                class="relative w-full" style="padding-bottom: 56.25%; /* 16:9 aspect ratio */">
                <iframe
                    frameborder="0"
                    allowfullscreen
                    :src="getYouTubeEmbedUrl(designCard.metadata.link)"
                    class="absolute top-0 left-0 w-full h-full rounded-md"
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
