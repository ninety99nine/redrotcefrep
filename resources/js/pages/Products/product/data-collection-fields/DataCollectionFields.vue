<template>

    <div>

        <div :class="['space-y-4', { 'mt-4' : productForm.data_collection_fields.length < 2 }]">

            <div
                v-if="productForm.data_collection_fields.length >= 2"
                class="flex justify-end items-center space-x-2 px-2">
                <span
                    v-if="!hasCollapsedAll"
                    class="text-sm text-gray-500 underline hover:text-black cursor-pointer"
                    @click="collapseAll">
                    collapse all
                </span>
                <span
                    v-if="!hasExpandedAll"
                    class="text-sm text-gray-500 underline hover:text-black cursor-pointer"
                    @click.stop="expandAll">
                    expand all
                </span>
            </div>

            <!-- Draggable Fields -->
            <draggable
                class="space-y-2"
                handle=".draggable-handle"
                ghost-class="bg-yellow-50"
                v-model="productForm.data_collection_fields">

                <div
                    :key="dataCollectionField.temporary_id"
                    v-for="(dataCollectionField, index) in productForm.data_collection_fields"
                    @click="productForm.data_collection_fields[index].is_editable ? null : productForm.data_collection_fields[index].is_editable = true"
                    :class="['w-full relative bg-gray-50 p-4 border border-gray-300 rounded-lg hover:bg-gray-100 group', { 'cursor-pointer' : !productForm.data_collection_fields[index].is_editable }]">

                    <div class="absolute top-2 right-2 flex items-center space-x-2 opacity-20 group-hover:opacity-100">

                        <!-- Edit / Collapse Button -->
                        <span
                            v-if="productForm.data_collection_fields[index].is_editable"
                            class="text-sm text-gray-500 underline hover:text-black cursor-pointer"
                            @click.stop="productForm.data_collection_fields[index].is_editable = false">
                            collapse
                        </span>
                        <svg v-else class="w-4 h-4 cursor-pointer hover:opacity-50" @click.stop="productForm.data_collection_fields[index].is_editable = true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path v-if="productForm.data_collection_fields[index].is_editable" stroke-linecap="round" stroke-linejoin="round" d="M9 9V4.5M9 9H4.5M9 9 3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5 5.25 5.25" />
                            <path v-else stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>

                        <!-- Delete Field Button -->
                        <svg @click.stop="showDeleteConfirmationModal(dataCollectionField)" class="w-4 h-4 cursor-pointer hover:text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>

                        <!-- Drag & Drop Handle -->
                        <Move @click.stop size="16" class="draggable-handle cursor-grab active:cursor-grabbing text-gray-500 hover:text-yellow-500"></Move>

                    </div>

                    <div v-if="productForm.data_collection_fields[index].is_editable" class="space-y-4">

                        <div class="flex items-center space-x-2">

                            <Clock v-if="dataCollectionField.type == 'time'" size="20"></Clock>
                            <List v-if="dataCollectionField.type == 'selection'" size="20"></List>
                            <Hash v-if="dataCollectionField.type == 'number'" size="20"></Hash>
                            <Calendar v-if="dataCollectionField.type == 'date'" size="20"></Calendar>
                            <MapPin v-if="dataCollectionField.type == 'location'" size="20"></MapPin>
                            <CloudUpload v-if="dataCollectionField.type == 'media'" size="20"></CloudUpload>
                            <SquareCheck v-if="dataCollectionField.type == 'checkbox'" size="20"></SquareCheck>
                            <Tally2 v-if="dataCollectionField.type == 'long answer'" size="20" class="rotate-90"></Tally2>
                            <Tally1 v-if="dataCollectionField.type == 'short answer'" size="20" class="rotate-90"></Tally1>

                            <!-- Type Select -->
                            <Select
                                class="w-60"
                                :search="false"
                                :options="typeOptions"
                                v-model="productForm.data_collection_fields[index].type"
                                @change="productState.saveStateDebounced('Option type changed')"
                                :errorText="formState.getFormError('data_collection_fields'+index+'type')">
                            </Select>

                        </div>

                        <div class="flex items-start justify-between space-x-8">

                            <div class="w-full space-y-4">

                                <!-- Title Text Input -->
                                <Input
                                    class="w-full"
                                    placeholder="Title"
                                    v-model="productForm.data_collection_fields[index].title"
                                    @input="productState.saveStateDebounced('Option title changed')"
                                    :errorText="formState.getFormError('data_collection_fields'+index+'title')">
                                </Input>

                                <!-- Description Text Input -->
                                <Input
                                    rows="2"
                                    class="w-full"
                                    type="textarea"
                                    placeholder="Short description (optional)"
                                    v-model="productForm.data_collection_fields[index].description"
                                    @input="productState.saveStateDebounced('Option description changed')"
                                    :errorText="formState.getFormError('data_collection_fields'+index+'description')">
                                </Input>

                            </div>

                            <!-- Required Checkbox -->
                            <Input
                                type="checkbox"
                                inputLabel="Required"
                                v-model="productForm.data_collection_fields[index].required"
                                @change="productState.saveStateDebounced('Required status changed')"
                                :errorText="formState.getFormError('data_collection_fields'+index+'required')">
                            </Input>

                        </div>

                        <template v-if="supportsOptions(dataCollectionField)">

                            <!-- Draggable Options -->
                            <draggable
                                class="space-y-0"
                                handle=".draggable-handle-2"
                                ghost-class="bg-yellow-50"
                                v-model="productForm.data_collection_fields[index].options">
                                <div
                                    :key="index2"
                                    v-for="(option, index2) in productForm.data_collection_fields[index].options"
                                    class="flex items-center justify-between space-x-4 rounded-lg py-2 px-2 hover:border-blue-200 hover:bg-gray-50 group">

                                    <div class="w-full flex items-center space-x-4">

                                        <!-- Option Name Text Input -->
                                        <Input
                                            class="w-full"
                                            :placeholder="`Option ${index2 + 1}`"
                                            @input="productState.saveStateDebounced('Sub option name changed')"
                                            v-model="productForm.data_collection_fields[index].options[index2].name"
                                            :errorText="formState.getFormError('data_collection_fields'+index+'options'+index2+'name')">
                                        </Input>

                                        <!-- Option Fee Input -->
                                        <Input
                                            type="money"
                                            :currency="store.currency"
                                            v-model="productForm.data_collection_fields[index].options[index2].fee"
                                            @input="productState.saveStateDebounced('Sub option amount changed')"
                                            :errorText="formState.getFormError('data_collection_fields'+index+'options'+index2+'fee')">
                                        </Input>

                                    </div>

                                    <div class="flex items-center space-x-4">
                                        <!-- Delete Button - Triggers Confirmation Modal -->
                                        <svg @click="onRemoveOption(index, index2)" class="w-4 h-4 cursor-pointer hover:text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>

                                        <!-- Drag & Drop Handle -->
                                        <Move @click.stop size="16" class="draggable-handle-2 cursor-grab active:cursor-grabbing text-gray-500 hover:text-yellow-500"></Move>
                                    </div>

                                </div>

                            </draggable>

                        </template>

                        <div
                            v-if="supportsOptions(dataCollectionField)"
                            :class="['flex', dataCollectionField.type == 'checkbox' ? 'justify-between items-start' : 'justify-end']">

                            <!-- Validation Select -->
                            <div v-if="dataCollectionField.type == 'checkbox'" class="flex items-end space-x-2">

                                <Select
                                    class="w-60"
                                    :search="false"
                                    label="Validation"
                                    :options="validationOptions"
                                    v-model="productForm.data_collection_fields[index].validation"
                                    @change="productState.saveStateDebounced('Validation changed')"
                                    :errorText="formState.getFormError('data_collection_fields'+index+'validation')">
                                </Select>

                                <!-- Select At Most Input -->
                                <Input
                                    min="0"
                                    class="w-24"
                                    type="number"
                                    v-model="productForm.data_collection_fields[index].min"
                                    @change="productState.saveStateDebounced('Validation changed')"
                                    :errorText="formState.getFormError('data_collection_fields'+index+'min')"
                                    :label="productForm.data_collection_fields[index].validation == 'select at least' ? null : 'Min'"
                                    v-if="productForm.data_collection_fields[index].validation == 'select at least' || productForm.data_collection_fields[index].validation == 'select between'">
                                </Input>

                                <!-- Select At Least Input -->
                                <Input
                                    min="0"
                                    class="w-24"
                                    type="number"
                                    v-model="productForm.data_collection_fields[index].max"
                                    @change="productState.saveStateDebounced('Validation changed')"
                                    :errorText="formState.getFormError('data_collection_fields'+index+'max')"
                                    :label="productForm.data_collection_fields[index].validation == 'select at most' ? null : 'Max'"
                                    v-if="productForm.data_collection_fields[index].validation == 'select at most' || productForm.data_collection_fields[index].validation == 'select between'">
                                </Input>

                            </div>

                            <!-- Add Option Button -->
                            <div class="relative">
                                <div class="absolute -bottom-12 left-1/2 transform -translate-x-1/2">
                                    <div v-if="!hasOptions(index)" class="animate-bounce text-4xl">👆</div>
                                </div>
                                <Button
                                    size="xs"
                                    type="primary"
                                    :leftIcon="Plus"
                                    :action="() => onAddOption(index)">
                                    <span>Add Choice</span>
                                </Button>
                            </div>

                        </div>

                    </div>

                    <div v-else class="space-y-2" @click="productForm.data_collection_fields[index].is_editable = true">

                        <!-- Field Summary -->
                        <div class="flex items-center space-x-2 text-sm text-gray-500">

                            <!-- Icon -->
                            <Clock v-if="dataCollectionField.type == 'time'" size="20"></Clock>
                            <List v-if="dataCollectionField.type == 'selection'" size="20"></List>
                            <Hash v-if="dataCollectionField.type == 'number'" size="20"></Hash>
                            <Calendar v-if="dataCollectionField.type == 'date'" size="20"></Calendar>
                            <MapPin v-if="dataCollectionField.type == 'location'" size="20"></MapPin>
                            <CloudUpload v-if="dataCollectionField.type == 'media'" size="20"></CloudUpload>
                            <SquareCheck v-if="dataCollectionField.type == 'checkbox'" size="20"></SquareCheck>
                            <Tally2 v-if="dataCollectionField.type == 'long answer'" size="20" class="rotate-90"></Tally2>
                            <Tally1 v-if="dataCollectionField.type == 'short answer'" size="20" class="rotate-90"></Tally1>

                            <!-- Title -->
                            <span v-if="dataCollectionField.title">{{ dataCollectionField.title }}</span>
                            <span v-else class="font-medium text-red-500 text-xs ml-1">
                                No title
                            </span>

                            <Pill v-if="dataCollectionField.required" type="primary" size="xs" :showDot="false">required</Pill>

                        </div>

                    </div>

                </div>

            </draggable>

            <div :class="[{'flex space-x-2 justify-between' : !hasFields}]">

                <p v-if="!hasFields" class="text-sm text-gray-700">Add questions if your product must capture additional information from the customer</p>

                <div class="flex justify-end space-x-2">

                    <!-- Undo Button -->
                    <Button
                        size="xs"
                        type="primary"
                        :action="onResetFields"
                        v-if="fieldsHaveChanged && hasOriginalFields">
                        <span>Undo</span>
                    </Button>

                    <!-- Add Field Button -->
                    <Button
                        size="xs"
                        type="light"
                        :leftIcon="Plus"
                        :action="onAddField">
                        <span>Add Question</span>
                    </Button>

                </div>

            </div>

        </div>

        <!-- Confirm Delete Field -->
        <Modal
            approveType="danger"
            approveText="Delete"
            ref="confirmDeleteModal"
            :approveAction="(hideModal) => onRemoveField(hideModal)">

            <template #content v-if="deletableDataCollectionField">
                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">Confirm Delete</p>
                <div v-if="deletableDataCollectionField.title" class="flex items-start space-x-2 border border-gray-300 rounded-lg p-2 mb-4">

                    <!-- Icon -->
                    <Clock v-if="deletableDataCollectionField.type == 'time'" size="20"></Clock>
                    <List v-if="deletableDataCollectionField.type == 'selection'" size="20"></List>
                    <Hash v-if="deletableDataCollectionField.type == 'number'" size="20"></Hash>
                    <Calendar v-if="deletableDataCollectionField.type == 'date'" size="20"></Calendar>
                    <MapPin v-if="deletableDataCollectionField.type == 'location'" size="20"></MapPin>
                    <CloudUpload v-if="deletableDataCollectionField.type == 'media'" size="20"></CloudUpload>
                    <SquareCheck v-if="deletableDataCollectionField.type == 'checkbox'" size="20"></SquareCheck>
                    <Tally2 v-if="deletableDataCollectionField.type == 'long answer'" size="20" class="rotate-90"></Tally2>
                    <Tally1 v-if="deletableDataCollectionField.type == 'short answer'" size="20" class="rotate-90"></Tally1>

                    <!-- Title -->
                    <span class="text-sm">{{ deletableDataCollectionField.title }}</span>

                </div>
                <p class="mb-8">Are you sure you want to delete this <Pill type="primary" size="xs" :showDot="false">{{ deletableDataCollectionField.type }}</Pill> field?</p>
            </template>

        </Modal>

    </div>

</template>

<script>

    import { v4 as uuidv4 } from 'uuid';
    import isEqual from 'lodash.isEqual';
    import Pill from '@Partials/Pill.vue';
    import Input from '@Partials/Input.vue';
    import Modal from '@Partials/Modal.vue';
    import cloneDeep from 'lodash.cloneDeep';
    import Button from '@Partials/Button.vue';
    import Select from '@Partials/Select.vue';
    import { VueDraggableNext } from 'vue-draggable-next';
    import { Move, Plus, List, Clock, Tally1, Tally2, MapPin, Hash, Calendar, CloudUpload, SquareCheck } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'storeState', 'productState'],
        components: {
            Move, List, Clock, Tally1, Tally2, MapPin, Hash, Calendar, CloudUpload, SquareCheck,
            Pill, Input, Modal, Button, Select, draggable: VueDraggableNext
        },
        data() {
            return {
                Plus,
                originalFields: [],
                deletableDataCollectionField: null,
                typeOptions: [
                    { label: 'Short answer', value: 'short answer'},
                    { label: 'Long answer', value: 'long answer'},
                    { label: 'Number', value: 'number'},
                    { label: 'Date', value: 'date'},
                    { label: 'Time', value: 'time'},
                    { label: 'Checkbox', value: 'checkbox'},
                    { label: 'Selection', value: 'selection'},
                    { label: 'Location', value: 'location'},
                    { label: 'Media', value: 'media'},
                ],
                validationOptions: [
                    { label: 'Not applicable', value: 'not applicable'},
                    { label: 'Select at least', value: 'select at least'},
                    { label: 'Select at most', value: 'select at most'},
                    { label: 'Select between', value: 'select between'}
                ]
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            productForm() {
                return this.productState.productForm;
            },
            hasFields() {
                return this.productForm.data_collection_fields.length > 0;
            },
            hasOriginalFields() {
                return this.originalFields.length > 0;
            },
            hasCollapsedAll() {
                return this.productForm.data_collection_fields.every(field => field.is_editable === false);
            },
            hasExpandedAll() {
                return this.productForm.data_collection_fields.every(field => field.is_editable === true);
            },
            fieldsHaveChanged() {
                // Clone the arrays to avoid modifying the original data
                var a = cloneDeep(this.productForm.data_collection_fields);
                var b = cloneDeep(this.originalFields);

                // Loop through each object in the array and delete the property
                a.forEach(obj => delete obj.is_editable);
                b.forEach(obj => delete obj.is_editable);

                // Compare the modified arrays for equality
                return !isEqual(a, b);
            },
        },
        methods: {
            onAddField() {
                this.productForm.data_collection_fields.push({
                    validation: 'not applicable',
                    temporary_id: uuidv4(),
                    type: 'short answer',
                    is_editable: true,
                    required: false,
                    description: '',
                    options: [],
                    title: '',
                    min: '1',
                    max: '2'
                });
                this.productState.saveStateDebounced('Option added');
            },
            onRemoveField(hideModal) {
                const index = this.productForm.data_collection_fields.findIndex(dataCollectionField => dataCollectionField.temporary_id == this.deletableDataCollectionField.temporary_id);
                this.productForm.data_collection_fields.splice(index, 1);
                hideModal();
                this.productState.saveStateDebounced('Option removed');
            },
            onResetFields() {
                this.productForm.data_collection_fields = cloneDeep(this.originalFields);
                this.productState.saveStateDebounced('Options restored');
            },
            onAddOption(index) {
                this.productForm.data_collection_fields[index].options.push({
                    'title': `Option ${this.productForm.data_collection_fields[index].options.length + 1}`,
                    'fee': '0.00'
                });
                this.productState.saveStateDebounced('Sub option added');
            },
            onRemoveOption(index, index2) {
                this.productForm.data_collection_fields[index].options.splice(index2, 1);
                this.productState.saveStateDebounced('Sub option removed');
            },
            collapseAll() {
                this.productForm.data_collection_fields.forEach(field => {
                    field.is_editable = false;
                });
            },
            expandAll() {
                this.productForm.data_collection_fields.forEach(field => {
                    field.is_editable = true;
                });
            },
            showDeleteConfirmationModal(dataCollectionField) {
                this.deletableDataCollectionField = dataCollectionField;
                this.$refs.confirmDeleteModal.showModal();
            },
            supportsOptions(dataCollectionField) {
                return dataCollectionField.type == 'checkbox' || dataCollectionField.type == 'selection';
            },
            hasOptions(index) {
                return this.productForm.data_collection_fields[index].options.length > 0;
            },
        }
    };

</script>
