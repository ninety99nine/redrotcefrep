<template>

    <div>

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
                v-model="designCard.metadata.checkout_fees"
                v-if="designCard.metadata.checkout_fees.length"
                @change="designState.saveStateDebounced('Fee moved')">

                <template
                    :key="index"
                    v-for="(checkoutFee, index) in designCard.metadata.checkout_fees">

                    <div class="bg-white border border-gray-200 shadow rounded-lg space-y-2 p-2">

                        <!-- Name Input -->
                        <Input
                            type="text"
                            class="w-full"
                            placeholder="Packaging"
                            v-model="designCard.metadata.checkout_fees[index].name"
                            @input="designState.saveStateDebounced('Checkout fee name changed')">
                        </Input>

                        <div class="flex items-center space-x-2">

                            <Select
                                class="w-60"
                                :search="false"
                                :options="feeTypes"
                                placeholder="Select fee type"
                                v-model="designCard.metadata.checkout_fees[index].rate_type"
                                @change="designState.saveStateDebounced('Checkout fee type changed')">
                            </Select>

                            <Input
                                type="money"
                                class="w-60"
                                :currency="store.currency.code"
                                v-model="designCard.metadata.checkout_fees[index].flat_rate"
                                v-if="designCard.metadata.checkout_fees[index].rate_type == RATE_TYPES.FLAT"
                                @input="designState.saveStateDebounced('Checkout fee amount changed')">
                            </Input>

                            <Input
                                v-else
                                class="w-60"
                                type="percentage"
                                v-model="designCard.metadata.checkout_fees[index].percentage_rate"
                                @input="designState.saveStateDebounced('Checkout fee amount changed')">
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
            v-model="designCard.metadata.combine_fees"
            @change="designState.saveStateDebounced('Combine fees status changed')">
        </Input>

        <Input
            class="mb-4"
            type="checkbox"
            inputLabel="Combine discounts"
            v-model="designCard.metadata.combine_discounts"
            @change="designState.saveStateDebounced('Combine discounts status changed')">
        </Input>

    </div>


</template>

<script>

    import Input from '@Partials/Input.vue';
    import Button from '@Partials/Button.vue';
    import Select from '@Partials/Select.vue';
    import { RATE_TYPES } from '@Enums/enums.js';
    import { capitalize } from '@Utils/stringUtils.js';
    import { Move, Trash, Plus } from 'lucide-vue-next';
    import { VueDraggableNext } from 'vue-draggable-next';

    export default {
        inject: ['formState', 'storeState', 'designState'],
        components: { Move, Trash, Input, Button, Select, draggable: VueDraggableNext },
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
            feeTypes() {
                return Object.entries(RATE_TYPES).map(([key, value]) => ({
                    label: capitalize(value),
                    value: value
                }));
            }
        },
        methods: {
            addCheckoutFee() {
                this.designCard.metadata.checkout_fees.unshift({
                    'name': '',
                    'flat_rate': '0',
                    'rate_type': 'flat',
                    'percentage_rate': '0'
                });
            },
            removeCheckoutFee(index) {
                this.designCard.metadata.checkout_fees.splice(index, 1);
            }
        }
    }
</script>
