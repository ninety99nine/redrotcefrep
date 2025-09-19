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
                suffixText="Show tips"
                v-model="designCard.metadata.show_tips"
                @change="designState.saveStateDebounced('Tips status changed')"
            />

            <template v-if="designCard.metadata.show_tips">

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

                <SelectTags
                    class="mb-4"
                    :options="tips"
                    placeholder="Add tip option"
                    v-model="designCard.metadata.tips"
                    :errorText="formState.getFormError('tips')"
                    @change="designState.saveStateDebounced('Tips changed')" />

                <Input
                    type="checkbox"
                    inputLabel="Show specify tip"
                    v-model="designCard.metadata.show_specify_tip"
                    @change="designState.saveStateDebounced('Specify tip status changed')">
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
    import SelectTags from '@Partials/SelectTags.vue';
    import Designer from '@Pages/design/_components/_components/design-cards/design-card/_components/Designer.vue';

    export default {
        inject: ['formState', 'designState'],
        components: { Pill, Input, Switch, SelectTags, Designer },
        props: {
            index: {
                type: Number
            },
            designCard: {
                type: Object
            }
        },
        computed: {
            tips() {
                return ['5', '10', '15', '20', '25', '30'].map((percentage) => {
                    return {
                        label: `${percentage}%`,
                        value: percentage
                    }
                });
            }
        }
    }
</script>
