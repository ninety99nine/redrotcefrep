<template>

    <div>

        <div class="flex items-center space-x-2 mb-4">

            <Pill :type="designCard.mode == '1' ? 'primary' : 'light'" size="sm" :action="() => designCard.mode = '1'">Content</Pill>
            <Pill :type="designCard.mode == '2' ? 'primary' : 'light'" size="sm" :action="() => designCard.mode = '2'">Design</Pill>

        </div>

        <template v-if="designCard.mode == '1'">

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
                    maxLength="40"
                    class="w-full mb-4"
                    placeholder="Title"
                    :showCharacterLengthCounter="true"
                    v-model="designCard.metadata.title"
                    @input="designState.saveStateDebounced('Title changed')"
                    :errorText="formState.getFormError(`design_cards.${index}.metadata.title`)">
                </Input>

                <Input
                    rows="2"
                    :resize="true"
                    type="textarea"
                    maxLength="200"
                    class="w-full mb-4"
                    :showCharacterLengthCounter="true"
                    placeholder="Additional Information"
                    v-model="designCard.metadata.description"
                    @input="designState.saveStateDebounced('Description changed')"
                    :errorText="formState.getFormError(`design_cards.${index}.metadata.description`)">
                </Input>

            </template>

        </template>

        <Designer :designCard="designCard"></Designer>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Input from '@Partials/Input.vue';
    import Switch from '@Partials/Switch.vue';
    import Designer from '@Pages/design/_components/_components/design-cards/design-card/_components/Designer.vue';

    export default {
        inject: ['formState', 'designState'],
        components: { Pill, Input, Switch, Designer },
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
