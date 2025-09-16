<template>

    <div>

        <div class="flex items-center space-x-2 mb-4">

            <Pill :type="designCard.metadata.mode == 'divider' ? 'primary' : 'light'" size="sm" :action="() => designCard.metadata.mode = 'divider'">Divider</Pill>
            <Pill :type="designCard.metadata.mode == 'design' ? 'primary' : 'light'" size="sm" :action="() => designCard.metadata.mode = 'design'">Design</Pill>

        </div>

        <template v-if="designCard.metadata.mode == 'divider'">

            <div class="grid grid-cols-2 gap-4">

                <Select
                    width="w-full"
                    label="Divider"
                    :options="dividerOptions"
                    v-model="designCard.metadata.divider"
                    @change="designState.saveStateDebounced('Divider changed')"
                    :errorText="formState.getFormError(`design_cards.${index}.metadata.divider`)">
                </Select>

                <Input
                    min="1"
                    type="number"
                    class="w-full"
                    label="Thickness"
                    v-model="designCard.metadata.thickness"
                    @input="designState.saveStateDebounced('Thickness changed')"
                    :errorText="formState.getFormError(`design_cards.${index}.metadata.thickness`)">
                </Input>

            </div>

        </template>

        <Designer :designCard="designCard"></Designer>

    </div>


</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Input from '@Partials/Input.vue';
    import Select from '@Partials/Select.vue';
    import Designer from '@Pages/design/_components/_components/design-cards/design-card/_components/Designer.vue';

    export default {
        inject: ['formState', 'designState'],
        components: { Pill, Input, Select, Designer },
        props: {
            index: {
                type: Number
            },
            designCard: {
                type: Object
            },
            editorOptions: {
                type: Object
            }
        },
        data() {
            return {
                dividerOptions: [
                    { label: 'None', value: 'none'},
                    { label: 'Solid line', value: 'solid'},
                    { label: 'Dashed line', value: 'dashed'},
                    { label: 'Dotted line', value: 'dotted'},
                ]
            }
        }
    }
</script>
