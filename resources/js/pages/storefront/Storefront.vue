<template>
    <div class="space-y-4 p-4">
        <template
            :key="index"
            v-for="(designCard, index) in designCards">
            <!-- Text Card -->
            <div class="bg-white rounded-2xl p-4"
                v-if="designCard.metadata.type == 'text' && designCard.metadata.body">
                <div class="markdown-content" v-html="renderMarkdown(designCard.metadata.body)"></div>
            </div>

            <!-- Link Card -->
            <div class="bg-white rounded-2xl p-4 hover:bg-gray-50 hover:scale-95 transition-all duration-300 cursor-pointer"
                v-if="designCard.metadata.type == 'link' && designCard.metadata.title && designCard.metadata.link">
                <div class="flex items-center justify-center space-x-4">
                    <Link size="20"></Link>
                    <a :href="designCard.metadata.link" target="_blank" class="text-blue-600 hover:text-blue-800">{{ designCard.metadata.title }}</a>
                </div>
            </div>

            <!-- Video Card -->
            <div class="bg-white rounded-2xl p-4"
                v-if="designCard.metadata.type == 'video' && (designCard.metadata.title || designCard.metadata.link)">
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
                            class="absolute top-0 left-0 w-full h-full rounded-md"
                            :src="getYouTubeEmbedUrl(designCard.metadata.link)"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                        ></iframe>
                    </div>
                </div>
            </div>

            <!-- Socials Card -->
            <div class="bg-white rounded-2xl p-4"
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
                                class="w-12 h-12 flex items-center justify-center hover:scale-90 active:scale-80 transition-all duration-300 cursor-pointer"
                                :aria-label="`Visit ${platform.name}`">
                                <img
                                    :src="`/images/social-media-icons/${platform.name.toLowerCase()}.png`"
                                    :alt="platform.name"
                                    class="w-8 h-8 object-contain"
                                />
                            </a>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
import { marked } from 'marked';
import DOMPurify from 'dompurify';
import { Link, Video, AtSign } from 'lucide-vue-next';

export default {
    inject: [],
    components: {
        Link, Video, AtSign
    },
    props: {
        designCards: {
            type: Array,
            default: () => [] // Default to empty array for safety
        }
    },
    data() {
        return {}
    },
    methods: {
        renderMarkdown(text) {
            // Parse Markdown and sanitize the output
            const rawHtml = marked(text, {
                breaks: true, // Treat line breaks as <br>
                gfm: true     // Enable GitHub Flavored Markdown
            });
            return DOMPurify.sanitize(rawHtml, {
                ADD_TAGS: ['img'], // Allow images
                ADD_ATTR: ['src', 'alt'] // Allow image attributes
            });
        },
        getYouTubeEmbedUrl(url) {
            // Extract YouTube video ID from various URL formats
            const regex = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/;
            const match = url.match(regex);
            const videoId = match ? match[1] : '';
            return videoId ? `https://www.youtube.com/embed/${videoId}` : '';
        }
    },
    watch: {},
    computed: {},
    unmounted() {},
    created() {}
}
</script>
