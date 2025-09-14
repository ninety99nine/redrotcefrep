<template>

    <div
        class="bg-white rounded-2xl p-4"
        v-if="designCard.type == 'text' && designCard.metadata.body?.trim() == ''">
        <Markdown :text="designCard.metadata.body"></Markdown>
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
