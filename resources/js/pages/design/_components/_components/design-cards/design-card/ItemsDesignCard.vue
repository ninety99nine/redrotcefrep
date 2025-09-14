<template>

    <div>

        <Switch
            size="xs"
            class="mb-4"
            suffixText="Show items"
            v-model="designCard.metadata.show_items"
            @change="designState.saveStateDebounced('Items status changed')"
        />

        <template v-if="designCard.metadata.show_items">

            <Input
                type="text"
                class="w-full mb-4"
                placeholder="Title"
                v-model="designCard.metadata.title"
                @input="designState.saveStateDebounced('Title changed')"
                :errorText="formState.getFormError(`design_cards.${index}.metadata.title`)">
            </Input>

            <Input
                rows="2"
                type="textarea"
                class="w-full mb-4"
                placeholder="Additional Information"
                v-model="designCard.metadata.description"
                @input="designState.saveStateDebounced('Description changed')"
                :errorText="formState.getFormError(`design_cards.${index}.metadata.description`)">
            </Input>

        </template>

    </div>


</template>

<script>

    import Input from '@Partials/Input.vue';
    import Switch from '@Partials/Switch.vue';

    export default {
        inject: ['formState', 'designState'],
        components: { Input, Switch },
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
