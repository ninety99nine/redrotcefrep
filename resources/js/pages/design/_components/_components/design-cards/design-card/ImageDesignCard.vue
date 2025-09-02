<template>

    <div>

        <div class="flex items-center space-x-2 mb-4">

            <Pill :type="designCard.metadata.mode == 'image' ? 'primary' : 'light'" size="sm" :action="() => designCard.metadata.mode = 'image'">Image</Pill>
            <Pill :type="designCard.metadata.mode == 'upper_text' ? 'primary' : 'light'" size="sm" :action="() => designCard.metadata.mode = 'upper_text'">Upper text</Pill>
            <Pill :type="designCard.metadata.mode == 'lower_text' ? 'primary' : 'light'" size="sm" :action="() => designCard.metadata.mode = 'lower_text'">Lower text</Pill>
            <Pill :type="designCard.metadata.mode == 'design' ? 'primary' : 'light'" size="sm" :action="() => designCard.metadata.mode = 'design'">Design</Pill>

        </div>

        <template v-if="designCard.metadata.mode == 'image'">

            <Input
                type="file"
                class="mb-4"
                :maxFiles="1"
                :imagePreviewGridCols="1"
                v-model="designCard.photos"
                singleFileUploadMessage="Image attached"
                @change="(photos) => designState.saveStateDebounced(photos.length ? 'Image added' : 'Image removed')">
            </Input>

            <Input
                type="text"
                label="Link"
                class="w-full mb-4"
                placeholder="https://"
                secondaryLabel="(optional)"
                v-model="designCard.metadata.link"
                @input="designState.saveStateDebounced('Link changed')"
                tooltipContent="Include https:// at the begining of your link"
                description="We will redirect to this link when image is clicked"
                :errorText="formState.getFormError(`design_cards.${index}.metadata.link`)">
            </Input>

        </template>

        <vue-easymde
            class="mb-4"
            :options="editorOptions"
            v-model="designCard.metadata.upper_text"
            v-if="designCard.metadata.mode == 'upper_text'"
            @change="designState.saveStateDebounced('Text content changed')" />

        <vue-easymde
            class="mb-4"
            :options="editorOptions"
            v-model="designCard.metadata.lower_text"
            v-else-if="designCard.metadata.mode == 'lower_text'"
            @change="designState.saveStateDebounced('Text content changed')" />

    </div>


</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Input from '@Partials/Input.vue';

    export default {
        inject: ['formState', 'designState'],
        components: { Pill, Input },
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
