<template>

    <div>

        <div class="flex items-center space-x-2 mb-4">

            <Pill :type="designCard.mode == '1' ? 'primary' : 'light'" size="sm" :action="() => designCard.mode = '1'">Content</Pill>
            <Pill :type="designCard.mode == '2' ? 'primary' : 'light'" size="sm" :action="() => designCard.mode = '2'">Design</Pill>

        </div>

        <template v-if="designCard.mode == '1'">

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

            <div class="grid grid-cols-2 h-8">

                <div class="col-span-1">
                    <Switch
                        size="xs"
                        suffixText="Show first name"
                        v-model="designCard.metadata.show_first_name"
                        @change="designState.saveStateDebounced('First name status changed')"
                    />
                </div>

                <div
                    class="col-span-1"
                    v-if="designCard.metadata.show_first_name">
                    <Input
                        type="checkbox"
                        inputLabel="Required"
                        alignItems="items-center"
                        v-model="designCard.metadata.first_name_required"
                        @change="designState.saveStateDebounced('First name status changed')"
                        :errorText="formState.getFormError(`design_cards.${index}.metadata.first_name_required`)">
                    </Input>
                </div>

            </div>

            <div class="grid grid-cols-2 h-8">

                <div class="col-span-1">
                    <Switch
                        size="xs"
                        suffixText="Show last name"
                        v-model="designCard.metadata.show_last_name"
                        @change="designState.saveStateDebounced('Last name status changed')"
                    />
                </div>

                <div
                    class="col-span-1"
                    v-if="designCard.metadata.show_last_name">
                    <Input
                        type="checkbox"
                        inputLabel="Required"
                        alignItems="items-center"
                        v-model="designCard.metadata.last_name_required"
                        @change="designState.saveStateDebounced('Last name status changed')"
                        :errorText="formState.getFormError(`design_cards.${index}.metadata.last_name_required`)">
                    </Input>
                </div>

            </div>

            <div class="grid grid-cols-2 h-8">

                <div class="col-span-1">
                    <Switch
                        size="xs"
                        suffixText="Show mobile number"
                        v-model="designCard.metadata.show_mobile_number"
                        @change="designState.saveStateDebounced('Mobile number status changed')"
                    />
                </div>

                <div
                    class="col-span-1"
                    v-if="designCard.metadata.show_mobile_number">
                    <Input
                        type="checkbox"
                        inputLabel="Required"
                        alignItems="items-center"
                        v-model="designCard.metadata.mobile_number_required"
                        @change="designState.saveStateDebounced('Mobile number status changed')"
                        :errorText="formState.getFormError(`design_cards.${index}.metadata.mobile_number_required`)">
                    </Input>
                </div>

            </div>

            <div class="grid grid-cols-2">

                <div class="col-span-1">
                    <Switch
                        size="xs"
                        suffixText="Show email"
                        v-model="designCard.metadata.show_email"
                        @change="designState.saveStateDebounced('Email status changed')"
                    />
                </div>

                <div
                    class="col-span-1"
                    v-if="designCard.metadata.show_email">
                    <Input
                        type="checkbox"
                        inputLabel="Required"
                        alignItems="items-center"
                        v-model="designCard.metadata.email_required"
                        @change="designState.saveStateDebounced('Email status changed')"
                        :errorText="formState.getFormError(`design_cards.${index}.metadata.email_required`)">
                    </Input>
                </div>

            </div>

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
