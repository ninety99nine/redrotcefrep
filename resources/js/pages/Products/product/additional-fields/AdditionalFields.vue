<template>

    <div :class="[{ 'border border-dashed rounded-lg': form.captureAdditionalFields }]">

        <div :class="[{ 'bg-blue-50 p-4 rounded-lg': form.captureAdditionalFields }, 'transition-all duration-500']">

            <!-- Capture Additional Fields Toggle Switch -->
            <CaptureAdditionalFieldsToggleSwitch :form="form"></CaptureAdditionalFieldsToggleSwitch>

            <div v-if="form.captureAdditionalFields" class="space-y-4 mt-4">

                <div class="flex justify-end items-center space-x-2 px-2">
                    <span
                        v-if="form.additionalFields.length >= 2 && !hasCollapsedAll"
                        class="text-sm text-gray-500 underline hover:text-black cursor-pointer"
                        @click="collapseAll">
                        collapse all
                    </span>
                    <span
                        v-if="form.additionalFields.length >= 2 && !hasExpandedAll"
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
                    v-model="form.additionalFields">

                    <div v-for="(additionalField, index) in form.additionalFields" :key="index"
                        @click="form.additionalFields[index].isEditable ? null : form.additionalFields[index].isEditable = true"
                        :class="['w-full relative bg-gray-50 p-4 border rounded-lg hover:bg-gray-100 group', { 'cursor-pointer' : !form.additionalFields[index].isEditable }]">

                        <div class="absolute top-2 right-2 flex items-center space-x-2 opacity-20 group-hover:opacity-100">

                            <!-- Edit / Close Button -->
                            <span
                                v-if="form.additionalFields[index].isEditable"
                                class="text-sm text-gray-500 underline hover:text-black cursor-pointer"
                                @click.stop="form.additionalFields[index].isEditable = false">
                                close
                            </span>
                            <svg v-else class="w-4 h-4 cursor-pointer hover:opacity-50" @click.stop="form.additionalFields[index].isEditable = true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path v-if="form.additionalFields[index].isEditable" stroke-linecap="round" stroke-linejoin="round" d="M9 9V4.5M9 9H4.5M9 9 3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5 5.25 5.25" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>

                            <!-- Delete Field Button -->
                            <svg @click.stop="showDeleteConfirmationModal(additionalField, index)" class="w-4 h-4 cursor-pointer hover:text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>

                            <!-- Drag & Drop Handle -->
                            <svg @click.stop class="draggable-handle w-4 h-4 cursor-grab hover:text-yellow-500 active:cursor-grabbing" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                            </svg>

                        </div>

                        <div v-if="form.additionalFields[index].isEditable" class="space-y-4">

                            <div class="flex items-center space-x-2">

                                <svg v-if="additionalField.type == 'short answer'" class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
                                </svg>
                                <svg v-else-if="additionalField.type == 'time'" class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                <svg v-else-if="additionalField.type == 'date'" class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                                <svg v-else-if="additionalField.type == 'checkbox'" class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                <svg v-else-if="additionalField.type == 'select'" class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                </svg>
                                <svg v-else-if="additionalField.type == 'file upload'" class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                </svg>
                                <svg v-else-if="additionalField.type == 'location'" class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                </svg>

                                <!-- Type Select Input -->
                                <SelectInput
                                    width="w-60"
                                    labelPopoverTitle="What Is This?"
                                    v-model="form.additionalFields[index].type"
                                    :errorText="formState.getFormError('additionalFields'+index+'type')"
                                    labelPopoverDescription="Select the field type">
                                    <option value="short answer">Short answer</option>
                                    <option value="time">Time</option>
                                    <option value="date">Date</option>
                                    <option value="checkbox">Checkbox</option>
                                    <option value="select">Select</option>
                                    <option value="file upload">File upload</option>
                                    <option value="location">Location</option>
                                </SelectInput>

                            </div>

                            <!-- Question Text Input -->
                            <TextInput
                                placeholder="Question"
                                labelPopoverTitle="What Is This?"
                                v-model="form.additionalFields[index].question"
                                labelPopoverDescription="The question to ask the customer"
                                :errorText="formState.getFormError('additionalFields'+index+'question')">
                            </TextInput>

                            <!-- Description Textarea Input -->
                            <TextareaInput
                                :rows="2"
                                placeholder="Description"
                                labelPopoverTitle="What Is This?"
                                v-model="form.additionalFields[index].description"
                                 v-if="form.additionalFields[index].addDescription"
                                :errorText="formState.getFormError('additionalFields'+index+'description')"
                                labelPopoverDescription="Additional information related to the question">
                            </TextareaInput>
                            <span
                                v-else
                                @click.stop="form.additionalFields[index].addDescription = true"
                                class="inline-block text-sm text-gray-500 underline cursor-pointer px-2">
                                Add description
                            </span>
                            <span
                                v-if="form.additionalFields[index].addDescription == true && form.additionalFields[index].description == ''"
                                class="inline-block text-sm text-gray-500 underline cursor-pointer px-2"
                                @click.stop="form.additionalFields[index].addDescription = false" >
                                Hide description
                            </span>

                            <template v-if="supportsOptions(additionalField)">

                                <!-- Draggable Options -->
                                <draggable
                                    class="space-y-2"
                                    handle=".draggable-handle-2"
                                    ghost-class="bg-yellow-50"
                                    v-model="form.additionalFields[index].options">
                                    <div
                                        :key="index2"
                                        v-for="(option, index2) in form.additionalFields[index].options"
                                        class="flex items-center justify-between space-x-4 rounded-lg py-2 px-2 hover:border-blue-200 hover:bg-gray-50 group">

                                        <div class="w-full flex items-center space-x-4">

                                            <!-- Option Name Text Input -->
                                            <TextInput
                                                class="w-full"
                                                placeholder="Option"
                                                v-model="form.additionalFields[index].options[index2].name"
                                                :errorText="formState.getFormError('additionalFields'+index+'options'+index2+'name')">
                                            </TextInput>

                                            <!-- Option Fee Input -->
                                            <MoneyInput
                                                v-model="form.additionalFields[index].options[index2].fee"
                                                :errorText="formState.getFormError('additionalFields'+index+'options'+index2+'fee')">
                                            </MoneyInput>

                                        </div>

                                        <div class="flex items-center space-x-4">
                                            <!-- Delete Button - Triggers Confirmation Modal -->
                                            <svg @click="onRemoveFieldOption(index, index2)" class="w-4 h-4 cursor-pointer hover:text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>

                                            <!-- Drag & Drop Handle -->
                                            <svg @click.stop class="draggable-handle-2 w-4 h-4 cursor-grab hover:text-yellow-500 active:cursor-grabbing" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                                            </svg>
                                        </div>

                                    </div>

                                </draggable>

                                <div class="flex justify-end">

                                    <!-- Add Option Button -->
                                    <div class="relative">
                                        <div class="absolute -bottom-12 left-1/2 transform -translate-x-1/2">
                                            <div v-if="!hasOptions(index)" class="animate-bounce text-4xl">👆</div>
                                        </div>
                                        <Button type="primary" size="xs" :action="() => onAddOption(index)" class="w-40">
                                            <span>Add Option</span>
                                        </Button>
                                    </div>

                                </div>

                            </template>

                            <div :class="['flex', additionalField.type == 'checkbox' ? 'justify-between' : 'justify-end', 'items-end']">

                                <!-- Validation Select Input -->
                                <div v-if="additionalField.type == 'checkbox'" class="flex items-end space-x-2">

                                    <SelectInput
                                        width="w-60"
                                        label="Validation"
                                        labelPopoverTitle="What Is This?"
                                        v-model="form.additionalFields[index].validation"
                                        :errorText="formState.getFormError('additionalFields'+index+'validation')"
                                        labelPopoverDescription="Select the type of validation to be applied">
                                        <option value="not applicable">Not applicable</option>
                                        <option value="select at least">Select at least</option>
                                        <option value="select at most">Select at most</option>
                                        <option value="select between">Select between</option>
                                    </SelectInput>

                                    <!-- Select At Most Input -->
                                    <NumberInput
                                        min="0"
                                        class="w-24"
                                        v-model="form.additionalFields[index].min"
                                        :errorText="formState.getFormError('additionalFields'+index+'min')"
                                        :label="form.additionalFields[index].validation == 'select at least' ? '' : 'Min'"
                                        v-if="form.additionalFields[index].validation == 'select at least' || form.additionalFields[index].validation == 'select between'">
                                    </NumberInput>

                                    <!-- Select At Least Input -->
                                    <NumberInput
                                        min="0"
                                        class="w-24"
                                        v-model="form.additionalFields[index].max"
                                        :errorText="formState.getFormError('additionalFields'+index+'max')"
                                        :label="form.additionalFields[index].validation == 'select at most' ? '' : 'Max'"
                                        v-if="form.additionalFields[index].validation == 'select at most' || form.additionalFields[index].validation == 'select between'">
                                    </NumberInput>

                                </div>

                                <!-- Required Checkbox -->
                                <Checkbox
                                    size="xs"
                                    v-model="form.additionalFields[index].required"
                                    :errorText="formState.getFormError('additionalFields'+index+'required')"
                                    labelPopoverDescription="Whether this field is always required">
                                    <p class="text-sm mt-0.5 mx-2">Required</p>
                                </Checkbox>

                            </div>

                        </div>

                        <div v-else class="space-y-2" @click="form.additionalFields[index].isEditable = true">

                            <!-- Field Summary -->
                            <div class="flex items-center space-x-2 text-sm text-gray-500">

                                <!-- Icon -->
                                <svg v-if="additionalField.type == 'short answer'" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
                                </svg>
                                <svg v-else-if="additionalField.type == 'time'" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                <svg v-else-if="additionalField.type == 'date'" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                                <svg v-else-if="additionalField.type == 'checkbox'" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                <svg v-else-if="additionalField.type == 'select'" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                </svg>
                                <svg v-else-if="additionalField.type == 'file upload'" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                </svg>
                                <svg v-else-if="additionalField.type == 'location'" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                </svg>

                                <!-- Question -->
                                <span v-if="additionalField.question">{{ additionalField.question }}</span>
                                <InputErrorMessage v-else errorText="No question asked" margin="mt-0"></InputErrorMessage>

                                <Pill v-if="additionalField.required" type="primary" size="xs" :showDot="false">required</Pill>

                            </div>

                            <!-- Description -->
                            <p v-if="additionalField.description" class="text-sm text-gray-500">{{ additionalField.description }}</p>

                        </div>

                    </div>

                </draggable>

                <div class="flex justify-end space-x-2">

                    <!-- Undo Button -->
                    <Button v-if="fieldsHaveChanged && hasOriginalFields" type="primary" size="xs" :action="onResetFields">
                        <span>Undo</span>
                    </Button>

                    <div class="flex justify-end">

                        <!-- Add Field Button -->
                        <div class="relative">
                            <div class="absolute -bottom-12 left-1/2 transform -translate-x-1/2">
                                <div v-if="!hasFields" class="animate-bounce text-4xl">👆</div>
                            </div>
                            <Button type="primary" size="xs" :action="onAddField" class="w-40">
                                <span>Add Field</span>
                            </Button>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- Confirm Delete Field -->
        <ConfirmModal v-if="deletableAdditionalField" approveText="Delete" :approveAction="(hideModal) => onRemoveField(hideModal)">

            <template #content>
                <p class="text-lg font-bold border-b border-dashed pb-4 mb-4">Confirm Delete</p>
                <div v-if="deletableAdditionalField.question" class="flex items-start space-x-2 border rounded-lg p-2 mb-4">

                    <!-- Icon -->
                    <svg v-if="deletableAdditionalField.type == 'short answer'" class="w-4 h-4 flex-shrink-0 mt-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
                    </svg>
                    <svg v-else-if="deletableAdditionalField.type == 'time'" class="w-4 h-4 flex-shrink-0 mt-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <svg v-else-if="deletableAdditionalField.type == 'date'" class="w-4 h-4 flex-shrink-0 mt-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    <svg v-else-if="deletableAdditionalField.type == 'checkbox'" class="w-4 h-4 flex-shrink-0 mt-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <svg v-else-if="deletableAdditionalField.type == 'select'" class="w-4 h-4 flex-shrink-0 mt-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                    </svg>
                    <svg v-else-if="deletableAdditionalField.type == 'file upload'" class="w-4 h-4 flex-shrink-0 mt-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                    </svg>
                    <svg v-else-if="deletableAdditionalField.type == 'location'" class="w-4 h-4 flex-shrink-0 mt-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    </svg>

                    <!-- Question -->
                    <span>{{ deletableAdditionalField.question }}</span>

                </div>
                <p class="mb-8">Are you sure you want to delete this <Pill type="primary" size="xs" :showDot="false">{{ deletableAdditionalField.type }}</Pill> field?</p>
            </template>

        </ConfirmModal>

    </div>

</template>

<script>

    import isEqual from 'lodash/isEqual';
    import cloneDeep from 'lodash/cloneDeep';
    import Pill from '@Partials/pills/Pill.vue';
    import Button from '@Partials/buttons/Button.vue';
    import { VueDraggableNext } from 'vue-draggable-next';
    import TextInput from '@Partials/inputs/TextInput.vue';
    import MoneyInput from '@Partials/inputs/MoneyInput.vue';
    import Checkbox from '@Partials/checkboxes/Checkbox.vue';
    import SelectInput from '@Partials/inputs/SelectInput.vue';
    import NumberInput from '@Partials/inputs/NumberInput.vue';
    import ConfirmModal from '@Partials/modals/ConfirmModal.vue';
    import TextareaInput from '@Partials/inputs/TextareaInput.vue';
    import InputErrorMessage from '@Partials/input-error-messages/InputErrorMessage.vue';
    import CaptureAdditionalFieldsToggleSwitch from '@Pages/stores/store/settings/delivery-methods/components/CaptureAdditionalFieldsToggleSwitch.vue';

    export default {
        inject: ['formState', 'storeState'],
        components: {
            Pill, Button, draggable: VueDraggableNext, TextInput, MoneyInput, Checkbox,
            SelectInput, ConfirmModal, NumberInput, TextareaInput, InputErrorMessage,
            CaptureAdditionalFieldsToggleSwitch
        },
        props: {
            form: {
                type: Object
            }
        },
        data() {
            return {
                originalFields: [],
                deletableIndex: null,
                deletableAdditionalField: null,
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            hasFields() {
                return this.form.additionalFields.length > 0;
            },
            hasOriginalFields() {
                return this.originalFields.length > 0;
            },
            hasCollapsedAll() {
                return this.form.additionalFields.every(field => field.isEditable === false);
            },
            hasExpandedAll() {
                return this.form.additionalFields.every(field => field.isEditable === true);
            },
            fieldsHaveChanged() {
                // Clone the arrays to avoid modifying the original data
                var a = cloneDeep(this.form.additionalFields);
                var b = cloneDeep(this.originalFields);

                // Loop through each object in the array and delete the property
                a.forEach(obj => delete obj.isEditable);
                b.forEach(obj => delete obj.isEditable);

                // Compare the modified arrays for equality
                return !isEqual(a, b);
            },
        },
        methods: {
            onAddField() {
                this.form.additionalFields.push({
                    'validation': 'not applicable',
                    'type': 'short answer',
                    'isEditable': true,
                    'required': false,
                    'description': '',
                    'options': [],
                    'question': '',
                    'min': '1',
                    'max': '2'
                });
            },
            showDeleteConfirmationModal(additionalField, index) {
                this.deletableIndex = index;
                this.deletableAdditionalField = additionalField;
                /**
                 *  After setting the deletableAdditionalField, we need to wait until the nextTick()
                 *  before we programatically trigger the element click() event which opens the
                 *  confirmation modal dialog. This is so that when the dialog opens we don't
                 *  get an error trying to access the deletableAdditionalField values. This
                 *  is only available on the nextTick().
                 */
                this.$nextTick(() => {
                    this.$refs.confirmDeleteButton.$el.click();
                });
            },
            onRemoveField(hideModal) {
                this.form.additionalFields.splice(this.deletableIndex, 1);
                hideModal();
            },
            onResetFields() {
                this.form.additionalFields = cloneDeep(this.originalFields);
            },
            hasOptions(index) {
                return this.form.additionalFields[index].options.length > 0;
            },
            supportsOptions(additionalField) {
                return additionalField.type == 'checkbox' || additionalField.type == 'select';
            },
            onAddOption(index) {
                this.form.additionalFields[index].options.push({
                    'name': '',
                    'fee': '0.00'
                });
            },
            collapseAll() {
                this.form.additionalFields.forEach(field => {
                    field.isEditable = false;
                });
            },
            expandAll() {
                this.form.additionalFields.forEach(field => {
                    field.isEditable = true;
                });
            },
            onRemoveFieldOption(index, index2) {
                this.form.additionalFields[index].options.splice(index2, 1);
            },
        }
    };

</script>
