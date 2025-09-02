<template>

    <div>

        <div class="flex items-center space-x-2 mb-4">

            <Pill :type="designCard.metadata.mode == 'map' ? 'primary' : 'light'" size="sm" :action="() => designCard.metadata.mode = 'map'">Map</Pill>
            <Pill :type="designCard.metadata.mode == 'upper_text' ? 'primary' : 'light'" size="sm" :action="() => designCard.metadata.mode = 'upper_text'">Upper text</Pill>
            <Pill :type="designCard.metadata.mode == 'lower_text' ? 'primary' : 'light'" size="sm" :action="() => designCard.metadata.mode = 'lower_text'">Lower text</Pill>
            <Pill :type="designCard.metadata.mode == 'design' ? 'primary' : 'light'" size="sm" :action="() => designCard.metadata.mode = 'design'">Design</Pill>

        </div>

        <template v-if="designCard.metadata.mode == 'map'">

            <AddressInput
                class="mb-4"
                height="250px"
                :onlyValidate="true"
                :pinLocationOnMap="true"
                triggerClass="space-y-4"
                @onDeleted="unsetAddress"
                :address="designCard.metadata.address"
                @onValidated="(address) => setAddress(address)">
            </AddressInput>

            <Input
                type="checkbox"
                inputLabel="Show address"
                v-model="designCard.metadata.show_address"
                @change="designState.saveStateDebounced(`Show address status changed`)"
                :errorText="formState.getFormError(`design_cards.${index}.metadata.show_address`)">
            </Input>

        </template>

        <vue-easymde
            class="mb-4"
            :options="editorOptions"
            v-model="designCard.metadata.upper_text"
            v-if="designCard.metadata.mode == 'upper_text'"
            @change="designState.saveStateDebounced('Text content changed')" />

        <vue-easymde
            class="mb-4"
            :options="editorOptions"
            v-model="designCard.metadata.lower_text"
            v-else-if="designCard.metadata.mode == 'lower_text'"
            @change="designState.saveStateDebounced('Text content changed')" />

    </div>


</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Input from '@Partials/Input.vue';
    import AddressInput from '@Partials/AddressInput.vue';

    export default {
        inject: ['formState', 'designState'],
        components: { Pill, Input, AddressInput },
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
        methods: {
            setAddress(address) {
                this.designCard.address = address;
                this.designState.saveStateDebounced('Address added');
            },
            unsetAddress() {
                this.designCard.address = null;
                this.designState.saveStateDebounced('Address removed');
            }
        }
    }
</script>
