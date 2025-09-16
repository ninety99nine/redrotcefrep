<template>

    <div>

        <div class="flex items-center space-x-2 mb-4">

            <Pill :type="designCard.metadata.mode == 'content' ? 'primary' : 'light'" size="sm" :action="() => designCard.metadata.mode = 'content'">Content</Pill>
            <Pill :type="designCard.metadata.mode == 'design' ? 'primary' : 'light'" size="sm" :action="() => designCard.metadata.mode = 'design'">Design</Pill>

        </div>

        <template v-if="designCard.metadata.mode == 'content'">

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

        <Designer :designCard="designCard"></Designer>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Input from '@Partials/Input.vue';
    import Designer from '@Pages/design/_components/_components/design-cards/design-card/_components/Designer.vue';

    export default {
        inject: ['formState', 'designState'],
        components: { Pill, Input, Designer },
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
