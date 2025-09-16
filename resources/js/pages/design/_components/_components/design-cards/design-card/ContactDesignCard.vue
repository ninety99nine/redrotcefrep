<template>

    <div>

        <div class="flex items-center space-x-2 mb-4">

            <Pill :type="designCard.metadata.mode == 'contact' ? 'primary' : 'light'" size="sm" :action="() => designCard.metadata.mode = 'contact'">Contact</Pill>
            <Pill :type="designCard.metadata.mode == 'design' ? 'primary' : 'light'" size="sm" :action="() => designCard.metadata.mode = 'design'">Design</Pill>

        </div>

        <template v-if="designCard.metadata.mode == 'contact'">

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
