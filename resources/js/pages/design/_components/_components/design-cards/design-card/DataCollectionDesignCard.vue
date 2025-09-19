<template>

    <div>

        <div class="flex items-center space-x-2 mb-4">

            <Pill :type="designCard.mode == '1' ? 'primary' : 'light'" size="sm" :action="() => designCard.mode = '1'">Content</Pill>
            <Pill :type="designCard.mode == '2' ? 'primary' : 'light'" size="sm" :action="() => designCard.mode = '2'">Design</Pill>

        </div>

        <template v-if="designCard.mode == '1'">

            <div class="space-y-4">

                <div class="flex items-start justify-between space-x-8">

                    <div class="w-full space-y-2">

                        <!-- Title Input -->
                        <Input
                            type="text"
                            class="w-full"
                            placeholder="Title"
                            v-model="designCard.metadata.title"
                            @input="designState.saveStateDebounced('Option title changed')"
                            :errorText="formState.getFormError('design_cards'+index+'metadata.title')">
                        </Input>

                        <!-- Description Input -->
                        <Input
                            type="text"
                            class="w-full"
                            v-model="designCard.metadata.description"
                            placeholder="Short description (optional)"
                            @input="designState.saveStateDebounced('Option description changed')"
                            :errorText="formState.getFormError('design_cards'+index+'metadata.description')">
                        </Input>

                    </div>

                    <!-- Required Checkbox -->
                    <Input
                        type="checkbox"
                        inputLabel="Required"
                        v-model="designCard.metadata.required"
                        @change="designState.saveStateDebounced('Required status changed')"
                        :errorText="formState.getFormError('design_cards'+index+'metadata.required')">
                    </Input>

                </div>

                <template v-if="supportsOptions">

                    <!-- Draggable Options -->
                    <draggable
                        class="space-y-0"
                        ghost-class="bg-yellow-50"
                        handle=".draggable-handle-2"
                        v-model="designCard.metadata.options">
                        <div
                            :key="index2"
                            v-for="(option, index2) in designCard.metadata.options"
                            class="flex items-center justify-between space-x-4 rounded-lg py-2 px-2 hover:border-blue-200 hover:bg-gray-50">

                            <div class="w-full flex items-center space-x-4">

                                <!-- Option Name Text Input -->
                                <Input
                                    class="w-full"
                                    :placeholder="`Option ${index2 + 1}`"
                                    v-model="designCard.metadata.options[index2].name"
                                    @input="designState.saveStateDebounced('Sub option name changed')"
                                    :errorText="formState.getFormError('design_cards'+index+'metadata.options'+index2+'name')">
                                </Input>

                                <!-- Option Fee Input -->
                                <Input
                                    type="money"
                                    v-model="designCard.metadata.options[index2].fee"
                                    @input="designState.saveStateDebounced('Sub option amount changed')"
                                    :errorText="formState.getFormError('design_cards'+index+'metadata.options'+index2+'fee')">
                                </Input>

                            </div>

                            <div class="flex items-center space-x-4">
                                <!-- Delete Button -->
                                <svg @click="onRemoveOption(index2)" class="w-4 h-4 cursor-pointer hover:text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>

                                <!-- Drag & Drop Handle -->
                                <Move @click.stop size="16" class="draggable-handle-2 cursor-grab active:cursor-grabbing text-gray-500 hover:text-yellow-500"></Move>
                            </div>

                        </div>

                    </draggable>

                </template>

                <div
                    v-if="supportsOptions"
                    :class="['flex', designCard.type == 'checkbox' ? 'justify-between items-start' : 'justify-end']">

                    <!-- Validation Select Input -->
                    <div v-if="designCard.type == 'checkbox'" class="flex items-end space-x-2">

                        <Select
                            class="w-60"
                            :search="false"
                            label="Validation"
                            :options="validationOptions"
                            v-model="designCard.metadata.validation"
                            @change="designState.saveStateDebounced('Validation changed')"
                            :errorText="formState.getFormError('design_cards'+index+'metadata.validation')">
                        </Select>

                        <!-- Select At Most Input -->
                        <Input
                            min="0"
                            class="w-24"
                            type="number"
                            v-model="designCard.metadata.min"
                            @change="designState.saveStateDebounced('Validation changed')"
                            :errorText="formState.getFormError('design_cards'+index+'metadata.min')"
                            :label="designCard.metadata.validation == 'select at least' ? null : 'Min'"
                            v-if="designCard.metadata.validation == 'select at least' || designCard.metadata.validation == 'select between'">
                        </Input>

                        <!-- Select At Least Input -->
                        <Input
                            min="0"
                            class="w-24"
                            type="number"
                            v-model="designCard.metadata.max"
                            @change="designState.saveStateDebounced('Validation changed')"
                            :errorText="formState.getFormError('design_cards'+index+'metadata.max')"
                            :label="designCard.metadata.validation == 'select at most' ? null : 'Max'"
                            v-if="designCard.metadata.validation == 'select at most' || designCard.metadata.validation == 'select between'">
                        </Input>

                    </div>

                    <!-- Add Option Button -->
                    <div class="relative">
                        <div class="absolute -bottom-12 left-1/2 transform -translate-x-1/2">
                            <div v-if="!hasOptions" class="animate-bounce text-4xl">👆</div>
                        </div>
                        <Button
                            size="xs"
                            type="primary"
                            :leftIcon="Plus"
                            :action="onAddOption">
                            <span>Add Choice</span>
                        </Button>
                    </div>

                </div>

            </div>

        </template>

        <Designer :designCard="designCard"></Designer>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Input from '@Partials/Input.vue';
    import Button from '@Partials/Button.vue';
    import Select from '@Partials/Select.vue';
    import { Move, Plus } from 'lucide-vue-next';
    import { VueDraggableNext } from 'vue-draggable-next';
    import Designer from '@Pages/design/_components/_components/design-cards/design-card/_components/Designer.vue';

    export default {
        inject: ['formState', 'designState'],
        components: {
            Move, Pill, Input, Button, Select, draggable: VueDraggableNext, Designer
        },
        props: {
            index: {
                type: Number
            },
            designCard: {
                type: Object
            }
        },
        watch() {

        },
        data() {
            return {
                Plus,
                validationOptions: [
                    { label: 'Not applicable', value: 'not applicable'},
                    { label: 'Select at least', value: 'select at least'},
                    { label: 'Select at most', value: 'select at most'},
                    { label: 'Select between', value: 'select between'}
                ]
            }
        },
        computed: {
            hasOptions() {
                return this.designCard.metadata.options.length > 0;
            },
            supportsOptions() {
                return this.designCard.type == 'checkbox' || this.designCard.type == 'selection';
            }
        },
        methods: {
            onAddOption() {
                this.designCard.metadata.options.push({
                    'name': `Option ${this.designCard.metadata.options.length + 1}`,
                    'fee': '0.00'
                });
                this.designState.saveStateDebounced('Sub option added');
            },
            onRemoveOption(index2) {
                this.designCard.metadata.options.splice(index2, 1);
                this.designState.saveStateDebounced('Sub option removed');
            }
        }
    };

</script>
