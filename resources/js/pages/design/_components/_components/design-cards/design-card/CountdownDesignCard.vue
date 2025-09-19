<template>

    <div>

        <div class="flex items-center space-x-2 mb-4">

            <Pill :type="designCard.mode == '1' ? 'primary' : 'light'" size="sm" :action="() => designCard.mode = '1'">Countdown</Pill>
            <Pill :type="designCard.mode == '3' ? 'primary' : 'light'" size="sm" :action="() => designCard.mode = '3'">Upper text</Pill>
            <Pill :type="designCard.mode == '4' ? 'primary' : 'light'" size="sm" :action="() => designCard.mode = '4'">Lower text</Pill>
            <Pill :type="designCard.mode == '2' ? 'primary' : 'light'" size="sm" :action="() => designCard.mode = '2'">Design</Pill>

        </div>

        <template v-if="designCard.mode == '1'">

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
    import Datepicker from '@Partials/Datepicker.vue';
    import Designer from '@Pages/design/_components/_components/design-cards/design-card/_components/Designer.vue';

    export default {
        inject: ['formState', 'designState'],
        components: { Pill, Input, Datepicker, Designer },
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
