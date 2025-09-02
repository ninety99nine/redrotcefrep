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
            label="Link"
            class="w-full"
            placeholder="https://"
            v-model="designCard.metadata.link"
            @input="designState.saveStateDebounced('Link changed')"
            tooltipContent="Include https:// at the begining of your link"
            :errorText="formState.getFormError(`design_cards.${index}.metadata.link`)">
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
