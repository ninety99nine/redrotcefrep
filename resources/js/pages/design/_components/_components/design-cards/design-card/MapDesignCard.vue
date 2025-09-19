<template>

    <div>

        <div class="flex items-center space-x-2 mb-4">

            <Pill :type="designCard.mode == '1' ? 'primary' : 'light'" size="sm" :action="() => designCard.mode = '1'">Map</Pill>
            <Pill :type="designCard.mode == '3' ? 'primary' : 'light'" size="sm" :action="() => designCard.mode = '3'">Upper text</Pill>
            <Pill :type="designCard.mode == '4' ? 'primary' : 'light'" size="sm" :action="() => designCard.mode = '4'">Lower text</Pill>
            <Pill :type="designCard.mode == '2' ? 'primary' : 'light'" size="sm" :action="() => designCard.mode = '2'">Design</Pill>

        </div>

        <template v-if="designCard.mode == '1'">

            <AddressInput
                class="mb-4"
                height="250px"
                :onlyValidate="true"
                :pinLocationOnMap="true"
                triggerClass="space-y-4"
                @onDeleted="unsetAddress"
                :address="designCard.address"
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
            v-if="designCard.mode == '3'"
            v-model="designCard.metadata.upper_text"
            @change="designState.saveStateDebounced('Text content changed')" />

        <vue-easymde
            class="mb-4"
            :options="editorOptions"
            v-else-if="designCard.mode == '4'"
            v-model="designCard.metadata.lower_text"
            @change="designState.saveStateDebounced('Text content changed')" />

        <Designer :designCard="designCard"></Designer>

    </div>


</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Input from '@Partials/Input.vue';
    import AddressInput from '@Partials/AddressInput.vue';
    import Designer from '@Pages/design/_components/_components/design-cards/design-card/_components/Designer.vue';

    export default {
        inject: ['formState', 'designState'],
        components: { Pill, Input, AddressInput, Designer },
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
