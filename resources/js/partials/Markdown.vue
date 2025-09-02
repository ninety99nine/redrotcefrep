<template>
    <div class="markdown-content whitespace-pre-wrap" v-html="renderMarkdown(text)"></div>
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

                // Custom renderer for links
                const renderer = new marked.Renderer();

                renderer.link = function(token) {
                    const titleAttr = token.title ? ` title="${token.title}"` : '';
                    return `<a href="${token.href}"${titleAttr} target="_blank" rel="noopener noreferrer">${token.text}</a>`;
                };

                // Step 2: run through marked
                let rawHtml = marked(text, {
                    breaks: true,
                    renderer
                });

                return DOMPurify.sanitize(rawHtml, {
                    ADD_ATTR: ['title', 'target']
                });

            }
        }
    };
</script>
