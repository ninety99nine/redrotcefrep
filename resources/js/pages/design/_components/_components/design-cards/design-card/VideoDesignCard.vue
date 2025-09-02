<template>

    <div>

        <Input
            type="text"
            class="w-full mb-4"
            placeholder="Title"
            v-model="designCard.metadata.title"
            @input="designState.saveStateDebounced('Title changed')"
            :errorText="formState.getFormError(`design_cards.${index}.metadata.title`)">
        </Input>

        <Input
            type="text"
            class="w-full"
            label="Video link"
            v-model="designCard.metadata.link"
            placeholder="Youtube or Tiktok video link"
            @input="designState.saveStateDebounced('Video link changed')"
            :errorText="formState.getFormError(`design_cards.${index}.metadata.link`)"
            tooltipContent="Copy the link from the web browser (not from the app) to add a Tiktok video">
        </Input>

    </div>


</template>

<script>

    import Input from '@Partials/Input.vue';

    export default {
        inject: ['formState', 'designState'],
        components: { Input },
        props: {
            index: {
                type: Number
            },
            designCard: {
                type: Object
            }
        }
    }
</script>
