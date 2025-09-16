<template>

    <div>

        <div class="flex items-center space-x-2 mb-4">

            <Pill :type="designCard.metadata.mode == 'content' ? 'primary' : 'light'" size="sm" :action="() => designCard.metadata.mode = 'content'">Content</Pill>
            <Pill :type="designCard.metadata.mode == 'design' ? 'primary' : 'light'" size="sm" :action="() => designCard.metadata.mode = 'design'">Design</Pill>

        </div>

        <template v-if="designCard.metadata.mode == 'content'">

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
