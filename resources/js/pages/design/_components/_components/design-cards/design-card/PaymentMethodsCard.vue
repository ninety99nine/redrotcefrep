<template>

    <div class="space-y-4">

        <Input
            type="text"
            class="w-full"
            placeholder="Title"
            v-model="designCard.metadata.title"
            @input="designState.saveStateDebounced('Title changed')"
            :errorText="formState.getFormError(`design_cards.${index}.metadata.title`)">
        </Input>

        <Input
            type="text"
            class="w-full"
            placeholder="Subtitle"
            v-model="designCard.metadata.subtitle"
            @input="designState.saveStateDebounced('Subtitle changed')"
            :errorText="formState.getFormError(`design_cards.${index}.metadata.subtitle`)">
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
