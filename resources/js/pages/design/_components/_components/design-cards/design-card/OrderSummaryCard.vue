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
                rows="2"
                :resize="true"
                maxLength="200"
                type="textarea"
                class="w-full mb-4"
                :showCharacterLengthCounter="true"
                placeholder="Additional Information"
                v-model="designCard.metadata.description"
                @input="designState.saveStateDebounced('Description changed')"
                :errorText="formState.getFormError(`design_cards.${index}.metadata.description`)">
            </Input>

            <div class="p-4 bg-gray-100 rounded-lg mb-4">

                <div class="w-full flex justify-between items-end space-x-4">

                    <div class="w-full">

                        <h1 class="text-sm font-bold mb-2">Apply Fees</h1>

                        <p class="text-sm text-gray-500">These fees will be applied on every order e.g administration, packaging, etc.</p>

                    </div>

                    <Button
                        size="xs"
                        type="light"
                        :leftIcon="Plus"
                        :action="addCheckoutFee">
                        <span>Add Fee</span>
                    </Button>

                </div>

                <!-- Draggable Fields -->
                <draggable
                    class="mt-4 space-y-2"
                    ghost-class="bg-yellow-50"
                    handle=".draggable-handle-3"
                    v-model="designForm.store_settings.checkout_fees"
                    v-if="designForm.store_settings.checkout_fees.length"
                    @change="designState.saveStateDebounced('Fee moved')">

                    <template
                        :key="index"
                        v-for="(checkoutFee, index) in designForm.store_settings.checkout_fees">

                        <div class="bg-white border border-gray-200 shadow rounded-lg space-y-2 p-2">

                            <!-- Name Input -->
                            <Input
                                type="text"
                                class="w-full"
                                maxLength="40"
                                placeholder="Packaging"
                                :showCharacterLengthCounter="true"
                                v-model="designForm.store_settings.checkout_fees[index].name"
                                @input="designState.saveStateDebounced('Checkout fee name changed')"
                                    :errorText="formState.getFormError(`checkout_fees.${index}.name`)">
                            </Input>

                            <div class="flex items-center space-x-2">

                                <Select
                                    class="w-60"
                                    :search="false"
                                    :options="feeTypes"
                                    placeholder="Select fee type"
                                    v-model="designForm.store_settings.checkout_fees[index].rate_type"
                                    @change="designState.saveStateDebounced('Checkout fee type changed')"
                                    :errorText="formState.getFormError(`checkout_fees.${index}.rate_type`)">
                                </Select>

                                <Input
                                    type="money"
                                    class="w-60"
                                    :currency="store.currency.code"
                                    v-model="designForm.store_settings.checkout_fees[index].flat_rate"
                                    @input="designState.saveStateDebounced('Checkout fee amount changed')"
                                    :errorText="formState.getFormError(`checkout_fees.${index}.flat_rate`)"
                                    v-if="designForm.store_settings.checkout_fees[index].rate_type == RATE_TYPES.FLAT">
                                </Input>

                                <Input
                                    v-else
                                    class="w-60"
                                    type="percentage"
                                    @input="designState.saveStateDebounced('Checkout fee amount changed')"
                                    v-model="designForm.store_settings.checkout_fees[index].percentage_rate"
                                    :errorText="formState.getFormError(`checkout_fees.${index}.percentage_rate`)">
                                </Input>

                                <!-- Remove Button -->
                                <div class="w-full space-x-4 flex items-center justify-end">

                                    <Trash
                                        size="16"
                                        @click.stop="() => removeCheckoutFee(index)"
                                        class="cursor-pointer text-red-500 hover:text-red-600 active:text-red-600 active:scale-95 transition-all">
                                    </Trash>

                                    <!-- Drag & Drop Handle -->
                                    <Move @click.stop size="16" class="draggable-handle-3 cursor-grab active:cursor-grabbing text-gray-500 hover:text-yellow-500"></Move>

                                </div>

                            </div>

                        </div>

                    </template>

                </draggable>

            </div>

            <Input
                class="mb-4"
                type="checkbox"
                inputLabel="Combine fees"
                v-model="designForm.store_settings.combine_fees"
                :errorText="formState.getFormError(`combine_fees`)"
                @change="designState.saveStateDebounced('Combine fees status changed')">
            </Input>

            <Input
                class="mb-4"
                type="checkbox"
                inputLabel="Combine discounts"
                v-model="designForm.store_settings.combine_discounts"
                :errorText="formState.getFormError(`combine_discounts`)"
                @change="designState.saveStateDebounced('Combine discounts status changed')">
            </Input>

        </template>

        <Designer :designCard="designCard"></Designer>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Input from '@Partials/Input.vue';
    import Button from '@Partials/Button.vue';
    import Select from '@Partials/Select.vue';
    import { RATE_TYPES } from '@Enums/enums.js';
    import { capitalize } from '@Utils/stringUtils.js';
    import { Move, Trash, Plus } from 'lucide-vue-next';
    import { VueDraggableNext } from 'vue-draggable-next';
    import Designer from '@Pages/design/_components/_components/design-cards/design-card/_components/Designer.vue';

    export default {
        inject: ['formState', 'storeState', 'designState'],
        components: { Pill, Move, Trash, Input, Button, Select, draggable: VueDraggableNext, Designer },
        props: {
            index: {
                type: Number
            },
            designCard: {
                type: Object
            }
        },
        data() {
            return {
                Plus,
                RATE_TYPES
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            designForm() {
                return this.designState.designForm;
            },
            feeTypes() {
                return Object.entries(RATE_TYPES).map(([key, value]) => ({
                    label: capitalize(value),
                    value: value
                }));
            }
        },
        methods: {
            addCheckoutFee() {
                this.designForm.store_settings.checkout_fees.unshift({
                    'name': '',
                    'flat_rate': '0',
                    'rate_type': 'flat',
                    'percentage_rate': '0'
                });
                this.designState.saveStateDebounced('Checkout fee added');
            },
            removeCheckoutFee(index) {
                this.designForm.store_settings.checkout_fees.splice(index, 1);
                this.designState.saveStateDebounced('Checkout fee removed');
            }
        }
    }
</script>
