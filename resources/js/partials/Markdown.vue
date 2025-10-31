<template>
    <div class="markdown-content whitespace-pre-line" v-html="renderMarkdown(text)"></div>
</template>

<script>
import { marked } from 'marked';
import DOMPurify from 'dompurify';

export default {
    props: {
        text: {
            type: String,
            default: ''
        }
    },
    methods: {
        renderMarkdown(text) {
            // Trim leading/trailing whitespace
            const trimmedText = text.trim();

            // Custom renderer for links
            const renderer = new marked.Renderer();

            renderer.link = function (token) {
                const titleAttr = token.title ? ` title="${token.title}"` : '';
                return `<a href="${token.href}"${titleAttr} target="_blank" rel="noopener noreferrer">${token.text}</a>`;
            };

            // Run through marked
            let rawHtml = marked(trimmedText, {
                breaks: true,
                renderer
            });

            // Clean up trailing <br>, empty <p>, or whitespace
            rawHtml = rawHtml.replace(/<p>\s*<\/p>$/, '')
                             .replace(/<br\s*\/?>$/, '')
                             .replace(/\s+$/, '');

            // Sanitize with DOMPurify
            const sanitizedHtml = DOMPurify.sanitize(rawHtml, {
                ADD_ATTR: ['title', 'target']
            });

            return sanitizedHtml;
        }
    }
};
</script>
