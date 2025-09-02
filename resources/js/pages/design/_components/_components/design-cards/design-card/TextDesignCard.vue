<template>

    <div>

        <div class="flex items-center space-x-2 mb-4">

            <Pill :type="designCard.metadata.mode == 'content' ? 'primary' : 'light'" size="sm" :action="() => designCard.metadata.mode = 'content'">Content</Pill>
            <Pill :type="designCard.metadata.mode == 'design' ? 'primary' : 'light'" size="sm" :action="() => designCard.metadata.mode = 'design'">Design</Pill>

        </div>

        <vue-easymde
            class="text-xs"
            :options="editorOptions"
            v-model="designCard.metadata.body"
            @change="designState.saveStateDebounced('Text content changed')" />

    </div>


</template>

<script>

    import Pill from '@Partials/Pill.vue';

    export default {
        inject: ['designState'],
        components: { Pill },
        props: {
            index: {
                type: Number
            },
            designCard: {
                type: Object
            },
            editorOptions: {
                type: Object
            }
        }
    }
</script>
