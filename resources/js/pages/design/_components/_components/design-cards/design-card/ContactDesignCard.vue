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
            placeholder="+26772000001"
            v-model="designCard.metadata.mobile_number"
            @input="designState.saveStateDebounced('Mobile number changed')"
            :errorText="formState.getFormError(`design_cards.${index}.metadata.mobile_number`)">
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
