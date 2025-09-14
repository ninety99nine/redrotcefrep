<template>

    <div
        class="bg-white rounded-2xl p-4"
        v-if="designCard.type === 'image' && (designCard.photos?.[0]?.path || designCard.metadata.upper_text || designCard.metadata.lower_text)">

        <div class="space-y-4">

            <Markdown
                v-if="designCard.metadata.upper_text != null && designCard.metadata.upper_text?.trim() != ''"
                :text="designCard.metadata.upper_text">
            </Markdown>

            <div
                v-if="designCard.photos?.[0]?.path"
                class="w-full aspect-square relative flex items-center justify-center bg-gray-100 rounded-lg overflow-hidden">
                <a
                    target="_blank"
                    class="w-full h-full"
                    v-if="designCard.metadata.link"
                    :href="designCard.metadata.link">
                    <img
                        :src="designCard.photos[0].path"
                        class="w-full h-full object-cover"
                        :alt="designCard.metadata.title || 'Image'" />
                </a>
                <img
                    v-else
                    :src="designCard.photos[0].path"
                    class="w-full h-full object-cover"
                    :alt="designCard.metadata.title || 'Image'" />
            </div>

            <Markdown
                v-if="designCard.metadata.lower_text != null && designCard.metadata.lower_text?.trim() != ''"
                :text="designCard.metadata.lower_text">
            </Markdown>

        </div>

    </div>

</template>

<script>

    import Markdown from '@Partials/Markdown.vue';

    export default {
        components: { Markdown },
        props: {
            designCard: {
                type: Object
            }
        }
    }
</script>
