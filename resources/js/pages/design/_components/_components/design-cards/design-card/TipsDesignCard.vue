<template>

    <div>

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

    </div>


</template>

<script>

    import Input from '@Partials/Input.vue';
    import Switch from '@Partials/Switch.vue';
    import SelectTags from '@Partials/SelectTags.vue';

    export default {
        inject: ['formState', 'designState'],
        components: { Input, Switch, SelectTags },
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
