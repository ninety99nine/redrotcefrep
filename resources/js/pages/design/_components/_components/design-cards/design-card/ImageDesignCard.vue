<template>

    <div>

        <div class="flex items-center space-x-2 mb-4">

            <Pill :type="designCard.mode == '1' ? 'primary' : 'light'" size="sm" :action="() => designCard.mode = '1'">Image</Pill>
            <Pill :type="designCard.mode == '3' ? 'primary' : 'light'" size="sm" :action="() => designCard.mode = '3'">Upper text</Pill>
            <Pill :type="designCard.mode == '4' ? 'primary' : 'light'" size="sm" :action="() => designCard.mode = '4'">Lower text</Pill>
            <Pill :type="designCard.mode == '2' ? 'primary' : 'light'" size="sm" :action="() => designCard.mode = '2'">Design</Pill>

        </div>

        <template v-if="designCard.mode == '1'">

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
            v-if="designCard.mode == '3'"
            v-model="designCard.metadata.upper_text"
            @change="designState.saveStateDebounced('Text content changed')" />

        <vue-easymde
            class="mb-4"
            :options="editorOptions"
            v-else-if="designCard.mode == '4'"
            v-model="designCard.metadata.lower_text"
            @change="designState.saveStateDebounced('Text content changed')" />

        <Designer :designCard="designCard"></Designer>

    </div>


</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Input from '@Partials/Input.vue';
    import Designer from '@Pages/design/_components/_components/design-cards/design-card/_components/Designer.vue';

    export default {
        inject: ['formState', 'designState'],
        components: { Pill, Input, Designer },
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
