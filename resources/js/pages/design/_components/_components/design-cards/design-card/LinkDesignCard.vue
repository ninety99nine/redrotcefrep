<template>

    <div>

        <div class="flex items-center space-x-2 mb-4">

            <Pill :type="designCard.mode == '1' ? 'primary' : 'light'" size="sm" :action="() => designCard.mode = '1'">Content</Pill>
            <Pill :type="designCard.mode == '2' ? 'primary' : 'light'" size="sm" :action="() => designCard.mode = '2'">Design</Pill>

        </div>

        <template v-if="designCard.mode == '1'">

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
                type="text"
                label="Link"
                class="w-full"
                placeholder="https://"
                v-model="designCard.metadata.link"
                @input="designState.saveStateDebounced('Link changed')"
                tooltipContent="Include https:// at the begining of your link"
                :errorText="formState.getFormError(`design_cards.${index}.metadata.link`)">
            </Input>

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
