<template>
    <div class="text-gray-700">
        <p class="text-base font-medium">Storefront here</p>

        <template
            v-for="(designCard, index) in designCards"
            :key="index">
            <div
                class="bg-white rounded-lg p-4 mb-4"
                v-if="designCard.metadata.title || designCard.metadata.body">
                <template v-if="designCard.metadata.type === 'text'">
                    <div v-if="designCard.metadata.body" class="markdown-content" v-html="renderMarkdown(designCard.metadata.body)"></div>
                </template>
            </div>
        </template>
    </div>
</template>

<script>

import { marked } from 'marked';
import DOMPurify from 'dompurify';

export default {
    inject: [],
    components: {},
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
        }
    },
    watch: {},
    computed: {},
    unmounted() {},
    created() {}
}
</script>

<style scoped>

@reference "tailwindcss";

/* Tailwind styles for Markdown-rendered content in v-html */
.markdown-content :deep(h1) {
    @apply text-3xl font-bold text-gray-900 mb-4;
}
.markdown-content :deep(h2) {
    @apply text-2xl font-bold text-gray-900 mb-3;
}
.markdown-content :deep(h3) {
    @apply text-xl font-bold text-gray-900 mb-2;
}
.markdown-content :deep(h4) {
    @apply text-lg font-bold text-gray-900 mb-2;
}
.markdown-content :deep(h5) {
    @apply text-base font-bold text-gray-900 mb-2;
}
.markdown-content :deep(h6) {
    @apply text-sm font-bold text-gray-900 mb-2;
}
.markdown-content :deep(p) {
    @apply text-base text-gray-700 mb-4;
}
.markdown-content :deep(b, strong) {
    @apply font-bold;
}
.markdown-content :deep(i, em) {
    @apply italic;
}
.markdown-content :deep(del) {
    @apply line-through;
}
.markdown-content :deep(ul) {
    @apply list-disc pl-6 mb-4;
}
.markdown-content :deep(ol) {
    @apply list-decimal pl-6 mb-4;
}
.markdown-content :deep(li) {
    @apply mb-2;
}
.markdown-content :deep(a) {
    @apply text-blue-600 underline hover:text-blue-800;
}
.markdown-content :deep(blockquote) {
    @apply border-l-4 border-gray-300 pl-4 text-gray-600 italic mb-4;
}
.markdown-content :deep(pre) {
    @apply bg-gray-100 p-4 rounded-md overflow-x-auto mb-4;
}
.markdown-content :deep(code) {
    @apply font-mono text-sm;
}
.markdown-content :deep(table) {
    @apply w-full border-collapse mb-4;
}
.markdown-content :deep(th, td) {
    @apply border border-gray-300 p-2;
}
.markdown-content :deep(th) {
    @apply bg-gray-50 font-semibold text-gray-900;
}
.markdown-content :deep(img) {
    @apply max-w-full h-auto mb-4;
}
.markdown-content :deep(input[type="checkbox"]) {
    @apply mr-2;
}
</style>
