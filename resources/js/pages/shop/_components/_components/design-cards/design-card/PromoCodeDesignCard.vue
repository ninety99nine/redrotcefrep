<template>

    <div>

        <Switch
            size="xs"
            class="mb-4"
            suffixText="Show promo code"
            v-model="designCard.metadata.show_promo_code"
            @change="designState.saveStateDebounced('Promo code status changed')"
        />

        <template v-if="designCard.metadata.show_promo_code">

            <Input
                type="text"
                class="w-full"
                placeholder="Title"
                v-model="designCard.metadata.title"
                @input="designState.saveStateDebounced('Title changed')"
                :errorText="formState.getFormError(`design_cards.${index}.metadata.title`)">
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
