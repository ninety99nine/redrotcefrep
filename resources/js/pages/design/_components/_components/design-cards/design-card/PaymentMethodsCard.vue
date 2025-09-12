<template>

    <div>

        <Input
            type="text"
            class="w-full"
            placeholder="Title"
            v-model="designCard.metadata.title"
            @input="designState.saveStateDebounced('Title changed')"
            :errorText="formState.getFormError(`design_cards.${index}.metadata.title`)">
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
