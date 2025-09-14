<template>

    <div>

        <div class="flex items-center space-x-2 mb-4">

            <Pill :type="designCard.metadata.mode == 'countdown' ? 'primary' : 'light'" size="sm" :action="() => designCard.metadata.mode = 'countdown'">Countdown</Pill>
            <Pill :type="designCard.metadata.mode == 'upper_text' ? 'primary' : 'light'" size="sm" :action="() => designCard.metadata.mode = 'upper_text'">Upper text</Pill>
            <Pill :type="designCard.metadata.mode == 'lower_text' ? 'primary' : 'light'" size="sm" :action="() => designCard.metadata.mode = 'lower_text'">Lower text</Pill>
            <Pill :type="designCard.metadata.mode == 'design' ? 'primary' : 'light'" size="sm" :action="() => designCard.metadata.mode = 'design'">Design</Pill>

        </div>

        <template v-if="designCard.metadata.mode == 'countdown'">

            <Datepicker
                class="mb-4"
                :enableTimePicker="true"
                format="dd MMM yyyy HH:mm"
                modelType="yyyy-MM-dd HH:mm"
                placeholder="Countdown to date"
                v-model="designCard.metadata.date"
                @change="designState.saveStateDebounced('Countdown date changed')"
                :errorText="formState.getFormError(`design_cards.${index}.metadata.date`)">
            </Datepicker>

            <Input
                type="file"
                :maxFiles="1"
                :imagePreviewGridCols="1"
                v-model="designCard.photos"
                singleFileUploadMessage="Image attached"
                @change="(photos) => designState.saveStateDebounced(photos.length ? 'Image added' : 'Image removed')">
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
    import Datepicker from '@Partials/Datepicker.vue';

    export default {
        inject: ['formState', 'designState'],
        components: { Pill, Input, Datepicker },
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
